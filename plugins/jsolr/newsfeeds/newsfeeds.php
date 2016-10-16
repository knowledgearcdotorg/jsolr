<?php
/**
 * @package     JSolr.Plugin
 * @subpackage  Index
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

\JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

use \JSolr\Helper;

class PlgJSolrNewsfeeds extends \JSolr\Plugin
{
    protected $context = 'com_newsfeeds.newsfeed';

    /**
     * Get the total number of articles.
     *
     * @return  int  The total number of articles.
     */
    protected function getTotal()
    {
        // The getStoreId throws an error if catid array is used.
        // Therefore, block the error.
        return @(int)$this->getNewsfeeds()->getTotal();
    }

    /**
     * Get a list of items.
     *
     * Items are paged depending on the Joomla! pagination settings.
     *
     * @param   int         $start  The position of the first item in the
     * recordset.
     * @param   int         $limit  The page size of the recordset.
     *
     * @return  StdClass[]  A list of items.
     */
    protected function getItems($start = 0, $limit = 10)
    {
        $items = $this->getNewsfeeds();

        $items->setState('list.start', $start);
        $items->setState('list.limit', $limit);
        $items->setState('list.ordering', 'a.id');
        $items->setState('list.direction', 'asc');

        // The getStoreId throws an error if catid array is used.
        // Therefore, block the error.
        return @$items->getItems();
    }

    /**
     * Gets an instance of the ContentModelNewsfeeds class.
     *
     * @return  JModelLegacy  An instance of the ContentModelNewsfeeds class.
     */
    private function getNewsfeeds()
    {
        $path = JPATH_ADMINISTRATOR.'/components/com_newsfeeds/models/newsfeeds.php';
        \JLoader::register('NewsfeedsModelNewsfeeds', $path);

        \JModelLegacy::addTablePath(JPATH_ADMINISTRATOR.'/components/com_newsfeeds/tables');

        $newsfeeds = \JModelLegacy::getInstance(
                        'Newsfeeds',
                        'NewsfeedsModel',
                        array('ignore_request'=>true));

        $catids = $this->params->get('categories', array());

        if (($pos = array_search(0, $catids)) !== false) {
            unset($catids[$pos]);
        }

        if (count($catids)) {
            $newsfeeds->setState("filter.category_id", $catids);
        }

        return $newsfeeds;
    }

    /**
     * Gets a newsfeed by id.
     *
     * @param   int           $id  The newsfeed id.
     *
     * @return  JModelLegacy  An instance of the NewsfeedsModelNewsfeed class.
     */
    protected function getItem($id)
    {
        $path = JPATH_ROOT.'/administrator/components/com_newsfeeds/models/newsfeed.php';
        \JLoader::register('NewsfeedsModelNewsfeed', $path);

        $newsfeed = \JModelLegacy::getInstance(
                        'Newsfeed',
                        'NewsfeedsModel',
                        array('ignore_request'=>true));

        return $newsfeed->getItem($id);
    }

    /**
     * Prepare the item for indexing.
     *
     * @param   StdClass  $source
     * @return  array
     */
    protected function prepare($source)
    {
        $source = $this->getItem($source->id);

        $lang = $this->getLanguage($source->language, false);
        $author = JFactory::getUser($source->created_by);
        $category = JCategories::getInstance('newsfeeds')->get($source->catid);

        $array = array();

        $array['id'] = $this->buildId($source->id);
        $array['id_i'] = $source->id;
        $array['name'] = $source->name;
        $array["author"] = $author->name;
        $array["author_ss"] = $this->getFacet($author->name);
        $array["author_i"] = $author->id;
        $array["title_txt_$lang"] = $source->name;
        $array['alias_s'] = $source->alias;
        $array['context_s'] = $this->get('context');
        $array['lang_s'] = $source->language;
        $array['access_i'] = $source->access;
        $array["link_s"] = $source->link;
        $array["category_txt_$lang"] = $category->title;
        $array["category_s"] = $this->getFacet($category->title); // for faceting
        $array["category_i"] = $category->id;

        $created = JFactory::getDate($source->created);
        $modified = JFactory::getDate($source->modified);

        if ($created > $modified) {
            $modified = $created;
        }

        $array['created_tdt'] = $created->format('Y-m-d\TH:i:s\Z', false);
        $array['modified_tdt'] = $modified->format('Y-m-d\TH:i:s\Z', false);
        $array["parent_id_i"] = $source->catid;

        foreach ($source->tags->getItemTags('com_newsfeeds.newsfeed', $source->id) as $tag) {
            $array["tag_ss"][] = $tag->title;
        }

        return $array;
    }

    public function onJSolrSearchPrepareData($document)
    {
        if ($this->get('context') == $document->context_s) {
            require_once(JPATH_ROOT."/components/com_newsfeeds/helpers/route.php");

            $document->link = NewsfeedsHelperRoute::getNewsfeedRoute($document->id, $document->parent_id);

        }
    }
}
