<?php
/**
 * The JSolr Latest module helper.
 *
 * @package		JSolr.Module
 * @copyright	Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

use \JSolr\Search\Factory;

class ModJSolrLatestHelper
{
    public static function getItems($params)
    {
        try {
            \JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jsolr/models');

            $model = \JModelLegacy::getInstance(
                'Search',
                'JSolrModel',
                array('ignore_request'=>true));

            $model->setState('query.q', '*:*');
            $model->setState('list.ordering', $params->get('ordering', 'modified_dt'));
            $model->setState('list.direction', 'desc');
            $model->setState('list.limit', 5);

            $params = new \Joomla\Registry\Registry;

            if ($fq = $params->get('fq')) {
                $params->set('fq', $fq);
            }

            $model->setState($params);

            return $model->getItems();
        } catch (Exception $e) {
            JLog::addLogger(array());
            JLog::add($e->getCode().' '.$e->getMessage(), JLog::ERROR, 'jsolr');
            JLog::add((string)$e, JLog::ERROR, 'jsolr');
        }
	}
}
