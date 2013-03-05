<?php
/**
 * Supports a text field
 * 
 * @author		$LastChangedBy: bartlomiejkielbasa $
 * @package		JSolr
 *
 * @author Bartlomiej Kielbasa <bartlomiejkielbasa@wijiti.com> * * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.abstract');

class JSolrFormFieldText extends JSolrFormAbstract
{
	protected $type = 'JSolr.Text';
	
	/**
	 * @inheritdoc
	 */
	function getInputFacetFilter()
	{
		return '<input type="text" name="' . $this->name . '" value="" id="' . $this->element['name'] . '" />';
	}
	
	/**
	 * @inheritdoc
	 */
	function getInputSearchTool()
	{
		return '<input type="text" name="' . $this->name . '" value="" id="' . $this->element['name'] . '" />';
	}
}