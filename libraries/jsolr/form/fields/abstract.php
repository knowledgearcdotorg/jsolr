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
     * Method to get value of form field as a text. Maybe used to displaying it on frontend as current selected options
     */
	abstract public function getValueText();
	
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
	
	/**
	 * Returns label for facet filter. Defaults to parent::getLabel if not overridden.
	 */
	protected function getLabelFacetFilter() {
		return parent::getLabel();
	}
	
	/**
	 * Returns label for search tool. Defaults to parent::getLabel if not overridden.
	 */
	protected function getLabelSearchTool() {
		return parent::getLabel();
	}

	/**
	 * Returns rendered field
	 */
	public function getLabel()
	{
		$html = $this->preRenderLabel(); 
		
		$html .= $this->form->getType() == JSolrForm::TYPE_FACETFILTERS ? 
				$this->getLabelFacetFilter(): $this->getLabelSearchTool();
		
		$html .= $this->postRenderLabel();
		return $html;
	}
	
	/**
	 * String added before label.
	 * @return string
	 */
	public function preRenderLabel() { return ''; }
	
	/**
	 * String added after label
	 * @return string
	 */
	public function postRenderLabel() { return ''; } 
	
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

	function isMultiple()
	{
		return (isset($this->element['multi'])) ? $this->element['multi'] == 'true' : false;
	}
}
