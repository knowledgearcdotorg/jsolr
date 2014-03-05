<?php 
/**
 * A model that provides advanced search capabilities.
 * 
 * @package		JSolr.Search
 * @subpackage	Model
 * @copyright	Copyright (C) 2012-2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.

   The JSolrSearch component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@knowledgearc.com>
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('joomla.application.component.modelform');
jimport('jsolr.search.factory');

jimport('jsolr.form.form');

class JSolrSearchModelAdvanced extends JModelForm
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->set('context', $this->get('option').'.'.$this->getName());
	}

	protected function populateState()
	{
		$app = JFactory::getApplication();
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
	}
	
	public function buildQuery()
	{
		$application = JFactory::getApplication();

		$q = array();

		if ($application->input->get('eq')) {
			$q[] = "\"".$application->input->getHtml('eq')."\"";
		}
	
		if ($application->input->get('oq')) {
			$parts = explode(' ', $application->input->getHtml('oq'));
			
			if ($parts) {
				$q[] = implode(" OR ", $parts);
			}
		}		

		if ($application->input->get('nq')) {
			$matches = array();
			preg_match_all('/"{1}.+"{1}|\S+/', $application->input->getHtml('nq'), $matches);
			
			foreach (JArrayHelper::getValue($matches, 0) as $match) {
				$q[] = '-'.$match;				
			}
		}
		
		if ($application->input->get('aq')) {
			$q[] = $application->input->getHtml('aq');
		}

		return trim(implode(" ", $q));
	}
	
	public function parseQuery() 
	{
		$query = JFactory::getApplication()->input->getHtml("q");
		$data = array();

		$nq = array();
		$matches = array();
		preg_match_all('/-"{1}.+"{1}|-\S+/', $query, $matches);
		
		foreach (JArrayHelper::getValue($matches, 0) as $match) {
			$nq[] = implode("", explode("-", $match, 2));
			$query = str_replace($match, '', $query);
		}
		
		$data['nq'] = implode(' ', $nq);
		
		preg_match('/"{1}.+?"{1}/', $query, $eq);
		
		if ($eq) {
			$data['eq'] = str_replace("\"", "", JArrayHelper::getValue($eq, 0));
			$query = str_replace(JArrayHelper::getValue($eq, 0), '', $query);
		}
	
		$oq = array();
		
		$array = explode(' OR ', $query);

		if (count($array) > 1) {
			$i = 0;
			$parsed = false;

			while (($item = current($array)) && !$parsed) {
				$parts = explode(' ', trim($item));

				if ($i == 0) {
					$oq[] = $parts[count($parts) - 1];
				} else {
					$oq[] = $parts[0];

					if (count($parts) > 1) {
						$parsed = true;
					}
				}

				next($array);

				$i++;
			}

			$data['oq'] = implode(' ', $oq);

			$query = str_replace(implode(' OR ', $oq), '', $query);
		}

		$data['aq'] = trim($query);

		return $data;
	}
	
	/**
	 * Gets the search url.
	 *
	 * @return JURI The search url.
	 */
	public function getURI()
	{
		$uri = new JURI("index.php");
	
		$uri->setVar("option", "com_jsolrsearch");
		$uri->setVar("view", "search");
		$uri->setVar("Itemid", JRequest::getVar('Itemid'));
	
		if ($query = $this->buildQuery()) {
			$uri->setVar('q', urlencode($query));
		}
	
		if ($this->getState('query.o', null)) {
			$uri->setVar('o', $this->getState('query.o'));
		}
		
		$vars = array('task', 'nq', 'oq', 'eq', 'aq', 'as');

		foreach (JURI::getInstance()->getQuery(true) as $key=>$value) {
			if (array_search($key, $vars) === false && !empty($value)) {
				$uri->setVar($key, $value);
			}
		}
		
		// add the filters.
		foreach (JFactory::getApplication()->input->get('as', array(), 'array') as $key=>$value) {
			if (!empty($value)) {
				$uri->setVar($key, $value);
			}
		}
		
		// finally add the Itemid for basic search
		$uri->setVar('Itemid', JSolrSearchFactory::getSearchRoute()->getVar('Itemid'));

		return $uri;
	}
	
	/**
	 * Method to get the search form.
	 *
	 * @param   array $data    An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData   True if the form is to load its own data (default case), false if not.
	 * @return  JForm          A JForm object on success, false on failure.
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm($this->get('context'), $this->getName(), array('load_data'=>$loadData));

		if (empty($form)) {
			return false;
		}			

		return $form;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JModelForm::preprocessForm()
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'plugin')
	{
		$form->loadFile($this->_getCustomFormPath(), false);
      
		parent::preprocessForm($form, $data, $group);

		// Set 'as' field group fields to their respective values using the 
		// supplied 'data'.
		foreach ($data as $key=>$value) {
			if ($form->getField($key, 'as') !== false) {
				$form->setValue($key, 'as', trim($value));
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see JModelForm::loadFormData()
	 */
	protected function loadFormData()
	{
		$query = JSolrSearchFactory::getSearchRoute()->getQuery(true);

		if (count($query)) {
			$query = array_merge($query, $this->parseQuery());
			$data = $query;
		}
      
		$context = $this->get('option').'.'.$this->getName();

		if (version_compare(JVERSION, "3.0", "ge")) {
			$this->preprocessData($this->get('context'), $data);
		}
      
		return $data;
	}

	private function _getCustomFormPath()
	{
		$path = __DIR__ . '/forms/filters.xml';

		if ($this->getState('query.o')) {
			foreach ($this->getExtensions() as $extension) {
				if (JArrayHelper::getValue($extension, 'plugin') == $this->getState('query.o')) {
					$path = JPATH_ROOT.'/plugins/jsolrsearch/'.str_replace('com_', '', JArrayHelper::getValue($extension, 'plugin')).'/forms/filters.xml';					

					break;
				}
			}
		}
      
		return $path;
	}
	
	/**
	 * Override to use JSorlForm.
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   string   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  JForm object on success, False on error.
	 *
	 * @see     JForm
	 * @since   11.1
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear) {
			return $this->_forms[$hash];
      	}

		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
		JForm::addFieldPath(JPATH_BASE.'/libraries/jsolr/form/fields');

		try {
			$form = JSolrForm::getInstance($name, $source, $options, false, $xpath); //JSolrForm instead of JForm

			if (isset($options['load_data']) && $options['load_data']) {
				// Get the data for the form.
				$data = $this->loadFormData();
			} else {
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);

		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}
}
