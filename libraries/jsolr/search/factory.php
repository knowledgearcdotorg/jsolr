<?php
/**
 * @package		JSolr
 * @subpackage	Search
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

jimport('jsolr.factory');
jimport('jsolr.search.query');

class JSolrSearchFactory extends JSolrFactory 
{
	protected static $component = 'com_jsolrsearch';
	
	protected static $lookup;

	/**
	 * Gets an instance of the JSolrSearchQuery class.
	 * 
	 * @param string $query The initial query to search for.
	 * @return JSolrSearchQuery An instance of the JSolrSearchQuery class.
	 */
	public static function getQuery($query = null)
	{
		$solr = self::getService();
		
		return new JSolrSearchQuery($query, $solr);
	}
	
	/**
	 * Builds a route for basic search and results.
	 *
	 * Each filter should be defined within the array as $array[$key] = $value.
	 *
	 * @example
	 * $query = 'author: Ann-Teresa Young';
	 * $filters = array('q_custom'=>'my custom filter');
	 *
	 * JSolrSearchFactory::getSearchRoute($query, $filters);
	 *
	 * @param array $additionalFilters An array of additional query filters. Each filter
	 * should be defined within the array as $array[$key] = $value.
	 */
	public static function getSearchRoute($additionalFilters = array())
	{
		$uri = self::getRoute('basic', $additionalFilters);
		
		return $uri;
	}
	
	public static function getAdvancedSearchRoute($additionalFilters = array())
	{
		$uri = self::getRoute('advanced', $additionalFilters);
	
		return $uri;
	}
	
	public static function getQueryRoute($additionalFilters = array())
	{
		$uri = self::getRoute('basic', $additionalFilters, true);
	
		return $uri;
	}
	
	protected static function getRoute($view = 'basic', $additionalFilters = array(), $queryOnly = false)
	{
		$uri = new JURI('index.php');
		$uri->setVar('option', 'com_jsolrsearch');
		$uri->setVar('view', $view);
	
		if ($queryOnly) {
			if (JURI::getInstance()->getVar('q')) {
				$uri->setVar('q', urlencode(JURI::getInstance()->getVar('q')));
			}
		} else {
			foreach (JURI::getInstance()->getQuery(true) as $key=>$value) {
				if ($value && $key != 'limitstart' && $key != 'task') {
					$uri->setVar($key, urlencode($value));
				}
			}
		}
		
		foreach ($additionalFilters as $key=>$value) {
			$uri->setVar($key, urlencode($value));
		}
	
		if ($item = self::_findItem($view)) {
			$uri->setVar('Itemid', $item);
		}

		return $uri;
	}
	
	protected static function _findItem($view = 'basic')
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu('site');
		$found = false;
		$itemId = 0;
	
		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$view])) {
	
			$component = JComponentHelper::getComponent('com_jsolrsearch');
			$items = $menus->getItems('component_id', $component->id);

			while (($item = current($items)) && !$found) {
				if (isset($item->query) && isset($item->query['view'])) {					
					if ($view == $item->query['view']) {
						$found = true;
						self::$lookup[$view] = $item->id;
					}
				}
	
				next($items);
			}
		}

		if ($itemId = JArrayHelper::getValue(self::$lookup, $view, null)) {
			return $itemId;
		} else {
			$active = $menus->getActive();
				
			if ($active) {
				return $active->id;
			}
		}
	
		return null;
	}
}