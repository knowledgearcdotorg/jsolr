<?php
/**
 * @author		$LastChangedBy$
 * @package		JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr filter module for Joomla!.

   The JSolr filter module for Joomla! is free software: you can 
   redistribute it and/or modify it under the terms of the GNU General Public 
   License as published by the Free Software Foundation, either version 3 of 
   the License, or (at your option) any later version.

   The JSolr filter module for Joomla! is distributed in the hope 
   that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr filter module for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

/**
 * Provides a number of helper methods that provide additional information to the module.
 * 
 * @package JCAR
 * @subpackage Modules
 */
class modJSolrFilterHelper
{
	function __construct($params)
	{
		$this->params = $params;
	}
	
	public function showFilter()
	{
		if (trim(JRequest::getString("q", null))) {
			return true;
		} else {
			return false;
		}
	}
	
	function getDateLink($range)
	{
		$url = new JURI(JURI::current()."?".http_build_query(JRequest::get('get')));
		$text = JText::_("MOD_JSOLRFILTER_".$range);

		// in case the custom range is set.
		$url->delVar("dmin");
		$url->delVar("dmax");
		$url->delVar("fcat");
		$url->delVar("pmin");
		$url->delVar("pmax");		
		
		switch ($range) {
			case "1D":
				$url->setVar("qdr", "d");
				break;
			
			case "1W":
				$url->setVar("qdr", "w");
				break;

			case "1M":
				$url->setVar("qdr", "m");
				break;

			case "1Y":
				$url->setVar("qdr", "y");
				break;

			default:
				$url->delVar("qdr");
				break;				
		}

		if ($this->isDateRangeSelected($range)) {
			return JHTML::_("link", "#", $text);
		} else {
			return JHTML::_("link", $url->toString(), $text);
		}
	}
	
	function getCustomRangeLink()
	{
		$text = JText::_("MOD_JSOLRFILTER_CUSTOM_RANGE");
		
		$url = new JURI(JURI::current()."?".http_build_query(JRequest::get('get'))); 
		
		if (trim($url->getVar("dmin")) || trim($url->getVar("dmax"))) {
			return $text;		
		} else {
			return JHTML::_("link", "#", $text);	
		}
	}
	
	function isCustomRangeSelected()
	{
		$url = new JURI(JURI::current()."?".http_build_query(JRequest::get('get'))); 
		
		if (trim($url->getVar("dmin")) || trim($url->getVar("dmax"))) {
			return true;		
		} else {
			return false;	
		}
	}
	
	function isDateRangeSelected($range)
	{
		$url = new JURI(JURI::current()."?".http_build_query(JRequest::get('get')));

		$selected = false;
		
		switch ($range) {
			case "1D":
				$selected = ($url->getVar("qdr") == "d") ? true : false;
				break;
			
			case "1W":
				$selected = $url->getVar("qdr") == "w" ? true : false;
				break;

			case "1M":
				$selected = $url->getVar("qdr") == "m" ? true : false;
				break;

			case "1Y":
				$selected = $url->getVar("qdr") == "y" ? true : false;
				break;

			default:
				if (!$url->getVar("dmin") || !$url->getVar("dmax")) {
					$selected = $url->getVar("qdr") == "" ? true : false;
				}
				break;
		}

		return $selected;
	}
	
	function getCustomRangeFormURL()
	{
		$url = new JURI(JURI::current()."?".http_build_query(JRequest::get('get')));

		$url->delVar("fcat");
		$url->delVar("pmin");
		$url->delVar("pmax");
		$url->delVar("view");		
		$url->delVar("qdr");
		$url->setVar("task", "search");
		
		return JRoute::_($url->toString());
	}
	
	/**
	 * Gets a filter options array from each of the enabled JSolrSearch plugins.
	 * 
	 * @return array An array of filter option anchor tags.
	 */
	function getFilterOptions()
	{
		$url = new JURI(JURI::current()."?".http_build_query(JRequest::get('get')));
		
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		$links = array();
		
		$url->delVar("fcat");
		$url->delVar("pmin");
		$url->delVar("pmax");
		
		$selected = $url->getVar("o");
		
		if (!$selected) {
			$links[] = JHTML::_("link", "#", JText::_("MOD_JSOLRFILTER_OPTION_EVERYTHING"), array("class"=>"jsolr-fo-selected"));
		} else {
			$url->delVar("o");
			$links[] = JHTML::_("link", $url->toString(), JText::_("MOD_JSOLRFILTER_OPTION_EVERYTHING"));
		}
		
		foreach ($dispatcher->trigger('onFilterOptions', array()) as $options) {
			foreach ($options as $key=>$value) {
				if ($key == $selected) {
					$links[] = JHTML::_("link", "#", $value, array("class"=>"jsolr-fo-selected"));
				} else {
					$url->setVar("o", $key);				
					$links[] = JHTML::_("link", $url->toString(), $value);
				}
			}
		}

		return $links;
	}

	/**
	 * Gets a filter options array from each of the enabled JSolrSearch plugins.
	 * 
	 * @return array An array of filter option anchor tags.
	 */
	function getCategoryLink($value)
	{
		$url = new JURI(JURI::current()."?".http_build_query(JRequest::get('get')));

		$links = array();
		
		$selected = $url->getVar("o");

		if (!$selected) {
			$links[] = JHTML::_("link", "#", JText::_("MOD_JSOLRFILTER_OPTION_EVERYTHING"), array("class"=>"jsolr-fo-selected"));
		} else {
			$url->delVar("o");
			$links[] = JHTML::_("link", $url->toString(), JText::_("MOD_JSOLRFILTER_OPTION_EVERYTHING"));
		}

		foreach ($dispatcher->trigger('onFilterOptions', array()) as $options) {
			foreach ($options as $key=>$value) {
				if ($key == $selected) {
					$links[] = JHTML::_("link", "#", $value, array("class"=>"jsolr-fo-selected"));
				} else {
					$url->setVar("o", $key);				
					$links[] = JHTML::_("link", $url->toString(), $value);
				}
			}
		}

		return $links;
	}
	
	function getSolrClient()
	{
		require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrsearch".DS."configuration.php");
		
		$configuration = new JSolrSearchConfig();
		
		$options = array(
    		'hostname' => $configuration->host,
    		'login'    => $configuration->username,
    		'password' => $configuration->password,
    		'port'     => $configuration->port,
			'path'	   => $configuration->path
		);
				
		$client = new SolrClient($options);
		
		return $client;
	}
	
	/**
	 * Gets the modified language code for use by the Solr search engine.
	 * 
	 * The code will look like; _xx_XX.
	 */
	public function getLang()
	{
		$lang = JRequest::getString("lr", null);

		if (!trim($lang)) {
			$lang = JRequest::getString("lang");
		}

		// Language code must take the form xx-XX.
		if (!$lang || count(explode("-", $lang)) < 2) {
			$lang = JLanguageHelper::detectLanguage();
		}

		if ($lang) {
			$lang = "_" . $lang;
		}
		
		return str_replace("-", "_", $lang);
	}
	
	public function getSearchURL()
	{
		return JURI::current()."?".http_build_query(JRequest::get('get'));
	}
}