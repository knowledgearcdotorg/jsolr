<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JSolr\Form\Fields;

use \JEventDispatcher as JEventDispatcher;
use \JPluginHelper as JPluginHelper;
use \JArrayHelper as JArrayHelper;

\JFormHelper::loadFieldClass('list');

/**
 * Form field class for listing available extensions.
 */
class PluginList extends \JFormFieldList
{
	protected $type = 'JSolr.PluginList';

	protected function getOptions()
	{
		$options = parent::getOptions();

		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JEventDispatcher::getInstance();

		foreach ($dispatcher->trigger("onJSolrSearchRegisterPlugin", array()) as $result) {
			$tmp = JHtml::_(
				'select.option', JArrayHelper::getValue($result, 'name'),
				JText::alt(JArrayHelper::getValue($result, 'label'), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text'
			);

			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}