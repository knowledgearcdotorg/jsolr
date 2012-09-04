<?php
/**
 * @package		JSolr
 * @subpackage  Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.

   The JSolrSearch component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

/**
 * JSolrSearch Component Route Helper
 *
 * @static
 * @package		JSolr
 * @subpackage  Search
 */
abstract class JSolrSearchHelperRoute
{
	protected static $lookup;

	/**
	 * Builds a route for basic search and results.
	 * 
	 * Each filter should be defined within the array as $array[$key] = $value.
	 * 
	 * @example
	 * $query = 'author: Ann-Teresa Young';
	 * $filters = array('o'=>'com_jspace','q_custom'=>'my custom filter');
	 * 
	 * JSolrSearchHelperRoute::getSearchRoute($query, $filters);
	 * 
	 * @param string $query The query to search on. Do not specify if the 
	 * search page should be shown.
	 * @param array $filters An array of additional query filters. Each filter 
	 * should be defined within the array as $array[$key] = $value.
	 */
	public static function getSearchRoute($query = null, $filters = array())
	{	
		$link = new JURI('index.php');
		$link->setVar('option', 'com_jsolrsearch');
		$link->setVar('view', 'basic');

		if ($query) {
			$link->setVar('q', $query);
		}
		
		foreach ($filters as $key=>$value) {
			$link->setVar($key, $value);
		}
		
		if ($item = self::_findItem('basic')) {
			$link->setVar('Itemid', $item);
		}

		return (string)$link;
	}
	
	protected static function _findItem($view = 'basic')
 	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu('site');
		$found = false;
		$itemId = 0;
		
		// Prepare the reverse lookup array.
		if (self::$lookup === null) {
			self::$lookup = array();

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