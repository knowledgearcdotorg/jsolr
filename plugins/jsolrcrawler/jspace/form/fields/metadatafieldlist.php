<?php
/**
 * Provides a list of metadata fields available in DSpace.
 * 
 * @package		JSpace
 * @copyright	Copyright (C) 2013 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrCrawler JSpace plugin for Joomla!.

   The JSolrCrawler JSpace plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrCrawler JSpace plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrCrawler JSpace plugin for Joomla!.  If not, see 
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

JFormHelper::loadFieldClass('list');

class JSolrFormFieldMetadataFieldList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var         string
	 */
	protected $type = 'JSolr.MetadataFieldList';
	
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		JPluginHelper::importPlugin("jsolrcrawler");
		$dispatcher =& JDispatcher::getInstance();
		
		$array = JArrayHelper::getValue($dispatcher->trigger("onListMetadataFields"), 0);
		
		foreach ($array as $item) {
			$tmp = JHtml::_(
				'select.option', $item->name,
				JText::alt(trim($item->name), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text'
			);
			
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}