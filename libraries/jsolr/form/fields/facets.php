<?php
/**
 * Abstract class for all JSolr form fields
 * @package		JSolr
 */

defined('JPATH_PLATFORM') or die;

jimport('jsolr.form.fields.filterable');

class JSolrFormFieldFacets extends JFormField implements JSolrFilterable
{
	protected $type = 'JSolr.Facets';	
	
	protected function getFacets()
	{
		if ($facet = $this->facet) {
			$app = JFactory::getApplication('site');
			$facets = $app->getUserState('com_jsolrsearch.facets', null);
			
			if ($facets) {
				if (isset($facets->{$facet})) {
					return $facets->{$facet};
				}
			}
		}
	
		return array();
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		if ($class = JArrayHelper::getValue($this->element, "class", null)) {
			$class = ' class="'.$class.'"';
		}
		
		foreach ($this->getOptions() as $option) {
			$html[] = '<ul'.$class.'>';
			$html[] = $option;
			$html[] = "</ul>";
		}
		
		return implode($html);
	}
	
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
	
		$facets = $this->getFacets();

		foreach ($facets as $key=>$value)
		{
			$class = '';
			
			if ($this->isSelected($key)) {
				$class = ' class="selected"';
			}
			
			$options[] = '<li'.$class.'><a href="'.$this->getFilterURI($key).'">'.$key.'</a><span>('.$value.')</span></li>';
		}
	
		reset($options);
	
		return $options;
	}

	public function getFilter()
	{
		$value = JFactory::getApplication()->input->getString($this->filterQuery);

		return ($value) ? JArrayHelper::getValue($this->element, 'filter').':'.$value : null;
	}
	
	protected function isSelected($facet)
	{
		$url = JFactory::getURI();
		
		$filter = $url->getVar($this->filterQuery, null);

		return ($filter == $facet) ? true : false;
	}
	
	protected function getFilterURI($facet)
	{
		$url = JFactory::getURI();
		
		if ($this->isSelected($facet)) {
			$url->delVar($this->filterQuery);
		} else {
			$url->setVar($this->filterQuery, $facet);
		}
		
		return (string)$url;
	}
	
	public function __get($name)
	{
		switch ($name) {
			case 'filter':
			case 'query':
			case 'facet':
				return JArrayHelper::getValue($this->element, $name, null, 'string');
				break;
		
			case 'filterQuery':
				return 'q_'.$this->filter;
				break;
				
			default:
				return parent::__get($name);
		}
	}
}