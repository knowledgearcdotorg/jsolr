<?php
/**
 * @package     JSolr.Search
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @param   array
 *
 * @return  array
 */
function JSolrSearchBuildRoute(&$query)
{
    static $menu;

    $segments = array();

    // Load the menu if necessary.
    if (!$menu) {
        $menu = JFactory::getApplication('site')->getMenu();
    }

    if (count($query) === 2 &&
        isset($query['Itemid']) &&
        isset($query['option'])) {
        return $segments;
    }

    if (!empty($query['Itemid'])) {
        // Get the menu item.
        $item = $menu->getItem($query['Itemid']);

        // Check if the view matches.
        if ($item && @$item->query['view'] === @$query['view']) {
            unset($query['view']);
        }

        return $segments;
    }

    if (isset($query['view'])) {
        $segments[] = $query['view'];

        unset($query['view']);
    }

    return $segments;
}

/**
 * @param   array
 *
 * @return  array
 */
function JSolrSearchParseRoute($segments)
{
    $vars = array();

    $vars['option'] = 'com_jsolrsearch';

    if ($item = array_shift($segments)) {
        $vars['view'] = $item;
    }

    return $vars;
}
