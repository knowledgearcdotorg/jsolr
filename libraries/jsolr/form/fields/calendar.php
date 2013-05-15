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

jimport('joomla.form.formfield');
jimport('joomla.form.helper');

JFormHelper::loadFieldClass('dropdown');

class JSolrFormFieldCalendar extends JSolrFormFieldDropdown
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 * @since       1.6
	 */
	protected $type = 'JSolr.dropdown';
	
	protected static $_headLoaded = false;

	protected function getInput()
	{
		$this->_head();
		$ul  = '<div class="jsolr-dropdown">';
		$ul .= '<input class="jsolr-dropdown-input" type="hidden" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
		$ul .= '<div class="jsolr-dropdown-label">' . JText::_($this->getValueLabel()) . '</div>';
		$ul .= '<ul class="jsolr-dropdown-list">' . implode('', $this->getOptions()) . '</ul>';
		$ul .= '</div>';
		return $ul;
	}
	
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

			$uri = $this->form->getURI();
			
			if (!empty($key)) {
				$uri->setVar($this->fieldname, $key);
			}
			
			$link = '<a href="'.(string)$uri.'">' . JText::_($value) . '</a>';

			// Create a new option object based on the <option /> element.
			$tmp = '<li class="jsolr-dropdown-option' . ( $selected ? ' jsolr-dropdown-option-selected' : '' ) . '" data-value="' . ((string) $key) . '">' . $link . '</li>';
	
	
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
	
	public function getFilter()
	{
		$filter = null;
	
		switch ($this->value) {
			case 'h':
				$filter = '[NOW-1HOUR TO NOW]';
				break;
				
			case 'd':
				$filter = '[NOW-1DAY TO NOW]';
				break;

			case 'w':
				$filter = '[NOW-7DAY TO NOW]';
				break;

			case 'm':
				$filter = '[NOW-1MONTH TO NOW]';
				break;

			case 'y':
				$filter = '[NOW-1YEAR TO NOW]';
				break;
		}

		if ($filter) {
			$filter = JArrayHelper::getValue($this->element, 'filter') . ':' . $filter;
		}

		return $filter;
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
}