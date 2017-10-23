<?php
/**
 * A model that provides facet browsing.
 *
 * @package        JSolr.Search
 * @subpackage    Model
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     This file is part of the JSolr component for Joomla!.
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.application.component.modellist');

use \JSolr\Search\Factory;

class JSolrModelSuggest extends JModelList
{
    /**
     * @return array
     */
    function getItems()
    {
           $q = JFactory::getApplication()->input->getString('q');

           $fields = JFactory::getApplication()->input->getString('fields');

           $suggest = JFactory::getApplication()->input->getString('suggest');

        $query = \JSolr\Search\Factory::getQuery('*:*')
            ->useQueryParser("edismax")
            ->retrieveFields("*,score")
            ->limit(10) // TODO: move to config
            ->highlight(80, "<strong>", "</strong>", 1);

        $fields = explode(',', $fields);

        $items = array();

        if (empty($q)) {
            return $items;
        }

        foreach ($fields as &$field) {
            $field = explode('^', $field);

            $field = $field[0];

            $field = $field .':' . $q . '*';
        }

        $filters = implode(' OR ', $fields);

        $query->filters($filters);

        try {
            $results = $query->search();

            print_r($results);

            $response = json_decode($results->getSuggestions());

            foreach ($response->docs as $doc) {
                if (is_array($doc->$suggest)) {
                    $v = (array)$doc->$suggest;

                    $items[] = $v[0];
                } else {
                    $items[] = $doc->$suggest;
                }
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
        }

        return $items;
    }
}
