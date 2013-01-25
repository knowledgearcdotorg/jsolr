<?php
/**
 * @package		JSolr
 * @copyright	Copyright (C) 2013 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr library for Joomla!.

   The JSolr library for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr library for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrIndex component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */
 
// no direct access
defined('_JEXEC') or die();

jimport('joomla.log.log');

jimport('jsolr.apache.solr.service');
jimport('jsolr.apache.solr.document');

/**
 * The JSolr factory class.
 */
class JSolrFactory extends JObject 
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

		return new JSolrApacheSolrService($url, $params->get('port'), $params->get('path'));
	}
	
	/**
	 * Gets the Solr component's configuration parameters..
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