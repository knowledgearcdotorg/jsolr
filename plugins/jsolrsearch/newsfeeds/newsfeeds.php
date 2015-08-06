<?php
/**
 * A plugin for searching news feed items.
 *
 * @package     JSolr.Plugin
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');

use \JSolr\Search\Search;

class plgJSolrSearchNewsfeeds extends Search
{
    protected $context = 'com_newsfeeds.newsfeed';

    public function __construct(&$subject, $config = array())
    {
        $this->set('highlighting', array("title", "body", "link", "category"));

        parent::__construct($subject, $config);
    }

    private function _getHlContent($document, $highlighting, $fragSize, $lang)
    {
        $id = $document->key;
        $hl = array();

        $body = "body_$lang";

        if (isset($highlighting->$id->$body)) {
            foreach ($highlighting->$id->$body as $item) {
                $hl[] = $item;
            }
        }

        return implode("...", $hl);
    }


    public function onJSolrSearchURIGet($document)

    {

        if ($this->get('context') == $document->context) {

            require_once(JPATH_ROOT."/components/com_newsfeeds/helpers/route.php");



            return NewsfeedsHelperRoute::getNewsfeedRoute($document->id, $document->parent_id);

        }



        return null;

    }
}
