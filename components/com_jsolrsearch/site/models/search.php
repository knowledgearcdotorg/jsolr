<?php 
/**
 * A model that provides search capabilities.
 * 
 * @package		JSolr
 * @subpackage	Search
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.application.component.modellist');

require_once(JPATH_ROOT.DS."components".DS."com_content".DS."helpers".DS."route.php");

require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrsearch".DS."configuration.php");

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."lib".DS."apache".DS."solr".DS."service.php");
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."lib".DS."apache".DS."solr".DS."query.php");

class JSolrSearchModelSearch extends JModelList
{
	var $query;
	
	protected $total;
	
	protected $qTime = 0;
	
	var $dateRange;
	
	var $lang;
	
	var $category;
	
	var $params;
	
	public function __construct()
	{
		parent::__construct();

		$application = JFactory::getApplication("site");
		
		$config = JFactory::getConfig();
				
		$this->setState('limit', $application->getUserStateFromRequest('com_jsolrsearch.limit', 'limit', $config->getValue('config.list_limit'), 'int'));
		$this->setState('limitstart', JRequest::getVar('start', 0, '', 'int'));	

		$this->dateRange = null;
	}
	
	public function buildQueryURL($params)
	{
		$url = new JURI("index.php");

		$url->setVar("option", $this->get('option'));
		$url->setVar("view", "basic");
		
		foreach ($params as $key=>$value) {
			if ($value != "com_jsolrsearch") {
				if ($key == "task") {
					$url->delVar($key);
				} else {
					$url->setVar($key, $value);
				}
			}
		}
		
		return JRoute::_($url->toString(), false);
	}
	
	
	public function setQueryParams($params)
	{
		$this->query = JArrayHelper::getValue($params, "q", "", "string");
		
		$from = null;
		$to = null;
		
		if (JArrayHelper::getValue($params, "dmin") || 
			JArrayHelper::getValue($params, "dmax"))  {
			$from = JArrayHelper::getValue($params, "dmin", "*", "string");
			$to = JArrayHelper::getValue($params, "dmax", "NOW", "string");
		} else if (JArrayHelper::getValue($params, "qdr")) {
			$from = JArrayHelper::getValue($params, "qdr", "*", "string");
			$to = "NOW";
		}
		
		$this->_setDateRange($from, $to);
		
		$lang = JArrayHelper::getValue($params, "lr", null);
		
		if (!$lang) {
			$lang = JArrayHelper::getValue($params, "lang");
		}
		
		$this->_setParams($params);
		$this->_setLang($lang);
	}

	private function _setDateRange($from = null, $to = null)
	{
		$this->dateRange = new stdClass();
		$this->dateRange->from = $from;
		$this->dateRange->to = $to;
	}
	
	private function _setParams($params)
	{
		$this->params = $params;
	}
	
	private function _setLang($lang)
	{
		$this->lang = $lang;
	}
	
	public function getQuery()
	{
		return $this->query;
	}
	
	public function getDateRange()
	{
		return $this->dateRange;
	}
	
	public function getItems()
	{
		$hl = array();
		$filters = array();
		$list = array();

		$params = JComponentHelper::getParams($this->get('option'), true);
		
		try {
			JPluginHelper::importPlugin("jsolrsearch");
			$dispatcher =& JDispatcher::getInstance();
			
			if ($filter = $this->getDateQuery()) {
				$filters[] = $filter;
			}
			
			if ($filter = $this->_getExtensionFilter()) {
				$filters[] = $filter;
			}

			foreach ($dispatcher->trigger("onJSolrSearchFQAdd", array($this->getParams(), $this->getLanguage(false))) as $result) {
				foreach ($result as $item) {
					if ($item) {
						$filters[] = $item;
					}
				}				
			}

			// Get Highlight fields for results. 
			foreach ($dispatcher->trigger('onJSolrSearchHLAdd', array()) as $result) {
				foreach ($result as $item) {
					$hl[] = $item.'_'.$this->getLanguage(false);
				}
			}
			
			// get query filter params and boosts from plugin.
			$qf = $dispatcher->trigger('onJSolrSearchQFAdd', array());

			$host = $params->get('host');
			
			if ($params->get('username') && $params->get('password')) {
				$host = $params->get('username') . ":" . $params->get('password') . "@" . $url;
			}

			$client = new Apache_Solr_Service($host, $params->get('port'), $params->get('path'));
			$query = Apache_Solr_Query_Factory($this->getQuery(), $client)
				->useQueryParser("dismax")
				->retrieveFields("*,score")
				->filters($filters)
				->highlight(200, "<strong>", "</strong>", 1, implode(" ", $hl))
				->limit($this->getState("limit"))
				->offset($this->getState("limitstart"));

			if (count($qf)) {
				$query->queryFields($this->_buildQF($qf));
			}

			$response = $query->search();

			$headers = json_decode($response->getRawResponse())->responseHeader;

			$this->set('total', $response->response->numFound);
			$this->set('qTime', $headers->QTime);

			if (intval($response->response->numFound) > 0) {
				$i = 0;
				
				foreach ($response->response->docs as $document) {					    				
					$array = $dispatcher->trigger('onJSolrSearchResultPrepare', array(
						$document, 
						$response->highlighting, 
						JArrayHelper::getValue($query->getParams(), "fl.fragsize"),
						$this->getLanguage(false))
					);

					// @todo the following loop is causing problems.
					// When a plugin and the document's extension value match, 
					// the plugin will return a result. Therefore, only one 
					// result should be returned per document.
					foreach ($array as $result) {
						if (count($result)) {
							$list[$i] = $result;

							if ($list[$i]->created) {
								$list[$i]->created = $this->_localizeDateTime($list[$i]->created);
							}
							
							if ($list[$i]->modified) {
								$list[$i]->modified = $this->_localizeDateTime($list[$i]->modified);
							}

							$i++;
						}
					}
				}
			}
        } catch (Exception $e) {
			$log = JLog::getInstance();
			$log->addEntry(array("c-ip"=>"", "comment"=>$e->getMessage()));
		}

		return $list;
	}
	
	public function getTotal()
	{
		return $this->total;
	}
	
	public function getQTime()
	{
		return floatval($this->qTime / 1000);
	}

	public function getDateQuery()
	{	
		$query = "";
		if ($this->dateRange != null) {
			if ($this->dateRange->from) {
				$query = "modified:";
		
				switch ($this->dateRange->from) {
					case "d":
						$query .= "[NOW-1DAY TO NOW]";
						break;
						
					case "w":
						$query .= "[NOW-7DAY TO NOW]";
						break;
						
					case "m":
						$query .= "[NOW-1MONTH TO NOW]";
						break;
						
					case "y":
						$query .= "[NOW-1YEAR TO NOW]";
						break;
						
					default:
						$query .= "[";

						if ($this->dateRange->from != "*") {
							$from = JFactory::getDate($this->dateRange->from);
							$query .= $from->toISO8601();
						} else {
							$query .= "*";	
						}
						
						$query .= " TO ";
						
						$to = JFactory::getDate($this->dateRange->to);
						$query .= $to->toISO8601();
						
						$query .= "]";
						
						break;
				}
			}
		}

		return $query;
	}
	
	/**
	 * Get's the language, either from the item or from the Joomla environment.
	 * 
	 * @param bool $includeRegion True if the region should be included, false 
	 * otherwise. E.g. If true, en-AU would be returned, if false, just en 
	 * would be returned.
	 * 
	 *  @return string The language code.
	 */
	protected function getLanguage($includeRegion = true)
	{
		$lang = $this->lang;

		// Language code must take the form xx-XX.
		if (!$lang || count(explode("-", $lang)) < 2) {
			$lang = JLanguageHelper::detectLanguage();
		}
		
		if ($includeRegion) {
			return $lang;
		} else {
			$parts = explode('-', $lang);
			
			// just return the xx part of the xx-XX language.
			return JArrayHelper::getValue($parts, 0);
		}
	}
	
	private function _localizeDateTime($dateTime)
	{
		$date = JFactory::getDate($dateTime);
		
		return $date->format(JText::_("DATE_FORMAT_LC2"));
	}
	
	/**
	 * Gets a list of extensions as a Solr query filter.
	 * 
	 * Only items which have the same extension parameter as the querystring 
	 * "o" will be filtered if the parameter is specified, otherwise all items 
	 * which match any of the enabled plugins will be filtered.
	 * 
	 * Plugins must be enabled and have the event onJSolrSearchExtensionGet implemented.
	 */
	private function _getExtensionFilter()
	{	
		$extensions = array();
		
		$query = "";		

		if (JRequest::getCmd("o")) {
			$extensions[] = JRequest::getCmd("o");
		} else {
			JPluginHelper::importPlugin("jsolrsearch");
			$dispatcher =& JDispatcher::getInstance();
			
			foreach ($dispatcher->trigger("onJSolrSearchExtensionGet") as $result) {
				$extensions = array_merge($extensions, array_keys($result));
			}
		}
	
		$array = array();

		foreach ($extensions as $extension) {
			if ($extension) {
				$array[] = "extension:".$extension;
			}
		}

		if (count($array) > 1) {
			$query = "(" . implode(" OR ", $array) . ")";
		} else {
			$query = implode("", $array);
		}

		return $query;
	}
	
	/**
	 * Build a query filter string from an arrya of query filters.
	 * 
	 * @param array $qf An array of query filters.
	 */
	private function _buildQF($qf)
	{
		$array = array();

		foreach ($qf as $item) {			
			foreach ($item as $key=>$value) {
				if (!array_key_exists($key, $array)) {
					$array[$key . '_' . $this->getLanguage(false)] = array();
				}
	
				$array[$key . '_' . $this->getLanguage(false)][] = $value;
			}
		}
		
		$reweighted = "";
		
		foreach ($array as $key=>$value) {
			$boost = 0;
			
			foreach ($array[$key] as $item) {
				$boost += $item;
			}
			
			$reweighted .= " " . $key . "^" . $boost;
		}

		return trim($reweighted);
	}
	
	public function getAdvancedSearchURL()
	{
		$url = new JURI("index.php?".http_build_query(JRequest::get('get')));		
		$url->setVar("view", "advanced");

		return JRoute::_($url->toString(), false);
	}
	
	public function getParams()
	{
		return $this->params;
	}
}