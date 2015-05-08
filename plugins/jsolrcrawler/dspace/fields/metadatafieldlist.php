<?php
/**
 * Provides a list of metadata fields available in DSpace.
 *
 * @package		JSpace
 * @copyright	Copyright (C) 2013-2014 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrCrawler DSpace plugin for Joomla!.

   The JSolrCrawler DSpace plugin for Joomla! is free software: you can redistribute it
   and/or modify it under the terms of the GNU General Public License as
   published by the Free Software Foundation, either version 3 of the License,
   or (at your option) any later version.

   The JSolrCrawler DSpace plugin for Joomla! is distributed in the hope that it will be
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrCrawler DSpace plugin for Joomla!.  If not, see
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<hayden@knowledgearc.com>
 */

defined('JPATH_BASE') or die;

JLoader::import('joomla.form.formfield');
JLoader::import('joomla.form.helper');

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
		$dispatcher = JEventDispatcher::getInstance();

		$result = $dispatcher->trigger("onListMetadataFields");

		$array = JArrayHelper::getValue($result, 0, array());

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