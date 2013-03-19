<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2011-2012 Wijiti Pty Ltd. All rights reserved.
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

/**
 * @param	array
 * @return	array
 */
function JSolrSearchBuildRoute(&$query)
{
	$segments = array();

	// get a menu item based on Itemid or currently active
	$app  = JFactory::getApplication();
	$menu = $app->getMenu();
	$params = JComponentHelper::getParams('com_jsolrsearch');
	$advanced = $params->get('sef_advanced_link', 0);

	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
	} else {
		$menuItem = $menu->getItem($query['Itemid']);
	}

	$mView  = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];

	if (isset($query['view'])) {
		$view = $query['view'];

		if (empty($query['Itemid'])) {
			$segments[] = $query['view'];
		}
        unset($query['view']);
	}

	if (isset($view) && ($mView == $view)) {
		unset($query['view']);
		return $segments;
	}

	return $segments;
}

/**
 * @param	array
 * @return	array
 */
function JSolrSearchParseRoute($segments)
{
        $vars = array();

        $vars['option'] = 'com_jsolrsearch';
        
        if ($item = array_shift($segments)) {
			$vars['view'] = $item;
        }
        
        return $vars;
}