<?php
/**
 * Renders a calendar search tool form field. Filters the results displayed by 
 * a period of time.
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
 * Michał Kocztorz				<michalkocztorz@wijiti.com>
 * @author Hayden Young <haydenyoung@knowledgearc.com>
 * 
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.helper');

jimport('jsolr.form.fields.searchtool');
jimport('jsolr.form.fields.sortable');

class JSolrFormFieldSort extends JSolrFormFieldSearchTool implements JSolrSortable
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 * @since       1.6
	 */
	protected $type = 'JSolr.Sort';
	
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

			$link = '<a role="menuitem" tabindex="-1" href="'.htmlentities((string)$uri, ENT_QUOTES, 'UTF-8').'">'.
				JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)).
				'</a>';

			// Create a new option object based on the <option /> element.
			$tmp = '<li role="presentation" class="' . ( $selected ? 'active' : '' ) . '" data-value="'.$value.'">' . $link . '</li>';
	
	
			// Add the option object to the result set.
			$options[] = $tmp;
		}
	
		reset($options);
	
		return $options;
	}
	
	public function getSort()
	{
		$value = JFactory::getApplication()->input->get($this->name);

		$sort = null;
		
		foreach ($this->element->children() as $option) {			
			if (JArrayHelper::getValue($option, 'value', null, 'string') == $value) {
				$sort = JArrayHelper::getValue($option, 'field', null, 'string');
				
				if (JArrayHelper::getValue($option, 'direction', null, 'string')) {
					$sort .= " ";
					$sort .= JArrayHelper::getValue($option, 'direction', null, 'string');
				}

				continue;
			}
		}
		
		return $sort;
	}
}