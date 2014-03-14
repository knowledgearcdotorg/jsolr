<?php
/**
 * Renders a list of facets.
 * 
 * @package		JSolr
 * @subpackage	Form
 * @copyright	Copyright (C) 2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSpace component for Joomla!.

   The JSpace component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSpace component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSpace component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * @author Hayden Young <haydenyoung@knowledgearc.com>
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

jimport('jsolr.form.fields.filterable');
jimport('jsolr.form.fields.facetable');

/**
 * The JSolrFormFieldFacets form field builds a list of facets which a user 
 * can then apply to the current search result set to narrow their search 
 * further (I.e. filter).
 */
class JSolrFormFieldDateFacets extends JSolrFormFieldFacets
{
	const RANGE_GAP = "+1YEAR";
	
	protected $type = 'JSolr.DateFacets';
	
	private $ranges = null;
	
	/**
	 * (non-PHPdoc)
	 * @see JSolrFormFieldFacets::getFacetParams()
	 */
	public function getFacetParams()
	{
		$array = array();

		$array[] = array("f.".$this->facet.".facet.range.start"=>$this->min);
		$array[] = array("f.".$this->facet.".facet.range.end"=>$this->max);
		$array[] = array("f.".$this->facet.".facet.range.gap"=>$this->gap);
		$array[] = array("facet.range"=>$this->facet);
		
		return $array;
	}
	
	/**
	 * Gets an array of facets from the current search results (provided via the 
	 * user's session).
	 * 
	 * @return array An array of facets from the current search results.
	 */
	protected function getFacets()
	{
		$array = array();
		
		if ($facet = $this->facet) {
			$app = JFactory::getApplication('site');
			$facets = $app->getUserState('com_jsolrsearch.facets.ranges', null);

			if (isset($facets->{$facet}->counts)) {
				foreach ($facets->{$facet}->counts as $key=>$value) {
					$parts = explode('-', $key);
				
					$array[JArrayHelper::getValue($parts, 0)] = $value;
				}
			}
		}
	
		return $array;
	}

	/**
	 * (non-PHPdoc)
	 * @see JSolrFilterable::getFilters()
	 */
	public function getFilters()
	{
		$cleaned = JString::trim($this->value);
		$array = explode(self::FACET_DELIMITER, $cleaned);
		$filters = array();
		
		if ($cleaned) {
			for ($i = 0; $i < count($filters); $i++) {
				if ($this->exactmatch) {
					$filters[$i] = '"'.$filters[$i].'"';
				}
				
				$filters[$i] = $this->filter.":".$filters[$i].'*';
			}
		}

		return (count($filters)) ? $filters : array();
	}
	
	private function _loadDefaultRanges()
	{
		if (!$this->ranges) {
			$params = array();
			$params["stats"] = "true";
			$params["stats.field"] = $this->filter; 
			
			$query = JSolrSearchFactory::getQuery("*:*")
				->mergeParams($params)
				->rows(0);
			
			$results = $query->search();
			
			$stats = $results->getStats();
			
			if (isset($stats->{$this->filter})) {
				$this->ranges = JArrayHelper::fromObject($stats->{$this->filter});
			}
		}
	}
	
	public function __get($name)
	{
		switch ($name) {
			case 'min':
			case 'max':
				if (!($this->$name = JArrayHelper::getValue($this->element, $name, null, 'string'))) {					
					// lazy load defaults.
					$this->_loadDefaultRanges();
					$this->$name = JArrayHelper::getValue($this->ranges, $name);
				}
				
				return $this->$name;
				break;
			
			case 'gap':
				if (!($this->$name = JArrayHelper::getValue($this->element, $name, null, 'string'))) {
					$this->$name = self::RANGE_GAP;
				}
				
				return $this->$name;
				break;
				
			default:
				return parent::__get($name);
				break;
		}
	}
}