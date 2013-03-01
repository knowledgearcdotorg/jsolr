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


class JSolrFormFieldFacetFilter extends JFormField
{
	protected $type = 'JSolr.FacetFilter';
	
	public function getQuery() {
		//singleton query, test if exists and create if needed
		return $this->form->getQuery();
	}
	
	/**
	 * To be overrided by subclasses.
	 * When submitting a form all fields' updateQuery should be called.
	 * Field may but doesn't have to update the query (if left blank).
	 * 
	 */
	public function updateQuery() {
		$query = $this->getQuery();
		//update the query
	}
}









