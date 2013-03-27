<?php
/**
 * Supports an autocomplete text field
 * 
 * @author		$LastChangedBy: bartlomiejkielbasa $
 * @package		JSolr
 *
 * @author Bartlomiej Kielbasa <bartlomiejkielbasa@wijiti.com> * * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.text');

class JSolrFormFieldTextAutoComplete extends JSolrFormFieldText
{
	protected $type = 'JSolr.TextAutoComplete';	

	function preRender()
	{
		$this->element['class'] = $this->element['class'] ? (string) $this->element['class'].' jsolr-autocompleter' : 'jsolr-autocompleter';

		$document = JFactory::getDocument();
		$document->addScript(JURI::base().'/media/com_finder/js/autocompleter.js');
		$document->addStyleSheet(JURI::base().'/media/com_finder/css/finder.css');
		
		$document->addScript(JURI::base().'/media/com_jsolrsearch/js/jsolrsearch_textautocompleter.js');
	}

		/**
	 * @inheritdoc
	 */
	function getInputFacetFilter()
	{
		$name = (string)$this->element['name'];

		$attr = '';
		
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}
		
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		$attr .= ' data-fields="' . $this->getFields() . '" ';
		
		return '<input type="text" name="' . htmlspecialchars($this->name) . '" value="' .htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" id="' . $this->element['name'] . '" ' . $attr . '/>';
	}
	
	/**
	 * @inheritdoc
	 */
	function getInputSearchTool()
	{
		$name = (string)$this->element['name'];

		$attr = '';
		
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}
		
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		$attr .= ' data-fields="' . $this->getFields() . '" ';
		
		return '<ul><li><input type="text" name="' . htmlspecialchars($this->name) . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" id="' . $this->element['name'] . '" ' . $attr . '/></li></ul>';
	}

	function getFields()
	{
		JArrayHelper::getValue($this->element, "fields", 'title_ac^50,author_ac^50');
	}
}