<?php
/**
 * Renders a single facet value that can be toggled on/off.
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

jimport('jsolr.form.fields.facets');

class JSolrFormFieldToggle extends JSolrFormFieldFacets
{
	public $type = 'JSolr.Toggle';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
	
		$facets = $this->getFacets();
	
		foreach ($facets as $key=>$value) {
			$class = '';
				
			if ($this->isSelected($key)) {
				$class = ' class="active"';
			}
				
			$count = '';
				
			if (JArrayHelper::getValue($this->element, 'count', false)) {
				$count = '<span>('.$value.')</span>';
			}
				
			$options[] = '<li'.$class.'><a href="'.$this->getFilterURI($key).'">'.JArrayHelper::getValue($this->element, 'value').'</a>'.$count.'</li>';
		}
	
		reset($options);
	
		return $options;
	}
}