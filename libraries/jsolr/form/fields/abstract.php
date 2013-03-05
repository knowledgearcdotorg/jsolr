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

class JSolrFormAbstract extends JFormField
{
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
		if ($this->form->getType() == JSolrForm::TYPE_FACETFILTERS) {
			return $this->getInputFacetFilter();
		}
		
		return $this->getInputSearchTool();
	}
}
