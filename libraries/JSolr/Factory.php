<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JSolr;

/**
 * The JSolr factory class.
 */
abstract class Factory extends \JObject
{
	/**
	 * @static
	 */
	protected static $component;

	/**
	 * @static
	 */
	public static $config = null;

	/**
	 * Gets a connection to the Solr Service using settings from the specified
	 * component.
	 *
	 * @return JSolrApacheSolrService A connection to the Solr Service.
	 * @throws Exception An exception when a connection issue occurs.
	 */
	public static function getService()
	{

		$params = self::getConfig();

		$url = $params->get('host');

		if ($params->get('username') && $params->get('password')) {
			$url = $params->get('username') . ":" . $params->get('password') . "@" . $url;
		}

		$service = new JSolrApacheSolrService($url, $params->get('port'), $params->get('path'));

		if (!$service->ping()) {
			throw new Exception('Could not contact the index server.', 503);
		}

		return $service;
	}

	/**
	 * Gets the Solr component's configuration parameters.
	 *
	 * @return JRegistry The Solr component's configuration parameters.
	 * @throws Exception An exception when the configuration parameters cannot be loaded.
	 */
	public static function getConfig()
	{
		if (!self::$config) {
			$lang = JFactory::getLanguage();

			$lang->load('lib_jsolr', JPATH_SITE, JLanguageHelper::detectLanguage(), true);

			if (!JComponentHelper::isEnabled(static::$component, true)) {
				throw new Exception(JText::sprintf('LIB_JSOLR_ERROR_COMPONENT_NOT_FOUND', static::$component), 404);
			}

			if (!self::$config = JComponentHelper::getParams(static::$component, true)) {
				throw new Exception(JText::sprintf('LIB_JSOLR_ERROR_PARAMS_NOT_LOADED', static::$component), 404);
			}
		}

		return self::$config;
	}
}