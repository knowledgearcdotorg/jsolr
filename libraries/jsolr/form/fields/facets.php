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

jimport('jsolr.form.fields.filterable');

class JSolrFormFieldFacets extends JFormFieldList implements JSolrFilterable
{
	protected $type = 'JSolr.Facets';	
	
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
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
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
	
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		
		$facets = $this->getFacets();

		foreach ($facets as $key=>$value) {
			$class = '';
			
			if ($this->isSelected($key)) {
				$class = ' class="selected"';
			}
			
			$count = '';
			
			if (JArrayHelper::getValue($this->element, 'count', 'false', 'string') === 'true') {
				$count = '<span>('.$value.')</span>';
			}

			$html = array();
			$html[] = '<li'.$class.'>';
			$html[] = '<a href="'.$this->getFilterURI($key).'">'.$key.'</a>';
			$html[] = $count;
			$html[] = '</li>';
			
			$options[] = implode($html);
		}
	
		reset($options);
	
		return $options;
	}

	public function getFilter()
	{
		$value = JFactory::getApplication()->input->getString($this->name);

		return ($value) ? JArrayHelper::getValue($this->element, 'filter').':'.$value : null;
	}
	
	protected function isSelected($facet)
	{
		$url = JFactory::getURI();
		
		$filter = $url->getVar($this->name, null);

		return ($filter == '"'.$facet.'"') ? true : false;
	}
	
	protected function getFilterURI($facet)
	{
		$url = clone JFactory::getURI();
		
		foreach ($url->getQuery(true) as $key=>$value) {
			$url->setVar($key, urlencode($value));
		}
		
		if ($this->isSelected($facet)) {
			$url->delVar($this->name);
		} else {
			$url->setVar($this->name, urlencode('"'.$facet.'"'));
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
		
			case 'filterQuery':
				return 'q_'.$this->filter;
				break;
				
			default:
				return parent::__get($name);
		}
	}
}