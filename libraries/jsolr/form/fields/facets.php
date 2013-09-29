<?php
/**
 * Renders a list of facets.
 * 
 * @package		JSpace
 * @subpackage	form.fields
 * @copyright	Copyright (C) 2013 Wijiti Pty Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@wijiti.com>  
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
class JSolrFormFieldFacets extends JFormFieldList implements JSolrFilterable, JSolrFacetable
{
	const FACET_DELIMITER = '|';
	
	protected $type = 'JSolr.Facets';
	
	protected $facetInput;
	
	/**
	 * Gets an array of facets from the current search results (provided via the 
	 * user's session).
	 * 
	 * @return array An array of facets from the current search results.
	 */
	protected function getFacets()
	{
		if ($facet = $this->facet) {
			$app = JFactory::getApplication('site');
			$facets = $app->getUserState('com_jsolrsearch.facets', null);
			
			if ($facets) {
				if (isset($facets->{$facet})) {
					return $facets->{$facet};
				}
			}
		}
	
		return array();
	}

	/**
	 * (non-PHPdoc)
	 * @see JFormFieldList::getInput()
	 */
	protected function getInput()
	{
		return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
	}
	
	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	public function getFacetInput()
	{
		// Initialize variables.
		$html = array();

		if ($class = JArrayHelper::getValue($this->element, "class", null)) {
			$class = ' class="'.$class.'"';
		}
		
		$html[] = '<ul'.$class.'>';
		foreach ($this->getOptions() as $option) {
			$html[] = $option;
		}
		$html[] = "</ul>";
		
		return implode($html);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JFormFieldList::getOptions()
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		
		$facets = $this->getFacets();		

		foreach ($facets as $key=>$value) {
			$html = array("<li>", "%s", "</li>");
			
			if ($this->isSelected($key)) {
				$html = array("<li class=\"active\">", "%s", "</li>");
			}

			$count = '';
			
			if (JArrayHelper::getValue($this->element, 'count', 'false', 'string') === 'true') {
				$count = '<span>('.$value.')</span>';
			}

			$facet = '<a href="'.$this->getFilterURI($key).'">'.$key.'</a>'.$count;
			
			$options[] = JText::sprintf(implode($html), $facet);
		}
	
		reset($options);
	
		return $options;
	}

	/**
	 * (non-PHPdoc)
	 * @see JSolrFilterable::getFilters()
	 */
	public function getFilters()
	{
		$value = JFactory::getApplication()->input->getString($this->name, null);
		
		return (JString::strlen(trim($value)) > 0) ? explode(self::FACET_DELIMITER, $value) : array();
	}
	
	/**
	 * Evaluates whether the current facet is selected or not.
	 * 
	 * @param string $facet The facet value to evaluate.
	 * @return bool True if the current facet is selected, false otherwise.
	 */
	protected function isSelected($facet)
	{
		$url = JFactory::getURI();
		
		$filters = $this->getFilters();

		$selected = false;
		
		while (($filter = current($filters)) && !$selected) {	
			if ($filter == $facet) {
				$selected = true;
			}
			
			next($filters);
		}

		return $selected;
	}
	
	/**
	 * Gets the filter uri for the current facet.
	 * 
	 * @param string $facet The facet value to build into the filter uri.
	 * @return string The filter uri for the current facet.
	 */
	protected function getFilterURI($facet)
	{
		$url = clone JFactory::getURI();
		
		foreach ($url->getQuery(true) as $key=>$value) {
			$url->setVar($key, urlencode($value));
		}		
		
		$filters = $this->getFilters();
		
		if ($this->isSelected($facet)) {
			if (count($filters) > 1) {
				$found = false;
				
				for ($i = 0; ($filter = current($filters)) && !$found; $i++) {
					if ($filter == $facet) {
						unset($filters[$i]);
						$found = true;
					} else {
						next($filters);
					}
				}
				
				$url->setVar($this->name, urlencode(implode(self::FACET_DELIMITER, $filters)));
				
			} else {
				$url->delVar($this->name);
			}
		} else {
			$filters[] = $facet;
			$url->setVar($this->name, urlencode(implode(self::FACET_DELIMITER, $filters)));
		}
		
		return (string)$url;
	}
	
	public function __get($name)
	{
		switch ($name) {
			case 'filter':
			case 'query':
			case 'facet':
				return JArrayHelper::getValue($this->element, $name, null, 'string');
				break;
				
			case 'exactmatch':
				if (JArrayHelper::getValue($this->element, $name, null, 'string') === 'true')
					return true;
				else 
					return false;
				break;
				
			case 'facetInput':
				// If the input hasn't yet been generated, generate it.
				if (empty($this->facetInput)) {
					$this->facetInput = $this->getFacetInput();
				}
				
				return $this->facetInput;
				break;				
				
			default:
				return parent::__get($name);
		}
	}
}