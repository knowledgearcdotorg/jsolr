<?php
/**
 * A text box with auto-complete/suggest features.
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

defined('JPATH_BASE') or die;

class JSolrFormFieldTextSuggest extends JFormField
{
	protected $type = 'JSolr.TextSuggest';	

	protected function getInput()
	{
		$this->element['class'] = $this->element['class'] ? (string) $this->element['class'].' jsolr-autocompleter' : 'jsolr-autocompleter';

		$document = JFactory::getDocument();
		$document->addScript(JURI::base().'/media/com_finder/js/autocompleter.js');
		$document->addScript(JURI::base().'/media/com_jsolrsearch/js/typeahead.js');
		$document->addScript(JURI::base().'/media/com_jsolrsearch/js/textsuggest.js');
		
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