<?php
/**
 * @package     JSolr.Search
 * @subpackage  Helpers
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

use \JSolr\Search\Factory;

/**
 * Provides route building for displaying items using a clean url.
 */
abstract class JSolrHelperRoute
{
    /**
     * Gets the search route from an existing menu item if available.
     *
     * Provides a convient wrapper around the \JSolr\Search\Factory class'
     * getSearchRoute method.
     */
    public static function getSearchRoute($filters = [])
    {
        return Factory::getSearchRoute($filters);
    }
}
