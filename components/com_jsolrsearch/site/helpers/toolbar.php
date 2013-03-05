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
abstract class JSolrSearchHelperToolbar
{
	public static function showFilter()
	{
		if (trim(JRequest::getString("q", null))) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function getDateLink($range)
	{
		$url = JSolrSearchHelperToolbar::getCleanedSearchURL(array("q", "lr", "option", "o", "view", "Itemid"));
		$text = JText::_("COM_JSOLR_TOOLBAR_".$range);	
		
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
				break;				
		}

		if (JSolrSearchHelperToolbar::isDateRangeSelected($range)) {
			return JHTML::_("link", "#", $text, 'class="jSolrShowRange"');
		} else {
			return JHTML::_("link", $url->toString(), $text);
		}
	}
	
	public static function getCustomRangeLink()
	{
		$text = JText::_("COM_JSOLR_TOOLBAR_CUSTOM_RANGE");
		
		$url = JSolrSearchHelperToolbar::getCleanedSearchURL(array("q", "lr", "option", "o", "view", "Itemid", "dmin", "dmax"));
		
		if (trim($url->getVar("dmin")) || trim($url->getVar("dmax"))) {
			return $text;		
		} else {
			return JHTML::_("link", "#", $text, 'id="jsolr-custom-range-toggle"');	
		}
	}
	
	public static function isCustomRangeSelected()
	{
		$url = JSolrSearchHelperToolbar::getCleanedSearchURL(array("q", "lr", "option", "o", "view", "Itemid", "dmin", "dmax"));
		
		if (trim($url->getVar("dmin")) || trim($url->getVar("dmax"))) {
			return true;		
		} else {
			return false;	
		}
	}
	
	public static function isDateRangeSelected($range)
	{
		$url = JSolrSearchHelperToolbar::getCleanedSearchURL(array("q", "lr", "option", "o", "view", "Itemid", "dmin", "dmax", "qdr"));

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
	
	public static function getFormURL($allowed = null)
	{
		$array = array("q", "lr", "option", "o", "Itemid");
		
		if ($allowed) {
			$array = $allowed;
		}
		
		$url = JSolrSearchHelperToolbar::getCleanedSearchURL($array);

		$url->setVar("task", "search");
		
		return JRoute::_($url->toString());
	}
	
	/**
	 * Gets a filter options array from each of the enabled JSolrSearch plugins.
	 * 
	 * @return array An array of filter option anchor tags.
	 */
	public static function getFilterOptions()
	{
		$url = JSolrSearchHelperToolbar::getCleanedSearchURL(array("q", "lr", "option", "o", "view", "Itemid"));
		
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		$links = array();
		
		$selected = $url->getVar("o");
		
		if (!$selected) {
			$links[] = JHTML::_("link", "#", JText::_("COM_JSOLR_TOOLBAR_OPTION_EVERYTHING"), array("class"=>"jsolr-fo-selected"));
		} else {
			$url->delVar("o");
			$links[] = JHTML::_("link", JRoute::_($url->toString()), JText::_("COM_JSOLR_TOOLBAR_OPTION_EVERYTHING"));
		}
		
		foreach ($dispatcher->trigger('onJSolrSearchExtensionGet') as $option) {
			if ($option->get('name') == $selected) {
				$links[] = JHTML::_("link", "#", $option->get('title'), array("class"=>"jsolr-fo-selected"));
			} else {
				$url->setVar("o", $option->get('name'));	
				$links[] = JHTML::_("link", JRoute::_($url->toString()), $option->get('title'));
			}
		}

		return $links;
	}
	
	/**
	 * Gets the language code.
	 * 
	 * The code will look like; xx-XX.
	 */
	public static function getLang()
	{
		$lang = JRequest::getString("lr", null);

		if (!trim($lang)) {
			$lang = JRequest::getString("lang");
		}

		// Language code must take the form xx-XX.
		if (!$lang || count(explode("-", $lang)) < 2) {
			$lang = JLanguageHelper::detectLanguage();
		}

		return $lang;
	}
	
	public function getSearchURL()
	{
		return "index.php?".http_build_query(JRequest::get('get'));
	}
	
	/**
	 * Gets the current search url cleaned of any unnecessary query string 
	 * values.
	 * 
	 * @param array $allowed
	 * 
	 * @return JURI A url cleaned of any unnecessary query string values.
	 */
	public static function getCleanedSearchURL($allowed)
	{
		$url = new JURI(JSolrSearchHelperToolbar::getSearchURL());
		
		foreach (JRequest::get('get') as $key=>$value) {
			if (array_search($key, $allowed) === false) {
				$url->delVar($key);
			}
		}

		return $url;
	}
	
	public static function renderFilterContext()
	{		
		$path = null;

		if (JRequest::getString("o")) {
			$application = JFactory::getApplication("site");
			
			$option = JArrayHelper::getValue(explode("_", JRequest::getWord("o"), 2), 1);
			//$themePath = JPATH_THEMES.DS.$application->getTemplate().DS."html".DS."mod_jsolrfilter";	

			//$overridePath = $themePath.DS."plugins".DS."jsolr".$option.DS."filters.php";
			$path = JPath::find(JPATH_PLUGINS.DS."jsolrsearch".DS.$option.DS."filters", "default.php");
		
			// check the html override path first.
			/* if (JFile::exists($overridePath)) {
				$path = $overridePath;
			} */
		} else {
			//$path = JSolrSearchHelperToolbar::getLayoutPath('mod_jsolrfiltertoolbar', "filters");
		}
		
		if ($path) {
			require_once($path);
		}
	}
}
