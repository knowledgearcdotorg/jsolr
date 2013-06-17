<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.

   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch Component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com>
 */
defined('JPATH_PLATFORM') or die;

/**
 * Form field class for listing available extensions. 
 */
class JFormFieldJSolrSearchExtensionList extends JFormFieldList
{
	protected $type = 'JSolrSearchExtensionList';

	protected function getOptions()
	{
		$options = parent::getOptions();
		
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		foreach ($dispatcher->trigger("onJSolrSearchExtensionGet", array()) as $result) {
			$tmp = JHtml::_(
				'select.option', $result->name,
				JText::alt(trim($result->title), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text'
			);
			
			$options[] = $tmp;
		}
		
		reset($options);

		return $options;
	}				
}