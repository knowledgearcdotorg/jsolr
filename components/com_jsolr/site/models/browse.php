<?php
/**
 * A model that provides facet browsing.
 *
 * @package     JSolr
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.application.component.modellist');

class JSolrModelBrowse extends JModelList
{
    public function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication('site');

        // Load the parameters.
        $params = $app->getParams();

        $this->setState('params', $params);

        $array = explode(',', $app->input->get('facet', null, 'string'));

        $this->setState('facet.fields', $array);

        $this->setState('facet.prefix', $app->input->get('prefix', null, 'string'));

        $this->setState('list.limit', $app->input->get('limit', $params->get('list_limit'), 'uint'));

        $this->setState('list.start', $app->input->get('start', 0, 'uint'));
    }

    /**
     * Builds the query and returns the result.
     *
     * @return  \Solarium\QueryType\Select\Result  The query result.
     */
    public function getQuery()
    {
        JPluginHelper::importPlugin("jsolr");
        $dispatcher = JEventDispatcher::getInstance();

        $store = $this->getStoreId();

        if (!isset($this->cache[$store])) {
            $filters = array();

            $filters['lang'] = $this->getLanguageFilter();

            if ($fq = $this->getState('params')->get('fq')) {
                $filters['fq'] = $fq;
            }

            // set access.
            if ($this->getState('params')->get('fq_access', 1)) {
                $viewLevels = JFactory::getUser()->getAuthorisedViewLevels();
                $access = implode(' OR ', array_unique($viewLevels));

                if ($access) {
                    $filters['access'] = 'access_i:'.'('.$access.') OR null';
                }
            }

            try {
                $client = \JSolr\Search\Factory::getClient();

                $query = $client->createSelect();

                $query
                    ->setQuery("*:*")
                    ->getFacetSet()
                        ->setMinCount(1);

                // set applied user filters.
                foreach ($filters as $key=>$value) {
                    $query->createFilterQuery($key)->setQuery($value);
                }

                foreach ($this->getState('facet.fields') as $field) {
                    $query->getFacetSet()->createFacetField($field)->setField($field);
                }

                if ($prefix = $this->getState('facet.prefix')) {
                    $query->getFacetSet()->setPrefix($prefix);
                }

                $dispatcher->trigger('onJSolrBrowseBeforeQuery', array($query, $this->getState()));

                $response = $client->select($query);

                $dispatcher->trigger('onJSolrBrowseAfterQuery', array($response, $this->getState()));

                $this->cache[$store] = $response;
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'jsolr');
            }
        }

        return $this->cache[$store];
    }

    public function getItems()
    {
        $result = $this->getQuery();

        return $result->getFacetSet();
    }

    private function getLanguageFilter()
    {
        $filter = null;

        // Get language from current tag or use default joomla langugage.
        if (!($lang = JFactory::getLanguage()->getTag())) {
            $lang = JFactory::getLanguage()->getDefault();
        }

        return "(lang_s:$lang OR lang_s:\*)";
    }
}
