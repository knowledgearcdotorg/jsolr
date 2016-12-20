<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Utilities;

use \Joomla\Utilities\ArrayHelper;
use \JFactory as JFactory;

class AliasHelper extends \JObject
{
    /**
     * Gets a Solr index field name based on an alias.
     *
     * @param   string  $alias  The alias to look up.
     *
     * @return  string  The Solr index field name.
     */
    public static function toFieldName($alias)
    {
        $config = \JSolr\Factory::getConfig();

        $lines = explode("\n", trim($config->get('aliases')));

        $aliases = new \Joomla\Registry\Registry;

        foreach ($lines as $line) {
            $parts = explode(":", $line);

            if (isset($parts[0]) && isset($parts[1])) {
                $aliases->set($parts[0], $parts[1]);
            }
        }

        return $aliases->get($alias);
    }

    /**
     * Gets a Solr index field name based on an alias.
     *
     * @param   string  $alias  The alias to look up.
     *
     * @return  string  The Solr index field name.
     */
    public static function getAliases()
    {
        $config = \JSolr\Factory::getConfig();

        $lines = explode("\n", trim($config->get('aliases')));

        $aliases = new \Joomla\Registry\Registry;

        foreach ($lines as $line) {
            $parts = explode(":", $line);

            if (isset($parts[0]) && isset($parts[1])) {
                $aliases->set($parts[0], $parts[1]);
            }
        }

        return $aliases;
    }

    public static function getAliasNames()
    {
        $aliases = array_keys(static::getAliases()->toArray());

        return array_map(function($alias) {return static::addQueryDivider($alias);}, $aliases);
    }

    public static function getFieldNames()
    {
        $fields = array_values(static::getAliases()->toArray());

        return array_map(function($field) {return static::addQueryDivider($field);}, $fields);
    }


    private static function addQueryDivider($value)
    {
        $value = \JSolr\Helper::localize(trim($value));
        return $value.":";
    }
}
