<?php 
/**
 * A model that provides facet browsing.
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

jimport('jsolr.search.factory');

class JSolrSearchModelBrowse extends JModelList
{
	public function populateState($ordering = null, $direction = null)
	{
		// If the context is set, assume that stateful lists are used.
		if ($this->context)
		{
			$array = explode(',', JRequest::getString('fields', array()));
			
			$this->setState('facet.fields', $array);
			
			$this->setState('facet.prefix', JRequest::getString('prefix'));
			
			$this->setState('facet.operators', $this->_getOperators());
		}
		
		parent::populateState($ordering, $direction);
	}
	
	public function getItems()
	{
		$params = JComponentHelper::getParams($this->get('option'), true);
		
		$list = array();
		$facetParams = array();
		$filters = array();
		
		if ($filter = $this->_getExtensionFilter()) {
			$filters[] = $filter;
		}
		
		if ($prefix = $this->getState('facet.prefix')) {
			$facetParams['facet.prefix'] = $prefix;
		}
		
		$facetFields = $this->getState('facet.fields');
		
		if (!count($facetFields)) {
			return JError::raiseError('0', JText::_('COM_JSOLRSEARCH_BROWSE_NO_FACET_FIELDS'));
		}
		
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();
		
		foreach ($dispatcher->trigger("onJSolrSearchFacetsParamsAdd", array($this->get('state'))) as $result) {
			foreach ($result as $item) {
				if ($item) {
					$facetParams[] = array_merge($facetParams, $item);
				}
			}				
		}

		try {			
			$host = $params->get('host');
			
			if ($params->get('username') && $params->get('password')) {
				$host = $params->get('username') . ":" . $params->get('password') . "@" . $url;
			}

			$query = JSolrSearchFactory::getQuery("*:*")
				->useQueryParser('edismax')
				->facetFields($facetFields)
				->mergeParams($facetParams)
				->filters($filters)
				->facet()
				->rows(0);

			$response = $query->search();

			$list = json_decode($response->getRawResponse())->facet_counts->facet_fields;
        } catch (Exception $e) {
			$log = JLog::getInstance();
			$log->addEntry(array("c-ip"=>"", "comment"=>$e->getMessage()));
		}

		return $list;
	}
	
	private function _getOperators()
	{
		$operators = array(); 
		
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();		
		
		foreach ($dispatcher->trigger("onJSolrSearchOperatorsGet") as $result) {
			$operators = array_merge($operators, $result);
		}
		
		return $operators;
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
				$extensions = array_merge($extensions, array($result->get('name')=>$result->get('title')));
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
}
