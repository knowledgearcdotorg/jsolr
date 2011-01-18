<?php 
/**
 * A model that provides search capabilities.
 * 
 * @author		$LastChangedBy$
 * @package	Wijiti
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

require_once JPATH_LIBRARIES."/joomla/database/table/section.php";
require_once JPATH_LIBRARIES."/joomla/database/table/category.php";
require_once JPATH_LIBRARIES."/joomla/database/table/content.php";
require_once(JPATH_ROOT.DS."components".DS."com_content".DS."helpers".DS."route.php");
require_once(JPATH_ROOT.DS.'components'.DS.'com_jsolrsearch'.DS.'helpers'.DS.'pagination.php');

require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrsearch".DS."configuration.php");

class JSolrSearchModelSearch extends JModel
{
	var $query;
	
	var $total;
	
	var $pagination;
	
	var $dateRange;
	
	public function __construct()
	{
		parent::__construct();

		$application = JFactory::getApplication("site");
		
		$config = JFactory::getConfig();
				
		$this->setState('limit', $application->getUserStateFromRequest('com_jsolrsearch.limit', 'limit', $config->getValue('config.list_limit'), 'int'));
		$this->setState('limitstart', JRequest::getVar('start', 0, '', 'int'));	

		$this->dateRange = null;
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
	}

	private function _setDateRange($from = null, $to = null)
	{
		$this->dateRange = new stdClass();
		$this->dateRange->from = $from;
		$this->dateRange->to = $to;
	}
	
	public function getQuery()
	{
		return $this->query;
	}
	
	public function getDateRange()
	{
		return $this->dateRange;
	}
	
	function getResults()
	{		
		try {
			$configuration = new JSolrSearchConfig();
			
			$options = array(
	    		'hostname' => $configuration->host,
	    		'login'    => $configuration->username,
	    		'password' => $configuration->password,
	    		'port'     => $configuration->port,
				'path'	   => $configuration->path
			);
						
			$client = new SolrClient($options);
			
			$query = new SolrQuery();
			
			$query->setQuery($this->getQuery());
			
			$filter = $this->getDateQuery();

			if ($filter) {
				$query->addFilterQuery($filter);
			}

			$query->setHighlight(true);
			
			$query->addField('*')->addField('score');
			
			$query->addHighlightField("title");
			$query->addHighlightField("content");
			$query->addHighlightField("metadescription");

			$query->setHighlightFragsize(200, "content");

			$query->setStart($this->getState("limitstart"));
			$query->setRows($this->getState("limit"));
			
			$queryResponse = $client->query($query);

			$response = $queryResponse->getResponse();
			
			$this->setTotal($response->response->numFound);

			$list = array();
			
			if(intval($response->response->numFound) > 0) {
				$i = 0;
				
				foreach ($response->response->docs as $document) {
					$parts = explode(".", $document->id);
					$id = JArrayHelper::getValue($parts, 1, 0);

					$highlighting = JArrayHelper::getValue($response->highlighting, $document->id);

					if ($highlighting->offsetExists("title")) {
        				$hlTitle = JArrayHelper::getValue($highlighting->title, 0);
					} else {
						$hlTitle = $document->title;
					}
					
					$list[$i]->title = strip_tags($hlTitle);
					
					$list[$i]->href = ContentHelperRoute::getArticleRoute($id);
					
					$list[$i]->text = $this->_getHlContent($document, $highlighting, $query->getHighlightFragsize());
					$list[$i]->created = $document->created;
					$list[$i]->section = JArrayHelper::getValue($document->section, 0) . "/" . JArrayHelper::getValue($document->category, 0);
					
					$i++;
				}
			}
        } catch (SolrClientException $e) {
			$log = JLog::getInstance();
			$log->addEntry(array("c-ip"=>"", "comment"=>$e->getMessage()));
		}
		
		return $list;
	}
	
	function setTotal($total)
	{
		$this->total = $total;
	}
	
	function getTotal()
	{
		return $this->total;
	}
	
	function getPagination()
	{
		if (empty($this->pagination)) {
			$this->pagination = new JSolrSearchPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}
	
	function getDateQuery()
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
	
	function _getHlContent($solrDocument, $highlighting, $fragSize)
	{
		$hlContent = null;

		$application = JFactory::getApplication("site");
		
		$params = $application->getParams();
		
		if ($params->get("jsolr_use_hl_metadescription") == 1 && 
			$highlighting->offsetExists("metadescription")) {
			$hlContent = JArrayHelper::getValue($highlighting->metadescription, 0);
		} else {		
			if ($highlighting->offsetExists("content")) {
				$hlContent = JArrayHelper::getValue($highlighting->content, 0);
			} else {
				$hlContent = substr($solrDocument->content, 0, $fragSize);
			}
		}
		
		return $hlContent;
	}	
}