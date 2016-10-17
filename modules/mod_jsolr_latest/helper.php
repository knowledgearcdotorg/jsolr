<?php
/**
 * The JSolr Latest module helper.
 *
 * @package		JSolr.Module
 * @copyright	Copyright (C) 2014-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

use \JSolr\Search\Factory;

class ModJSolrLatestHelper
{
	public function getItems($params)
	{
		$results = array();

        try {
            /*$client = \JSolr\Search\Factory::getClient();

            $query = $client->createSelect();
            $query
                ->setQuery("*:*")
                ->setRows($params->get('count', 5))
                ->addSort($params->get('ordering', 'modified_tdt'), $query::SORT_DESC)
                ->getEDisMax();

            if ($fq = $params->get('fq', null)) {
                $query->createFilterQuery('fq', $fq);
            }*/
            \JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jsolr/models');

            $model = \JModelLegacy::getInstance('Search', 'JSolrModel');
            $model->setState('list.ordering', 'modified_tdt');
            $model->setState('list.direction', 'desc');
            $model->setState('list.limit', 5);

            $params = $model->getState('params');
            $params->set('fq', $fq);
            $model->setState($params);

            return $model->getItems();
        } catch (Exception $e) {

        }

		return $results;
	}
}
