<?php
/**
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012-2013 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr library for Joomla!.
 *
 *   The JSolr library for Joomla! is free software: you can redistribute it 
 *   and/or modify it under the terms of the GNU General Public License as 
 *   published by the Free Software Foundation, either version 3 of the License, 
 *   or (at your option) any later version.
 *
 *   The JSolr library for Joomla! is distributed in the hope that it will be 
 *   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with the JSolrIndex component for Joomla!.  If not, see 
 *   <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Michał Kocztorz <michalkocztorz@wijiti.com> 
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com> 
 * @author Hayden Young <haydenyoung@wijiti.com>
 * 
 */
 
// no direct access
defined('_JEXEC') or die();

jimport('joomla.form.form');

/**
 * Provides a specialized JSolr form which exposes a number of additional 
 * methods for handling filtering, faceting and sorting of search results. 
 */
class JSolrForm extends JForm
{	
	protected $facets = false; 

	public function isFiltered()
	{
		if (count($this->getFilters())) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Method to get filters for narrowing the search result set.
	 * 
	 * The filters are provided via the JSolrFilterable interface's getFilters 
	 * method  and the form field must also provide a valid Solr filter field.
	 * 
	 * The returned array can be used to build the Solr querystring's filter 
	 * query (fq) so it is important that the Solr filter field exists.  
	 * 
	 * @return array A named array of filters. Each filter will also be an array:
	 * 
	 * E.g.
	 * 
	 * $filters['title'][0] = 'title1';
	 * $filters['title'][1] = 'title2';
	 * $filters['author'][0] = 'author1';
	 * $filters['author'][1] = 'author2';
	 */
	public function getFilters()
	{
		$filters = array();
		
		foreach ($this->getFieldsets() as $fieldset) {
			foreach ($this->getFieldset($fieldset->name) as $field) {
				if (in_array('JSolrFilterable', class_implements($field)) == true) {
					if (count($field->getFilters())) {
						$filters[$field->filter] = $field->getFilters();
																
						if ($field->exactmatch) {
							for ($i = 0; $i < count($filters[$field->filter]); $i++) {
								$filters[$field->filter][$i] = '"'.$filters[$field->filter][$i].'"';
							}
						}						
					}
				}
			}
		}

		return $filters;
	}
	
	/**
	 * Gets an array of facets from the currently configured list of JSolr 
	 * Form Fields.
	 * 
	 * @return array An array of facets.
	 */
	public function getFacets()
	{
		$facets = array();

		foreach ($this->getFieldset('facets') as $field) {
			$facets[] = $field->facet;
		}
		
		return $facets;		
	}
	
	/**
	 * Gets the fields to sort the result set by.
	 * 
	 * @return array The fields to sor the result set by.
	 */
	public function getSorts()
	{
		$sort = array();
		
		// get sort fields.
		foreach ($this->getFieldsets() as $fieldset) {
			foreach ($this->getFieldset($fieldset->name) as $field) {
				if (in_array('JSolrSortable', class_implements($field)) == true) {
					if ($field->getSort()) {
						$sort[] = $field->getSort();
					}
				}
			}
		}
		
		return $sort;
	}
	
	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   string  $data     The name of an XML file or string to load as the form definition.
	 * @param   array   $options  An array of form options.
	 * @param   string  $replace  Flag to toggle whether form fields should be replaced if a field
	 * already exists with the same group/name.
	 * @param   string  $xpath    An optional xpath to search for the fields.
	 *
	 * @return  object  JForm instance.
	 *
	 * @since   11.1
	 * @throws  Exception if an error occurs.
	 */
	public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false)
	{
		// Reference to array with form instances
		$forms = &self::$forms;
	
		// Only instantiate the form if it does not already exist.
		if (!isset($forms[$name]))
		{
			$data = trim($data);
	
			if (empty($data))
			{
				throw new Exception(JText::_('JLIB_FORM_ERROR_NO_DATA'));
			}
	
			// Instantiate the form.
			$forms[$name] = new JSolrForm($name, $options);
	
			// Load the data.
			if (substr(trim($data), 0, 1) == '<')
			{
				if ($forms[$name]->load($data, $replace, $xpath) == false)
				{
					throw new Exception(JText::_('JLIB_FORM_ERROR_XML_FILE_DID_NOT_LOAD'));
	
					return false;
				}
			}
			else
			{
				if ($forms[$name]->loadFile($data, $replace, $xpath) == false)
				{
					throw new Exception(JText::_('JLIB_FORM_ERROR_XML_FILE_DID_NOT_LOAD'));
	
					return false;
				}
			}
		}
	
		return $forms[$name];
	}

	/**
	 * Gets the facet form field markup for the facet field input.
	 * 
	 * The facet field must implement the JSolrFacetable interface.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value  The optional value to use as the default for the field.
	 *
	 * @return  string  The facet form field markup for the facet field input.
	 */
	public function getFacetInput($name, $group = null, $value = null)
	{
		// Attempt to get the form field.
		if ($field = $this->getField($name, $group, $value))
		{
			return $field->facetInput;
		}
	
		return '';
	}
}