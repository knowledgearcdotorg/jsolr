<?php
/**
 * Provides a hidden field for storing filters not included in Tools.
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
jimport('joomla.form.helper');
jimport('jsolr.form.fields.filterable');

JFormHelper::loadFieldClass('hidden');

class JSolrFormFieldHiddenFilter extends JFormFieldHidden implements JSolrFilterable
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 */
	protected $type = 'JSolr.HiddenFilter';
	
	/**
	 * (non-PHPdoc)
	 * @see JSolrFilterable::getFilters()
	 */
	public function getFilters()
	{
		$application = JFactory::getApplication();
		
		$filters = array();

        if ($value = $application->input->getString($this->name, null)) {
            $filters[] = $this->filter.":".$value;
        }
        
        return (count($filters)) ? $filters : array();
	}
	
	/**
	 * Gets the remove url for the applied hidden filter.
	 *
	 * @return string The filter uri for the current facet.
	 */
	protected function getFilterURI()
	{
		$url = clone JSolrSearchFactory::getSearchRoute();
		
		$url->delVar($this->name);
		
		return (string)$url;
	}

	public function __get($name)
	{
		switch ($name) {
			case 'filter':
				return JArrayHelper::getValue($this->element, $name, null, 'string');
				break;

			case 'label':
                // Give users two options for labels:
                // 1. define a label with the selected value available,
                // 2. no label will result in a name+value lang constant.
                $label = JArrayHelper::getValue($this->element, $name, null, 'string');
                
                if ($label)
                {
                    return JText::sprintf($label, $this->value);
                }
                else
                {
                    return JText::_('COM_JSOLRSEARCH_FILTER_'.JString::strtoupper($this->name)."_".str_replace(' ', '', JString::strtoupper($this->value)));
                }
                
				break;
				
			default:
				return parent::__get($name);
		}
	}
}