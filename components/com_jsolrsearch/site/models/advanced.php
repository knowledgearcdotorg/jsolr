<?php 
/**
 * A model that provides advanced search capabilities.
 * 
 * @author		$LastChangedBy$
 * @package		Wijiti
 * @subpackage	JSolrSearch
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');

require_once(JPATH_ROOT.DS."components".DS."com_content".DS."helpers".DS."route.php");

require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrsearch".DS."configuration.php");

class JSolrSearchModelAdvanced extends JModel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function buildQueryURL($params)
	{
		$url = new JURI("index.php");
		
		$url->setVar("option", "com_jsolrsearch");
		$url->setVar("view", "basic");

		$url->setVar("q", $this->buildQuery($params));
		
		foreach ($params as $key=>$value) {			
			switch ($key) {
				case "task":
				case "eq":
				case "aq":
				case "oq0":
				case "oq1":
				case "oq2":
				case "nq":
				case "option":				
					break;

				case "o":
					if ($value) {
						if ($value != "everything") {
							$url->setVar($key, $value);
						}	
					}
					break;					
					
				case "qdr":
					if ($value) {
						$url->setVar($key, $value);	
					}
					break;
									
				default:
					$url->setVar($key, $value);
					break;
			}
		}

		return JRoute::_($url->toString(), false);
	}
	
	public function buildQuery($params)
	{
		$q = "";
	
		if (JArrayHelper::getValue($params, "aq")) {
			$q .= JArrayHelper::getValue($params, "aq");
		}
		
		if (JArrayHelper::getValue($params, "eq")) {
			$q .= "\"".JArrayHelper::getValue($params, "eq")."\"";
		}
		
		$oq = array();
		
		for ($i=0; $i<3; $i++) {		
			if (trim(JArrayHelper::getValue($params, "oq".$i))) {
				$oq[] = trim(JArrayHelper::getValue($params, "oq".$i));
			}
		}

		if (count($oq)) {
			$q .= " " . implode(" or ", $oq);
		}

		if (JArrayHelper::getValue($params, "nq")) {
			$q .= " -".preg_replace('!\s+!', ' -', JArrayHelper::getValue($params, "nq"));			
		}

		return trim($q);
	}
	
	public function getAndQuery()
	{
		$query = preg_replace('/(".*?")/', '', JRequest::getString("q"), 1);
		$query = preg_replace('/(-.*?)(?=\s|$)/', '', $query);
		$query = trim(preg_replace('/"/', "", $query));
		
		return $query;
	}
	
	public function getExactQuery()
	{
		$matches = array();
		preg_match("/(?<=\").*?(?=\")/", JRequest::getString("q", "", "default", 2), $matches);
		
		return JArrayHelper::getValue($matches, 0, "");
	}

	public function getOrQuery()
	{
		return "";
	}
	
	public function getNotQuery()
	{
		$array = explode(" ", JRequest::getString("q"));
		$nq = "";
		
		$matches = array();
		preg_match_all("/(?<=-)(.*?)(?=\s|$)/", JRequest::getString("q"), $matches);
		
		foreach (JArrayHelper::getValue($matches, 0) as $item) {
			$nq .= " $item";
		}

		return trim($nq);
	}
	
	public function getFilterOptions()
	{
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		$default = array("everything"=>JText::_("COM_JSOLRSEARCH_OPTION_EVERYTHING"));
		
		$array = $dispatcher->trigger('onFilterOptions', array());

		$array = array_merge($default, JArrayHelper::getValue($array, 0, array()));

		$options = array();
		
		foreach ($array as $key=>$value) {
			$option = new stdClass();
			$option->value = $key;
			$option->text = $value;
			
			$options[] = $option;
		}
		
		return $options;
	}
	
	public function getLanguages()
	{
		$langs = JLanguage::getKnownLanguages();

		$options = array();
		
		foreach ($langs as $lang) {
			$option = new stdClass();
			$option->value = JArrayHelper::getValue($lang, "tag");
			$option->text = JArrayHelper::getValue($lang, "name");
			
			$options[] = $option;
		}
		
		return $options;		
	}
	
	public function getDateRanges()
	{
		$array = array(
			""=>JText::_("COM_JSOLR_QDR_ANYTIME"),
			"d"=>JText::_("COM_JSOLR_QDR_1D"),
			"w"=>JText::_("COM_JSOLR_QDR_1W"),
			"m"=>JText::_("COM_JSOLR_QDR_1M"),
			"y"=>JText::_("COM_JSOLR_QDR_1Y")
		);
							
		
		$options = array();
		
		foreach ($array as $key=>$value) {
			$option = new stdClass();
			$option->value = $key;
			$option->text = $value;
			
			$options[] = $option;
		}
		
		return $options;		
	}
	
	public function getFilterOption()
	{
		return JRequest::getWord("o", "everything");
	}
	
	public function getLanguage()
	{		
		return JRequest::getString("lr", JLanguageHelper::detectLanguage());
	}
	
	public function getDateRange()
	{
		return JRequest::getString("qdr", "");
	} 	
}
