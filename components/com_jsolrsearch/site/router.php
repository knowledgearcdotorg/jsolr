<?php
/**
 * 
 * @author		$LastChangedBy$
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
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

	// if no item id specified, try and get it.
	if (!JArrayHelper::getValue($query, "Itemid")) {
		$application = JFactory::getApplication("site");
		$menus = $application->getMenu();
		$items = $menus->getItems("link", "index.php?option=com_jsolrsearch");

		if (count($items) > 0) {
			$query["Itemid"] = $items[0]->id;
		}
	}
	
	if (isset($query['view'])) {		
		if ($query['view'] != "results" && $query['view'] != "basic") {
			$segments[] = JArrayHelper::getValue($query, "view");
		}
		
		unset($query['view']);
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