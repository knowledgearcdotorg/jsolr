<?php
/**
 * Supports a date picker.
 * 
 * @author		$LastChangedBy: bartlomiejkielbasa $
 * @package		JSolr
 * 
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Michał Kocztorz <michalkocztorz@wijiti.com> 
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com> 
 * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.rangeabstract');

//JSorl prefix!
class JSolrFormFieldDateRange extends JSolrFormFieldRangeAbstract
{
	protected $type = 'JSolr.DateRange'; //JSorl prefix
	
	/**
	 * @inheritdoc
	 */
	protected function preRender()
	{
		$document = JFactory::getDocument();
		$document->addScript('/media/com_jsolrsearch/js/jquery/jquery.js');
		$document->addScript('/media/com_jsolrsearch/js/jquery-ui/jquery-ui-1.10.1.custom.min.js');
		
		$document->addStyleSheet('/media/com_jsolrsearch/css/ui-lightness/jquery-ui-1.10.1.custom.min.css');
	}
	
	/**
	 * @inheritdoc
	 */
	public function getInputFacetFilter()
	{
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
		$html = ''; // TODO
		
		return $html;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getInputSearchTool()
	{
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
		
		$html = ''; // TODO
		
		return $html;
	}
	
	protected function postRender()
	{
		if ($this->useCustomRange()) {
			$name = $this->name;
			$id = $this->element['name'];
			/**
			 * A value must be provided and used in this html code.
			 * Usually $this->value contins value, but in our case we may want to consider
			 * parsing query provided by $this->getQuery() and filling in value field. Depends on
			 * query object.
			 * We actually have one value for entire form (a query).
			 *
			 */
			
			$html = <<< HTML
			
<script>
$(function() {
	$( "#{$id}_from" ).datepicker({
	defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
	onClose: function( selectedDate ) {
		$( "#{$id}_to" ).datepicker( "option", "minDate", selectedDate );
	}
});
$( "#{$id}_to" ).datepicker({
	defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
	onClose: function( selectedDate ) {
		$( "#{$id}_from" ).datepicker( "option", "maxDate", selectedDate );
	}
	});
});
</script>
			
<input type="text" id="{$id}_from" name="{$name}[from]" />
<input type="text" id="{$id}_to" name="{$name}[to]" />
HTML;
			return $html;
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function getFilter()
	{
		return '';
	}
	
	/**
	 * @inheritdoc
	 */
	protected function getDefaultOptions()
	{
		return array(COM_JSOLRSEARCH_DATERANGE_ANYTIME => '',COM_JSOLRSEARCH_DATERANGE_24_HOURS => 'd', COM_JSOLRSEARCH_DATERANGE_PREV_WEEK => 'w', COM_JSOLRSEARCH_DATERANGE_PREV_MONTH => 'm', COM_JSOLRSEARCH_DATERANGE_PREV_YEAR => 'y');
	}
}

