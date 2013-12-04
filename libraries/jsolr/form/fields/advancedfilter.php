<?php
/**
 * Provides a hidden field for storing advanced filters not included in Tools.
 * 
 * @package		JSolr
 * @subpackage	Form
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

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
jimport('joomla.form.helper');
jimport('jsolr.form.fields.filterable');

JFormHelper::loadFieldClass('hidden');

class JSolrFormFieldAdvancedFilter extends JFormFieldHidden implements JSolrFilterable
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 */
	protected $type = 'JSolr.AdvancedFilter';
	
	private $filter;
	
	public function __construct($form = null)
	{
		parent::__construct($form);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JSolrFilterable::getFilters()
	 */
	public function getFilters()
	{
		$this->filter = JFactory::getApplication()->input->getString($this->name, null);
		
		$filter = null;
		$query = JFactory::getApplication()->input->getString('q', null);

		if ($query && $this->filter) {
			$filter = $query; 
		}
		
		return ($filter) ? array($filter) : array();
	}
	
	public function __get($name)
	{
		switch ($name) {
			case 'filter':
				return $this->$name;
				break;
	
			default:
				return parent::__get($name);
		}
	}
}