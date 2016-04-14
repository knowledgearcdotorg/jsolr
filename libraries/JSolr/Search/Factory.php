<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Search;

use \JUri as JUri;
use \JFactory as JFactory;
use \JComponentHelper as JComponentHelper;
use \JArrayHelper as JArrayHelper;

class Factory extends \JSolr\Factory
{
    protected static $component = 'com_jsolrsearch';

    protected static $lookup;

    /**
     * Gets an instance of the Query class.
     *
     * @param string $query The initial query to search for.
     * @return Query An instance of the Query class.
     */
    public static function getQuery($query = null)
    {
        $solr = self::getService();

        return new Query($query, $solr);
    }

    /**
     * Builds a route for basic search and results.
     *
     * Each filter should be defined within the array as $array[$key] = $value.
     *
     * @example
     * $query = 'author: Ann-Teresa Young';
     * $filters = array('q_custom'=>'my custom filter');
     *
     * \JSolr\Search\Factory::getSearchRoute($query, $filters);
     *
     * @param array $additionalFilters An array of additional query filters. Each filter
     * should be defined within the array as $array[$key] = $value.
     */
    public static function getSearchRoute($additionalFilters = array())
    {
        $uri = self::getRoute('search', $additionalFilters);

        return $uri;
    }

    public static function getAdvancedSearchRoute($additionalFilters = array())
    {
        $uri = self::getRoute('advanced', $additionalFilters);

        return $uri;
    }

    public static function getQueryRoute($additionalFilters = array())
    {
        $uri = self::getRoute('search', $additionalFilters, true);

        return $uri;
    }

    public static function getQueryRouteWithPlugin($additionalFilters = array())
    {
        $uri = self::getQueryRoute($additionalFilters, true);

        if ($o = JURI::getInstance()->getVar('o')) {
            $uri->setVar('o', $o);
        }

        return $uri;
    }

    protected static function getRoute($view = 'search', $additionalFilters = array(), $queryOnly = false)
    {
        $uri = new JURI('index.php');

        $uri->setVar('option', 'com_jsolrsearch');

        $uri->setVar('view', $view);

        if ($queryOnly) {
            if (JURI::getInstance()->getVar('q')) {
                $uri->setVar('q', urlencode(JURI::getInstance()->getVar('q')));
            }
        } else {
            foreach (JURI::getInstance()->getQuery(true) as $key=>$value) {
                if ($value && $key != 'limitstart' && $key != 'task') {
                    $uri->setVar($key, urlencode($value));
                }
            }
        }

        foreach ($additionalFilters as $key=>$value) {
            $uri->setVar($key, urlencode($value));
        }

        if ($item = self::_findItem($view)) {
            $uri->setVar('Itemid', $item);
        }

        return $uri;
    }

    protected static function _findItem($view = 'search')
    {
        $app = JFactory::getApplication();

        $menus = $app->getMenu('site');

        $found = false;

        $itemId = 0;

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$view])) {
            $component = JComponentHelper::getComponent('com_jsolrsearch');

            $items = $menus->getItems('component_id', $component->id);

            while (($item = current($items)) && !$found) {
                if (isset($item->query) && isset($item->query['view'])) {
                    if ($view == $item->query['view']) {
                        $found = true;

                        self::$lookup[$view] = $item->id;
                    }
                }

                next($items);
            }
        }

        if ($itemId = JArrayHelper::getValue(self::$lookup, $view, null)) {
            return $itemId;
        } else {
            $active = $menus->getActive();

            if ($active) {
                return $active->id;
            }
        }

        return null;
    }
}
