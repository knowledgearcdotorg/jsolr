<?php
/**
 * @package		JSolr.Search
 * @subpackage	Form
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@knowledgearc.com>
 */
defined('JPATH_PLATFORM') or die;

/**
 * Form field class for listing facet fields.
 *
 * Facet fields are retrieved from JSolrSearch plugins (if configured in the
 * operators class variable).
 */
class JFormFieldJSolrSearchFacetFieldList extends JFormFieldList
{
	protected $type = 'JSolrSearchFacetFieldList';

	protected function getOptions()
	{
		$options = parent::getOptions();

		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		foreach ($dispatcher->trigger("onJSolrSearchOperatorsGet", array()) as $result) {
			foreach ($result as $key=>$value) {
				$tmp = JHtml::_(
					'select.option', (string)$key,
					JText::alt(trim((string)$value), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text'
				);

				$options[] = $tmp;
			}
		}

		reset($options);

		return $options;
	}
}