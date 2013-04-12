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
	
	public function fillQuery()
	{
		$filter = $this->getFilter();

		if( !empty($filter) ) {
			$this->form->getQuery()->mergeFilters( $filter );
			return true;
		}

		return false;
	}

    /**
     * Method to get value of form field as a text. Maybe used to displaying it on frontend as current selected options
     */
	public function getValueText()
	{
		return $this->getLabel();
	}
	
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

	public function getValue()
	{
		return $this->value;
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

	function getNameText()
	{
		return $this->element['name'];
	}

  	/**
	 * Method to escape strings/array of strings
	 * @return string|array
  	 */
	function escape($value)
	{
		$result = array();

		if (is_array($value)) {
			foreach ($value as $val)
			{
				$result[] = $this->escape($val);
			}
		} else {
			$result = JSolrSearchFactory::getService()->escape($value);
		}

		return $result;
	}
}
