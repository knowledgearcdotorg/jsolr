<?php
/**
 * @package		JSolr
 */

defined('JPATH_PLATFORM') or die;

jimport('jsolr.form.fields.facets');

class JSolrFormFieldToggle extends JSolrFormFieldFacets
{
	public $type = 'JSolr.Toggle';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
	
		$facets = $this->getFacets();
	
		foreach ($facets as $key=>$value) {
			$class = '';
				
			if ($this->isSelected($key)) {
				$class = ' class="selected"';
			}
				
			$count = '';
				
			if (JArrayHelper::getValue($this->element, 'count', false)) {
				$count = '<span>('.$value.')</span>';
			}
				
			$options[] = '<li'.$class.'><a href="'.$this->getFilterURI($key).'">'.JArrayHelper::getValue($this->element, 'value').'</a>'.$count.'</li>';
		}
	
		reset($options);
	
		return $options;
	}
}