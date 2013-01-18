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
	 * Gets a connection to the Solr Service using the JSolrIndex connection 
	 * settings.
	 * 
	 * @return JSolrApacheSolrService A connection to the Solr Service.
	 */
	public static function getIndexService()
	{
		return self::getService('com_jsolrindex');
	}

	/**
	 * Gets a connection to the Solr Service using the JSolrSearch connection 
	 * settings.
	 * 
	 * @return JSolrApacheSolrService A connection to the Solr Service.
	 */
	public static function getSearchService()
	{
		return self::getService('com_jsolrsearch');
	}
	
	/**
	 * Gets a connection to the Solr Service using settings from the specified 
	 * component.
	 * 
	 * @param string $component The component name. Defaults to com_jsolrsearch.
	 * @return JSolrApacheSolrService A connection to the Solr Service.
	 * @throws Exception An exception when a connection issue occurs.
	 */
	public static function getService($component = 'com_jsolrsearch')
	{
		$lang = JFactory::getLanguage();
		$lang->load('lib_jsolr', JPATH_SITE, JLanguageHelper::detectLanguage(), true);
		
		if (!JComponentHelper::isEnabled($component, true)) {
			throw new Exception(JText::sprintf('LIB_JSOLR_ERROR_COMPONENT_NOT_FOUND', $component), 404);
		}
		
		$params = JComponentHelper::getParams($component, true);
		
		if (!$params) {
			throw new Exception(JText::sprintf('LIB_JSOLR_ERROR_PARAMS_NOT_LOADED', $component), 404);
		}

		$url = $params->get('host');
		
		if ($params->get('username') && $params->get('password')) {
			$url = $params->get('username') . ":" . $params->get('password') . "@" . $url;
		}

		return new JSolrApacheSolrService($url, $params->get('port'), $params->get('path'));
	}
}