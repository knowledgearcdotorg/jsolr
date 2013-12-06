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
	 * Gets the search url.
	 *
	 * @param bool $queryOnly True if the initial query should be returned,
	 * false otherwise. Defaults to false.
	 *
	 * @return JURI The search url.
	 */
	public static function getURI($queryOnly = false)
	{
		$uri = new JURI('index.php');

		if (JURI::getInstance()->getVar('q')) {
			$uri->setVar('q', urlencode(JURI::getInstance()->getVar('q')));
		}
		
		$uri->setVar("option", "com_jsolrsearch");
		$uri->setVar("view", "basic");
		$uri->setVar("Itemid", JFactory::getApplication()->input->get('Itemid', null, 'int'));
		
		foreach (JURI::getInstance()->getQuery(true) as $key=>$value) {
			if (!$queryOnly) {				
				if (trim($value) && $key != 'limitstart' && $key != 'task') {
					$uri->setVar($key, $value);
				}
			}
		}
	
		return $uri;
	}
}