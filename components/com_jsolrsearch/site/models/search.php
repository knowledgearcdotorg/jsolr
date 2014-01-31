<?php 
/**
 * A model that provides default search capabilities.
 * 
 * @package    JSolr
 * @subpackage Search
 * @copyright  Copyright (C) 2012-2014 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.
 *
 *   The JSolrSearch component for Joomla! is free software: you can redistribute it 
 *   and/or modify it under the terms of the GNU General Public License as 
 *   published by the Free Software Foundation, either version 3 of the License, 
 *   or (at your option) any later version.
 *
 *   The JSolrSearch component for Joomla! is distributed in the hope that it will be 
 *   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with the JSolrSearch component for Joomla!.  If not, see 
 *   <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Hayden Young <hayden@knowledgearc.com>
 * @author Bartłomiej Kiełbasa <bartlomiej.kielbasa@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('joomla.html.pagination');
jimport('joomla.application.component.modelform');
jimport('joomla.filesystem.file');
jimport('jsolr.search.factory');
jimport('jsolr.form.form');
jimport('jsolr.pagination.pagination');

class JSolrSearchModelSearch extends JModelForm
{
	const MM_DEFAULT = '1';

	protected $form;
	protected $lang;
	protected $pagination;

	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->set('option', 'com_jsolrsearch');
		$this->set('context', $this->get('option').'.search');

		JFactory::getApplication()->setUserState('com_jsolrsearch.facets', null);
	}
   
   /**
    * (non-PHPdoc)
    * @see JModelList::populateState()
    */
   public function populateState($ordering = null, $direction = null)
   {
		$application = JFactory::getApplication('site');

		$this->setState('query.q', $application->input->get("q", null, "html"));
		$extension = $this->setState('query.o', $application->input->getString("o", null, "string"));

		$lang = $application->input->getString("lr", null);
  			
		if (!$lang) {
			$lang = $application->input->getString("lang", null);
		}

		$this->setState('query.lang', $lang);

		$value = $application->input->get('limit', $application->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		$value = $application->input->get('limitstart', 0);
		$this->setState('list.start', $value);
		
		$params = $application->getParams();
		$this->setState('params', $params);		

		parent::populateState($ordering, $direction);
   }
   
   public function getItems()
   {
		$hl = array();
		$filters = array();
		$facets = array();
		$qf = array();
		$sort = array();

		$filters = $this->getFilters();
		
		$access = implode(' OR ', JFactory::getUser()->getAuthorisedViewLevels());
		
		if ($access) {
			$access = 'access:'.'('.$access.') OR null';
			$filters[] = $access;
		}

		// nothing passed. Get out of here.
		if (!$this->getState('query.q')) {
			if (!$this->getForm()->isFiltered()) {
				return null;
			}
		}

		$sort = $this->getForm()->getSorts();
		
		$facets = $this->getForm()->getFacets();

   		JPluginHelper::importPlugin("jsolrsearch");
   		$dispatcher = JDispatcher::getInstance();
      
   		// Get any additional filters which may be needed as part of the search query.
   		foreach ($dispatcher->trigger("onJSolrSearchFQAdd", array($this->getState('query.lang'))) as $result) {
   			$filters = array_merge($filters, $result);
   		}
   
   		// Get Highlight fields for results.
   		foreach ($dispatcher->trigger('onJSolrSearchHLAdd', array($this->getState('query.lang'))) as $result) {
   			$hl = array_merge($hl, $result);
   		}

   		// get query filter params and boosts from plugin.
   		foreach ($dispatcher->trigger('onJSolrSearchQFAdd', array($this->getState('query.lang'))) as $result) {   			
   			$qf = array_merge($qf, $result);
   		}   	
   		
   		$q = $this->getState('query.q', "*:*");

   		$query = JSolrSearchFactory::getQuery($q)
   			->spellcheck(true)
			->useQueryParser("edismax")
			->retrieveFields("*,score")
			->filters($filters)
			->highlight(200, "<mark>", "</mark>", 3, implode(" ", $hl))
			->limit($this->getState("list.limit", JFactory::getApplication()->getCfg('list.limit', 10)))
			->offset($this->getState("list.start", 0))
			->mergeParams(
				array(
					'mm'=>$this->getState('params')->get('mm', self::MM_DEFAULT)
			));

   		if (count($sort)) {
   			$query->sort(implode(', ', $sort));
   		}
   		
   		if (count($qf)) {
   			$query->queryFields($qf);
   		}
   			
   		if (count($facets)) {
   			$query->facetFields($facets);
   			$query->facet(1, true, 10);
   		}

   		if ($extension = $this->getState('query.o', null)) {
   			$query->mergeFilters('extension:' . $extension);
   		}

		try {	
			$results = $query->search();

			JFactory::getApplication()->setUserState('com_jsolrsearch.facets', $results->getFacets());

			$this->pagination = new JSolrPagination($results->get('numFound'), $this->getState('list.start'), $this->getState('list.limit'));

			return $results;
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'jsolrsearch');
			$this->pagination = new JSolrPagination($this->get('total', 0), 0, 0);
			return null;
		}
	}

	public function getPagination()
	{
		return $this->pagination;
	}
	
	public function getSuggestionQueryURIs()
	{
		$uris = array();
		$i = 0;
		
		$uri = JSolrSearchFactory::getQueryRouteWithExtension();

		$uri->setVar('q', $this->getItems()->getSuggestions());
		
		$uri->setVar('q', $this->getItems()->getSuggestions());		

		$uris[$i]['uri'] = $uri;
		$uris[$i]['title'] = $this->getItems()->getSuggestions();
		
		return $uris;
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
		if (!is_null($this->form)) {
			return $this->form;
		}

		$context = $this->get('option').'.'.$this->getName();
		$this->form = $this->loadForm($context, $this->getName(), array('load_data'=>$loadData));

		if (empty($this->form)) {
			return false;
		}			

		return $this->form;
	}
   
	protected function preprocessForm(JForm $form, $data, $group = 'plugin')
	{
		$form->loadFile($this->_getCustomFilterPath(), false);
      
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * (non-PHPdoc)
	 * @see JModelForm::loadFormData()
	 */
	protected function loadFormData()
	{
		$data = array();
		
		$query = JURI::getInstance()->getQuery(true);

		if (count($query)) {
			$data = $query;
		}
      
		$context = $this->get('option').'.'.$this->getName();

		if (version_compare(JVERSION, "3.0", "ge")) {
			$this->preprocessData($this->get('context'), $data);
		}
      
		return $data;
	}

	/**
	 * Gets the custom form path for filters.
	 * 
	 * If a plugin has been selected (using the "o" parameter) then the 
	 * method will attempt to load the plugin's filter override. If no 
	 * override is found, it will attempt to load the default filters path.
	 * 
	 * This method will attempt to load an plugin filter like so:
	 * 1. Check in the current template's html/com_jsolrsearch/basic/forms/ 
	 * directory for a plugin override. The plugin override takes the form 
	 * filters.<plugin_name>.xml.
	 * 
	 * 2. If no plugin override is found in the template, the plugin's default 
	 * filters.xml is searched for (plugins/jsolrsearch/<plugin>/forms/filters.xml).
	 * 
	 * 3. If no plugin filters.xml exists, the default filters.xml is searched for 
	 * (html/com_jsolrsearch/basic/forms/filters.xml).
	 * 
	 * 4. If no default filters.xml is found in the current template, the 
	 * JSolr Search component's filters.xml is loaded.
	 */
	private function _getCustomFilterPath()
	{
		$path = null;
		
		// load plugin filter override.
		if ($this->getState('query.o')) {
			foreach ($this->getExtensions() as $extension) {
				if (JArrayHelper::getValue($extension, 'plugin') == $this->getState('query.o')) {
					$plugin = str_replace('com_', '', JArrayHelper::getValue($extension, 'plugin'));
					$pluginOverride =
						JPATH_ROOT.'/templates/'.
						JFactory::getApplication()->getTemplate().
						'/html/com_jsolrsearch/basic/forms/'.
						'filters.'.$plugin.'.xml';
					
					if (JFile::exists($pluginOverride)) {
						$path = $pluginOverride;	
					} else {
						$path = JPATH_ROOT.'/plugins/jsolrsearch/'.$plugin.'/forms/filters.xml';
					}					

					break;
				}
			}
		}
		
		// load default filter if no plugin override has been loaded.
		if (!$path) {
			$filters = 'filters.xml';
			
			$defaultOverride =
			JPATH_ROOT.'/templates/'.
			JFactory::getApplication()->getTemplate().
			'/html/com_jsolrsearch/basic/forms/'.$filters;
			
			if (JFile::exists($defaultOverride)) {
				$path = $defaultOverride;
			} else {
				$path = __DIR__.'/forms/'.$filters;
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
      if (isset($this->_forms[$hash]) && !$clear)
      {
         return $this->_forms[$hash];
      }

      // Get the form.
      JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
      JForm::addFieldPath(JPATH_BASE.'/libraries/jsolr/form/fields');

      try
      {
         $form = JSolrForm::getInstance($name, $source, $options, false, $xpath); //JSolrForm instead of JForm

         if (isset($options['load_data']) && $options['load_data'])
         {
            // Get the data for the form.
            $data = $this->loadFormData();
         }
         else
         {
            $data = array();
         }

         // Allow for additional modification of the form, and events to be triggered.
         // We pass the data because plugins may require it.
         $this->preprocessForm($form, $data);

         // Load the data into the form after the plugins have operated.
         $form->bind($data);

      }
      catch (Exception $e)
      {
         $this->setError($e->getMessage());
         return false;
      }

      // Store the form for later.
      $this->_forms[$hash] = $form;

      return $form;
   }

  /**
  * Get's the language, either from the item or from the Joomla environment.
  *
  * @param bool $includeRegion True if the region should be included, false
  * otherwise. E.g. If true, en-AU would be returned, if false, just en
  * would be returned.
  *
  * @return string The language code.
  */
  protected function getLanguage($includeRegion = true)
  {
    $lang = $this->lang;

    $result = $lang;

    // Language code must take the form xx-XX.
    if (!$lang || count(explode("-", $lang)) < 2) {
      $lang = JLanguageHelper::detectLanguage();
    }

    if ($includeRegion) {
      $result =  $lang;
    } else {
      $parts = explode('-', $lang);

      // just return the xx part of the xx-XX language.
      $result =  JArrayHelper::getValue($parts, 0);
    }

    if (empty($result)) {
      $result = 'en';
    }

    return $result;
  }

	/**
	 * Get the list of enabled extensions for search results.
	 */
  public function getExtensions()
  {
    JPluginHelper::importPlugin("jsolrsearch");
    $dispatcher = JDispatcher::getInstance();

    $array = $dispatcher->trigger('onJSolrSearchRegisterComponents');

    $array = array_merge(array(array('plugin' => '', 'name' => JText::_('Everything'))), $array);
    
    for ($i = 0; $i < count($array); $i++) {
    	$uri = clone JSolrSearchFactory::getQueryRoute();
    	
    	if ($array[$i]['plugin'])
    		$uri->setVar('o', $array[$i]['plugin']);
    	else
    		$uri->delVar('o');
    	
    	$array[$i]['uri'] = htmlentities((string)$uri, ENT_QUOTES, 'UTF-8');
    }
    
    return $array;
  }
  
	/**
	 * Gets an array of filters formatted for the JSolrSearchQuery.
	 * 
	 * @return array An array of filters formatted for the JSolrSearchQuery.
	 */
	public function getFilters()
	{
		$filters = array();

		foreach ($this->getForm()->getFilters() as $key=>$value) {
			foreach ($value as $item) {
				$filters[] = $key.':'.$item;
			}
		}
		
		return $filters;
	}

	/**
	 * Gets a list of applied filters based on any currently selected facets.
	 * 
	 * @return array A list of applied filters based on any currently selected facets.
	 */
	public function getAppliedFacetFilters()
	{
		$fields = array();
			
		foreach ($this->getForm()->getFieldset("facets") as $field) {
			if ($field->value) {
				$fields[] = $field;
			}
		}

		return $fields;
	}
	
	/**
	 * Gets a list of applied filters based on any specified advanced search 
	 * parameters.
	 *
	 * @return array a list of applied filters based on any specified advanced 
	 * search parameters.
	 */
	public function getAppliedAdvancedFilters()
	{
		$fields = array();
		
		foreach ($this->getForm()->getFieldset('tools') as $field) {			
			if (strtolower($field->type) == 'jsolr.advancedfilter') {

				if ($field->value) {
					$fields[] = $field;
				}
			}
		}

		return $fields;
	}
}
