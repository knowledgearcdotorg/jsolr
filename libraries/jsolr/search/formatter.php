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

class JSolrSearchFormatter extends JObject
{
	public static function highlight($key, $field, $default = null)
	{
		$highlighting = JFactory::getApplication()->getUserState('com_jsolrsearch.highlighting');

		$array = array();

		if (isset($highlighting->$key->$field)) {
			foreach ($highlighting->$key->$field as $item) {
				$array[] = $item;
			}
				
			return implode("...", $array);
		}
		
		return $default;
	}
	
	public static function datetime($raw, $format = "DATE_FORMAT_LC2")
	{
		return JFactory::getDate()->format(JText::_($format));
	}
}