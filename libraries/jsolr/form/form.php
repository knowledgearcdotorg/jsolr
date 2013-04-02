<?php
/**
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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
 * 
 */
 
// no direct access
defined('_JEXEC') or die();

jimport('joomla.form.form');


class JSolrForm extends JForm
{
	const TYPE_FACETFILTERS 	= 0;
	const TYPE_SEARCHTOOLS 		= 1;
	
	/**
	 * Keeps type of the form
	 * @var integer
	 */
	protected $type;

	/**
	 * true if any filter is applied, otherwise false
	 * @var boolean
	 */
	protected $filtered = false;
	
	protected $query;
	
	/**
	 * @return integer one of the consts JSolrForm::TYPE_FACETFILTERS or JSolrForm::TYPE_SEARCHTOOLS
	 */
	public function getType()
	{
		return (int)$this->type;
	}

	public function isFiltered()
	{
		return $this->filtered;
	}
	
	/**
	 * Set form type. Accepted values:
	 * * JSolrForm::TYPE_FACETFILTERS
	 * * JSolrForm::TYPE_SEARCHTOOLS
	 * @param integer $type
	 */
	public function setType($type)
	{
		switch($type) {
			case self::TYPE_FACETFILTERS:
			case self::TYPE_SEARCHTOOLS:
				$this->type = $type;
				break;
			default:
				$this->type = self::TYPE_FACETFILTERS;
				break;
		}
	}
	
	/**
	 * Method to get filters for JSolr
	 * @return array
	 */
	public function getFilters()
	{
		$filters = array();

		foreach ($this->getFieldsets() as $fieldset) {
			foreach ($this->getFieldset($fieldset->name) as $field) {
				$filter = $field->getFilter();

				if (!empty($filter)) {
					$filters[] = $filter;
				}
			}
		}

		return $filters;
	}
	
	public function createQuery() {
		$mainframe = JFactory::getApplication();

        return $this->query = JSolrSearchFactory::getQuery('*:*')
            ->useQueryParser("edismax")
            ->retrieveFields("*,score")
            ->limit($mainframe->getCfg('list_limit'))
            ->highlight(200, "<strong>", "</strong>", 1);
	}
	
	public function getQuery() {
		if( empty($this->query) ) {
			return $this->createQuery();
		}
		return $this->query;
	}
	
	public function fillQuery() {
		$uri = JFactory::getURI();
		$params = $uri->getQuery(true);

		foreach ($this->getFieldsets() as $fieldset) {
			foreach ($this->getFieldset($fieldset->name) as $field) {
				if ($field->fillQuery()) {
					$this->filtered = true;
				}
			}
		}
		return $this;
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

	function loadFile($file, $reset = true, $xpath = false)
	{
		if (strpos($file, '.xml') !== FALSE) {
			$substr = substr($file, strlen($file) - 9, 9);

			$this->type = $substr == 'tools.xml' ? self::TYPE_SEARCHTOOLS : self::TYPE_FACETFILTERS;

			JSolrSearchModelSearch::addForm($this);
		}

		return parent::loadFile($file, $reset, $xpath);
	}

	/**
	 * Method to get all applied facet filters in the form
	 * @return array
	 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com> 
	 */
	function getAppliedFacetFilters()
	{
		$result = array();

		if ($this->getType() != self::TYPE_FACETFILTERS) return $result;

		foreach ($this->getFieldsets() as $fieldset) {
			if ($fieldset->name == 'main') continue;

			foreach ($this->getFieldset($fieldset->name) as $field) {
				$value = $field->getValue();
				if (!empty($value)) {
					if (is_array($value)) {
						if (isset($value['from'])) {
							if (empty($value['from'])  && empty($value['to']) && empty($value['value'])) {
								continue;
							}
						} elseif (count($value) && $value[0] == 'null') {
							continue;
						}
					}

					$result[] = array(
						'label' => $field->getLabel(),
						'value' => $field->getValueText(),
						'name'  => $field->name,
					);
				}
			}
		}

		return $result;
	}
}