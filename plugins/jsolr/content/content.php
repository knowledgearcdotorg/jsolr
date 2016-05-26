<?php
/**
 * @package     JSolr.Plugin
 * @subpackage  Index
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

\JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

use \JSolr\Index\Crawler;
use \JSolr\Helper;

class PlgJSolrContent extends Crawler
{
    protected $context = 'com_content.article';

    /**
     *
     * @param string    $context  The context of the item being saved.
     * @param StdClass  $item     The item being deleted (must have an id property).
     * @param bool      $isNew    True if the item is new, false otherwise.
     */
    public function onJSolrAfterSave($context, $item, $isNew)
    {
        if ($context == $this->get('context')) {
            $commitWithin = $this->params->get('component.commitWithin', '1000');

            $client = \JSolr\Index\Factory::getClient();
            $update = $client->createUpdate();

            if ((int)$item->state == 1) {
                $array = $this->prepare($item);

                $document = $update->createDocument($array);
                $update->addDocument($document, null, $commitWithin);
            } else {
                $dispatcher = JDispatcher::getInstance();
                JPluginHelper::importPlugin('jsolr');

                $results = $dispatcher->trigger('onJSolrAfterDelete', array($context, $item));
            }

            $update->addCommit();

            $result = $client->update($update);
        }
    }

    /**
     * Triggers an event to delete an indexed item.
     *
     * @param string $context The context of the item being deleted.
     * @param mixed $item The item being deleted (must have an id property).
     */
    public function onJSolrAfterDelete($context, $item)
    {
        if ($context == $this->get('context')) {
            $client = \JSolr\Index\Factory::getClient();
            $update = $client->createUpdate();

            $update->addDeleteQuery("id:".$this->get('context').'.'.$item->id);
            $update->addCommit();

            $result = $client->update($update);
        }
    }

    public function onJSolrChangeState($context, $pks, $value)
    {
        if ($context == $this->get('context')) {
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('jsolr');

            foreach ($pks as $pk) {
                // dummy item for passing to respective event.
                $item = new StdClass();
                $item->id = $pk;
                $item->state = $value;

                $results = $dispatcher->trigger('onJSolrAfterSave', array($context, $item, false));
            }
        }
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
        $items = $this->getArticles();

        $items->setState('list.start', $start);
        $items->setState('list.limit', $limit);

        return $items->getItems();
    }

    /**
     * Get the total number of articles.
     *
     * @return  int  The total number of articles.
     */
    protected function getTotal()
    {
        return (int)$this->getArticles()->getTotal();
    }

    /**
     * Gets an instance of the ContentModelArticles class.
     *
     * @return  JModelLegacy  An instance of the ContentModelArticle class.
     */
    private function getArticles()
    {
        $path = JPATH_ROOT.'/administrator/components/com_content/models/articles.php';
        \JLoader::register('ContentModelArticles', $path);

        $articles = \JModelLegacy::getInstance(
                        'Articles',
                        'ContentModel',
                        array('ignore_request'=>true));

        return $articles;
    }

    /**
     * Gets an article by id.
     *
     * @param   int           $id  The article id.
     *
     * @return  JModelLegacy  An instance of the ContentModelArticle class.
     */
    protected function getItem($id)
    {
        $path = JPATH_ROOT.'/administrator/components/com_content/models/article.php';
        \JLoader::register('ContentModelArticle', $path);

        $article = \JModelLegacy::getInstance(
                        'Article',
                        'ContentModel',
                        array('ignore_request'=>true));

        return $article->getItem($id);
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
        $category = JCategories::getInstance('content')->get($source->catid);

        $array = array();

        $array['id'] = $this->buildId($source->id);
        $array['name'] = $source->title;
        $array["author"] = $author->name;
        $array["author_s"] = $this->getFacet($author->name);
        $array["author_i"] = $author->id;
        $array["title_txt_$lang"] = $source->title;
        $array['alias_s'] = $source->alias;
        $array['context_s'] = $this->get('context');
        $array['lang_s'] = $source->language;
        $array['access_i'] = $source->access;
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

        $params = new \Joomla\Registry\Registry();
        $params->loadArray($source->attribs);

        $content = Helper::prepareContent($source->articletext, $params);

        $array["content_txt_$lang"] = strip_tags($content);

        foreach ($source->tags->getItemTags('com_content.article', $source->id) as $tag) {
            $array["tag_ss"][] = $tag->title;
        }

        return $array;
    }
}
