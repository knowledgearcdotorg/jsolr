<?php
/**
 * Abstract class for all JSolr form fields
 *
 * @author		$LastChangedBy: bartlomiejkielbasa $
 * @package		JSolr
 *
 * @author Bartlomiej Kielbasa <bartlomiejkielbasa@wijiti.com> *
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

abstract class JSolrFormFieldAbstract extends JFormField
{
	/**
	 * Method to update JSolrSearchQuery object
	 */
	abstract public function getFilter();
	
	/**
	 * Returns rendered HTML form field for facet filter
	 */
	abstract protected function getInputFacetFilter();
	
	/**
	 * Returns rendered HTML form field for search tool
	 */
	abstract protected function getInputSearchTool();
	
	/**
	 * Returns rendered field
	 */
	public function getInput()
	{
		$html = $this->preRender();
		
		$html .= $this->form->getType() == JSolrForm::TYPE_FACETFILTERS ? 
				$this->getInputFacetFilter() : $this->getInputSearchTool();
		
		$html .= $this->postRender();
		return $html;
	}
	
	abstract public function fillQuery();
	
	/**
	 * Called before rendering field
	 */
	protected function preRender()
	{
	}
	
	/**
	 * Called after rendering field
	 */
	protected function postRender()
	{
	}
}
