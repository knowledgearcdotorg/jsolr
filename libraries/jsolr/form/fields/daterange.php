<?php
/**
 * Supports a collection picker.
 * 
 * @author		$LastChangedBy: michalkocztorz $
 * @package		JSolr
 * 
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Michał Kocztorz				<michalkocztorz@wijiti.com> 
 * 
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

//JSorl prefix!
class JSolrFormFieldDateRange extends JFormField
{
	protected $type = 'JSolr.DateRange'; //JSorl prefix
	
	public function getInput() {
		$document = JFactory::getDocument();
		$document->addScript('/media/com_jsolrsearch/js/jquery/jquery.js');
		$document->addScript('/media/com_jsolrsearch/js/jquery-ui/jquery-ui-1.10.1.custom.min.js');
		
		$document->addStyleSheet('/media/com_jsolrsearch/css/ui-lightness/jquery-ui-1.10.1.custom.min.css');
		
		$name = $this->element['name'];
		
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
	$( "#{$name}_from" ).datepicker({
	defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
	onClose: function( selectedDate ) {
		$( "#{$name}_to" ).datepicker( "option", "minDate", selectedDate );
	}
});
$( "#{$name}_to" ).datepicker({
	defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
	onClose: function( selectedDate ) {
		$( "#{$name}_from" ).datepicker( "option", "maxDate", selectedDate );
	}
	});
});
</script>

<input type="text" id="{$name}_from" name="jform[{$name}][from]" />
<input type="text" id="{$name}_to" name="jform[{$name}][to]" />
HTML;
		return $html;
	}
	
	/**
	 * 
	 */
	public function updateQuery() {
		$query = $this->getQuery();
		//update the query
		
	}
}









