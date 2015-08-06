<?php
/**
 * @package        JSolr.Module
 * @copyright    Copyright (C) 2014 KnowledgeARC Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

abstract class ModJSolrConnectionMonitorHelper
{
    public static function getIndexAjax()
    {
        jimport('joomla.application.module.helper');

        $module = JModuleHelper::getModule('jsolrconnectionmonitor');

        $params = new \Joomla\Registry\Registry();

        $params->loadString($module->params);

        $index = self::getIndex($params);

        $language = JFactory::getLanguage();

        $loaded = $language->load('mod_jsolrconnectionmonitor', JPATH_ADMINISTRATOR, null, true);

        if (JArrayHelper::getValue($index, 'status')) {
            $index['statusText'] = JText::_("MOD_JSOLRCONNECTIONMONITOR_CONNECTED");
        } else {
            $index['statusText'] = JText::_("MOD_JSOLRCONNECTIONMONITOR_NOT_CONNECTED");
        }

        if ($statistics = JArrayHelper::getValue($index, 'statistics')) {
            if (isset($statistics->lastModified)) {
                $index['statistics']->lastModifiedFormatted = JHtml::_('date', $statistics->lastModified, JText::_('DATE_FORMAT_LC2'));
            }
        }

        return ($index) ? $index : false;
    }

    public static function getIndex($params)
    {
        $index = array();

        try {
            $config = self::getConfig($params);

            $index['status'] = self::isConnected($params);

            $index['host'] = $config->get('host', null);

            $index['port'] = $config->get('port', null);

            $index['path'] = $config->get('path', null);

            $index['libraries']['curl'] = self::isCurlInstalled();

            $index['libraries']['jsolr'] = self::isJSolrLibraryInstalled();

            if (count($statistics = self::getStatistics($params)) > 0) {
                $index['statistics'] = $statistics;
            }

            $index['extractor'] = self::getTikaSettings($params);
        } catch (Exception $e) {
            // do nothing.
        }

        return $index;
    }

    public static function isConnected($params)
    {
        try {
            $client = self::_getService($params);

            $response = $client->ping(10);

            if ($response === false) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            if ($e->getCode() == 503) {
                return false;
            } else {
                throw $e;
            }
        }
    }

    private static function _getService($params)
    {
        $class = '\JSolr\Index\Factory';

        if ($params->get('service') == 1) {
            $class = '\JSolr\Search\Factory';
        }

        return $class::getService();
    }

    private static function getConfig($params)
    {
        JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

        $class = '\JSolr\Index\Factory';

        if ($params->get('service') == 1) {
            $class = '\JSolr\Search\Factory';
        }

        return $class::getConfig();
    }

    public static function isCurlInstalled()
    {
        return function_exists('curl_version');
    }

    public static function isJSolrLibraryInstalled()
    {
        return class_exists('\JSolr\Factory');
    }

    public static function getTikaSettings($params)
    {
        $settings = array();

        $config = self::getConfig($params);

        if (!$params->get('service')) {
            if ($config->get('index')) {
                $settings['type'] = $config->get('extractor');

                if ($settings['type'] == 'local') {
                    $settings['path'] = $config->get('local_tika_app_path');
                }
            }
        }

        return $settings;
    }

    public static function getStatistics($params)
    {
        $statistics = array();

        if (self::isConnected($params) &&
            self::isCurlInstalled() &&
            self::isJSolrLibraryInstalled()) {
            $client = self::_getService($params);

            $response = $client->luke();

            $statistics = $response->index;
        }

        return $statistics;
    }
}
