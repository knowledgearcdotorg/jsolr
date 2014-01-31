<?php
/**
 * Renders a calendar search tool form field. Filters the results displayed by 
 * a period of time.
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
 * Michał Kocztorz				<michalkocztorz@wijiti.com>
 * @author Hayden Young <haydenyoung@knowledgearc.com>
 * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.dropdown');
jimport('jsolr.form.fields.filterable');

class JSolrFormFieldCalendar extends JSolrFormFieldDropdown implements JSolrFilterable
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 * @since       1.6
	 */
	protected $type = 'JSolr.Calendar';
	
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		
		// Initialize variables.
		$options = array();
	
		foreach ($this->element->children() as $option) {
			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}
			
			$value = JArrayHelper::getValue($option, 'value', null, 'string');
			
			$selected = $value == $this->value;

			$uri = clone JSolrSearchFactory::getSearchRoute();
			
			if (!empty($value)) {
				$uri->setVar($this->name, $value);
			} else {
				$uri->delVar($this->name);
			}

			$link = '<a role="menuitem" tabindex="-1" href="'.((string)$uri).'">'.JText::_(trim((string)$option)).'</a>';

			$tmp = '<li role="presentation" class="'.( $selected ? 'active' : '').'" data-value="'.$value.'">'.$link.'</li>';
	
	
			// Add the option object to the result set.
			$options[] = $tmp;
		}
	
		reset($options);
	
		return $options;
	}
	
	protected function getValueLabel() 
	{
		foreach ($this->element->children() as $option) {
			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}
			
			$value = JArrayHelper::getValue($option, 'value', null, 'string');
			
			if ($value == $this->value) {
				return (string)$option;
			}
		}

		return '';
	}
	
	/**
	 * Gets the date filter based on the currently selected value.
	 * 
	 * @return array An array containing a single date filter based on the 
	 * currently selected value.
	 * 
	 * @see JSolrFilterable::getFilters()
	 */
	public function getFilters()
	{	
		$filter = null;
		
		foreach ($this->element->children() as $option) {
			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}
			
			$value = JArrayHelper::getValue($option, 'value', null, 'string');
			
			if ($value == $this->value) {
				$filter = JArrayHelper::getValue($option, 'filter', null, 'string');
				continue;
			}
		}
		
		return ($filter) ? array($filter) : array();
	}
	
	public function __get($name)
	{
		switch ($name) {
			case 'filter':
				return JArrayHelper::getValue($this->element, $name, null, 'string');
				break;
								
			case 'filter_quoted':
				if (JArrayHelper::getValue($this->element, $name, null, 'string') === 'true')
					return true;
				else
					return false;
				break;
	
			default:
				return parent::__get($name);
		}
	}
}