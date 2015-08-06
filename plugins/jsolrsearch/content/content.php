<?php
/**
 * A plugin for searching articles.
 *
 * @package     JSolr.Plugin
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.database.table.category');
jimport('joomla.database.table.content');

use \JSolr\Search\Search;

class plgJSolrSearchContent extends JSolrSearchSearch
{
    protected $context = 'com_content.article';

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->set('highlighting', array("title", "title_*", "body_*", "metadescription_*", "category_*"));
    }

    public function onJSolrSearchURIGet($document)
    {
        if ($this->get('context') == $document->context) {
            require_once(JPATH_ROOT."/components/com_content/helpers/route.php");

            return ContentHelperRoute::getArticleRoute($document->id, $document->parent_id);
        }

        return null;
    }
}
