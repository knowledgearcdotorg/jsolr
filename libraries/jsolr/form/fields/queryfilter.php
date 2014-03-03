<?php
/**
 * Provides a hidden field for storing advanced filters not included in Tools.
 * 
 * @package		JSolr
 * @subpackage	Form
 * @copyright	Copyright (C) 2013-2014 KnowledgeARC Ltd. All rights reserved.
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

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
jimport('jsolr.form.fields.filterable');
jimport('jsolr.form.fields.hiddenfilter');

class JSolrFormFieldQueryFilter extends JSolrFormFieldHiddenFilter implements JSolrFilterable
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 */
	protected $type = 'JSolr.QueryFilter';
	
	/**
	 * (non-PHPdoc)
	 * @see JSolrFilterable::getFilters()
	 */
	public function getFilters()
	{
		$application = JFactory::getApplication();
		
		$filters = array();
		
		if ($this->filter && $application->input->getString('q', null)) {
			$filters[] = $this->filter.":".$application->input->getString('q', null);
		}
		
		return (count($filters)) ? $filters : array();
	}

	public function __get($name)
	{
		switch ($name) {
			case 'filter':
				$application = JFactory::getApplication();
				return $application->input->getString($this->name, null);
				break;
	
			default:
				return parent::__get($name);
				break;
		}
	}
}