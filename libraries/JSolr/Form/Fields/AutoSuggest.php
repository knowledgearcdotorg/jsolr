<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JSolr\Form\Fields;

use \JArrayHelper as JArrayHelper;
use \JFactory as JFactory;

class AutoSuggest extends \JFormField
{
	protected $type = 'JSolr.TextSuggest';

	protected function getInput()
	{
		$this->element['class'] = $this->element['class'] ? (string) $this->element['class'].' jsolr-autocompleter' : 'jsolr-autocompleter';

		$document = JFactory::getDocument();
		//$document->addScript(JURI::base().'/media/com_finder/js/autocompleter.js');
		//$document->addScript(JURI::base().'/media/com_jsolrsearch/js/typeahead.js');
		//$document->addScript(JURI::base().'/media/com_jsolrsearch/js/textsuggest.js');

		$document->addStyleSheet(JURI::base().'/media/com_finder/css/finder.css');

		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$url = JRoute::_("index.php?option=com_jsolrsearch&view=suggest&fields=" . $this->getFields() . "&suggest=" . JArrayHelper::getValue($this->element, 'query') . "&Itemid=0");
		$suggest = ' data-autocompleteurl="' . $url . '" ';

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $suggest . '/>';
	}

	public function getFields()
	{
		return JArrayHelper::getValue($this->element, "fields", 'title_ac^50,author_ac^50');
	}
}