<?php
/**
 * @package     JSolr.Plugin
 * @subpackage  Index
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die();

use \JSolr\Index\Crawler;

class plgJSolrCrawlerNewsfeeds extends JSolrIndexCrawler
{
    protected $context = 'com_newsfeeds.newsfeed';

    /**
    * Prepares an article for indexing.
    */
    protected function getDocument(&$record)
    {
        $doc = new JSolrApacheSolrDocument();

        $created = JFactory::getDate($record->created);
        $modified = JFactory::getDate($record->modified);

        $lang = $this->getLanguage($record, false);

        $doc->addField('created', $created->format('Y-m-d\TH:i:s\Z', false));
        $doc->addField('modified', $modified->format('Y-m-d\TH:i:s\Z', false));
        $doc->addField("title", $record->title);
        $doc->addField("title_$lang", $record->title);
        $doc->addField("link_$lang", $record->link);
        $doc->addField("access", $record->access);

        foreach (explode(',', $record->metakey) as $metakey) {
            $doc->addField("metakeywords_$lang", trim($metakey));
        }

        $doc->addField("metadescription_$lang", $record->metadesc);
        $doc->addField("author", $record->author);

        if ($record->catid) {
            $doc->addField("parent_id", $record->catid);
            $doc->addField("category_$lang", $record->category);
            $doc->addField("category_fc", $record->category); // facet
        }

        return $doc;
    }

    protected function buildQuery()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.id, a.catid, a.name AS title, a.alias, a.link AS link');
        $query->select('a.published AS state, a.ordering, a.created, a.params, a.access');
        $query->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
        $query->select('a.metakey, a.metadesc, a.metadata, a.language');
        $query->select('a.created_by, a.created_by_alias, a.modified, a.modified_by');

        $query->from('#__newsfeeds AS a');

        // Join over the users for the author.
        $query->select('ua.name AS author');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        $query->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');
        $query->join('LEFT', '#__categories AS c ON c.id = a.catid');

        $conditions = array();

        $categories = $this->params->get('categories');

        if (is_array($categories)) {
            if (JArrayHelper::getValue($categories, 0) != 0) {
                JArrayHelper::toInteger($categories);
                $categories = implode(',', $categories);
                $conditions[] = 'a.catid IN ('.$categories.')';
            }
        }

        if (count($conditions)) {
            $query->where($conditions);
        }

        return $query;
    }
}
