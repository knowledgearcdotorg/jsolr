<?php
/**
 * A model that provides facet browsing.
 *
 * @package     JSolr
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.application.component.modellist');

class JSolrSearchModelBrowse extends JModelList
{
    public function populateState($ordering = null, $direction = null)
    {
        // If the context is set, assume that stateful lists are used.
        if ($this->context) {
            $app = JFactory::getApplication('site');

            // Load the parameters.
            $params = $app->getParams();
            $this->setState('params', $params);

            $this->setState('facet.range', $app->input->get('range', null, 'string'));

            $this->setState('facet.start', $app->input->get('start', null, 'string'));

            $this->setState('facet.end', $app->input->get('end', null, 'string'));

            $this->setState('facet.gap', $app->input->get('gap', null, 'string'));

            $array = $app->input->get('facet', null, 'string');

            if ($array) {
                $array = explode(',', $array);
            }

            $this->setState('facet.fields', $array);

            $this->setState('facet.prefix', $app->input->get('prefix', null, 'string'));

            $this->setState('facet.operators', $this->getOperators());

            $this->setState('list.limit', $app->input->get('limit', $params->get('list_limit'), 'uint'));

            $this->setState('list.start', $app->input->get('start', 0, 'uint'));
        }
    }

    public function getItems()
    {
        $params = JComponentHelper::getParams($this->get('option'), true);

        $list = array();
        $facetParams = array();
        $filters = array();
        $array = array();

        $facetFields = $this->getState('facet.fields');

        if ($this->getState('facet.range')) {
            $facetFields[] = $this->getState('facet.range');
        }

        if (!$this->getState('params')->get('override_schema', 0)) {
            $access = implode(' OR ', JFactory::getUser()->getAuthorisedViewLevels());

            if ($access) {
                $access = 'access:'.'('.$access.') OR null';
                $filters[] = $access;
            }

            $filters[] = $this->getLanguageFilter();
        }

        // get context.
        if ($this->getState('query.o', null)) {
            foreach ($dispatcher->trigger('onJSolrSearchRegisterPlugin') as $result) {
                if (JArrayHelper::getValue($result, 'name') == $this->getState('query.o', null)) {
                    $filters = array_merge($filters, array('context:'.JArrayHelper::getValue($result, 'context')));
                }
            }
        }

        if ($prefix = $this->getState('facet.prefix')) {
            $facetParams['facet.prefix'] = $prefix;
        }

        JPluginHelper::importPlugin("jsolrsearch");
        $dispatcher = JDispatcher::getInstance();

        try {
            $query = \JSolr\Search\Factory::getQuery("*:*")
                ->useQueryParser('edismax')
                ->mergeParams($facetParams)
                ->filters($filters)
                ->facet(0, 'index', -1)
                ->rows(0);

            if ($this->getState('facet.fields')) {
                $query->facetFields($this->getState('facet.fields'));
            }

            if ($this->getState('facet.range')) {
                $query->facetRange(
                    $this->getState('facet.range'),
                    $this->getState('facet.start'),
                    $this->getState('facet.end'),
                    $this->getState('facet.gap'));
            }

            $results = $query->search();

            foreach ($facetFields as $field) {
                $array[$field] = array();

                if (isset($results->getFacets()->{$field})) {
                    foreach ($results->getFacets()->{$field} as $key=>$value) {
                        $array[$field][$key] = $value;
                    }
                }

                if (isset($results->getFacetRanges()->{$field})) {
                    $counts = (array)$results->getFacetRanges()->{$field}->counts;
                    foreach ($counts as $key=>$value) {
                        $array[$field][$key] = $value;
                    }
                }
            }
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'jsolrsearch');
        }

        return $array;
    }

    private function getOperators()
    {
        $operators = array();

        JPluginHelper::importPlugin("jsolrsearch");
        $dispatcher = JDispatcher::getInstance();

        foreach ($dispatcher->trigger("onJSolrSearchOperatorsGet") as $result) {
            $operators = array_merge($operators, $result);
        }

        return $operators;
    }

    private function getLanguageFilter()
    {
        $filter = null;

        // Get language from current tag or use default joomla langugage.
        if (!($lang = JFactory::getLanguage()->getTag())) {
            $lang = JFactory::getLanguage()->getDefault();
        }

        return "(lang:$lang OR lang:\*)";
    }
}
