<?php
/**
 * Renders a calendar search tool form field. Filters the results displayed by 
 * a period of time.
 * 
 * @package		JSpace
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
 * Michał Kocztorz				<michalkocztorz@wijiti.com>
 * Hayden Young					<haydenyoung@wijiti.com>  
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
	
		foreach ($this->_getDateRanges() as $key=>$value) {			
			$selected = ((string) $key) == $this->value;

			$uri = clone JFactory::getURI();
			$url->delVar('start');
			
			if (!empty($key)) {
				$uri->setVar($this->name, $key);
			} else {
				$uri->delVar($this->name);
			}
			
			$link = '<a role="menuitem" tabindex="-1" href="'.(string)$uri.'">' . JText::_($value) . '</a>';

			$tmp = '<li role="presentation" class="' . ( $selected ? 'active' : '' ) . '" data-value="' . ((string) $key) . '">' . $link . '</li>';
	
	
			// Add the option object to the result set.
			$options[] = $tmp;
		}
	
		reset($options);
	
		return $options;
	}
	
	protected function getValueLabel() 
	{
		$ret = "";
		foreach ($this->_getDateRanges() as $key=>$value)
		{	
			$selected = $key == $this->value;
			if( $selected ) {
				return trim((string) $value);
			}
	
		}
	
		return $ret;
	}
	
	public function getFilters()
	{
		$filters = array();
	
		switch ($this->value) {
			case 'h':
				$filters[] = '[NOW-1HOUR TO NOW]';
				break;
				
			case 'd':
				$filters[] = '[NOW-1DAY TO NOW]';
				break;

			case 'w':
				$filters[] = '[NOW-7DAY TO NOW]';
				break;

			case 'm':
				$filters[] = '[NOW-1MONTH TO NOW]';
				break;

			case 'y':
				$filters[] = '[NOW-1YEAR TO NOW]';
				break;
		}

		return $filters;
	}
	
	private function _getDateRanges()
	{
		$array = array(
				''=>'LIB_JSOLR_CALENDAR_ANYTIME',
				'h'=>'LIB_JSOLR_CALENDAR_HOUR',
				'd'=>'LIB_JSOLR_CALENDAR_DAY',
				'w'=>'LIB_JSOLR_CALENDAR_WEEK',
				'm'=>'LIB_JSOLR_CALENDAR_MONTH',
				'y'=>'LIB_JSOLR_CALENDAR_YEAR'
		);
		
		return $array;
	}
	
	public function __get($name)
	{
		switch ($name) {
			case 'filter':
			case 'query':		
				return JArrayHelper::getValue($this->element, $name, null, 'string');
				break;
	
			default:
				return parent::__get($name);
		}
	}
}