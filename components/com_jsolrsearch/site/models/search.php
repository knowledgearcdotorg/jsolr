<?php 
/**
 * A model that provides advanced search capabilities.
 * 
 * @package    JSolr
 * @subpackage Search
 * @copyright  Copyright (C) 2012-2013 Wijiti Pty Ltd. All rights reserved.
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
 *   GNU General Public License for more details.https://www.google.com.au/search?hl=en&safe=off&site=&source=hp&q=Nintendo&oq=Nintendo&gs_l=hp.3..0l10.1369.2958.0.3237.8.6.0.2.2.2.527.2233.0j1j1j1j1j2.6.0.les%3B..0.0...1c.1.5.hp.gLNc7juiz2c
 *
 *   You should have received a copy of the GNU General Public License
 *   along with the JSolrSearch component for Joomla!.  If not, see 
 *   <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Hayden Young <haydenyoung@wijiti.com>
 * @author Bartłomiej Kiełbasa <bartlomiej.kielbasa@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('jsolr.search.factory');
jimport('joomla.html.pagination');

jimport('joomla.application.component.modelform');

jimport('jsolr.form.form');

 // error_reporting(E_ALL);
 // ini_set("display_errors", 1); 


class JSolrSearchModelSearch extends JModelForm
{
	const MM_DEFAULT = '1';

	protected $form;
	protected $lang;
	protected $pagination;
	private $filtered;
   
   protected static $form_facet_filter = NULL;
   protected static $form_search_tools = NULL;

	/**
	 * (non-PHPdoc)
	 * @see JModelList::populateState()
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->set('option', 'com_jsolrsearch');
		$this->set('context', $this->get('option').'.search');
		
		$this->set('params', JComponentHelper::getParams($this->get('option'), true));
	}
   
   static function getFacetFilterForm()
   {
     return self::$form_facet_filter;
   }
   
	/**
    * @return bool
    */
   public static function showFilter() {
   	return !is_null(self::getFacetFilterForm());
   }

   static function getSearchToolsForm()
   {
     return self::$form_search_tools;
   }

   static function addForm(JSolrForm $form)
   {
     switch ($form->getType()) {
        case JSolrForm::TYPE_FACETFILTERS:
          self::$form_facet_filter = $form;
          break;

        case JSolrForm::TYPE_SEARCHTOOLS:
          self::$form_search_tools = $form;
          break;
     }
   }
   
   /**
    * (non-PHPdoc)
    * @see JModelList::populateState()
    */
   public function populateState($ordering = null, $direction = null)
   {
		$application = JFactory::getApplication();
   
		$q = $application->input->get("q", null, "string");
		$extension = $application->input->getString("o", null, "string");

		if ($q) {
			$this->setState('query.q', $q);
			
			$this->setState('query.o', $extension);
   		
			$lang = $application->input->getString("lr", null);
   			
			if (!$lang) {
				$lang = $application->input->getString("lang", null);
			}

			$this->setState('query.lang', $lang);
		}

		$value = $application->input->get('limit', $application->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		$value = $application->input->get('limitstart', 0);
		$this->setState('list.start', $value);

		parent::populateState($ordering, $direction);
   }
   
   private function _query()
   {
		$hl = array();
		$filters = array();
		$facets = array();
		$qf = array();
		$sort = array();
   
		$form = $this->getForm();

		// get filters from current form fields. 
		foreach ($form->getFieldsets() as $fieldset) {
			foreach ($form->getFieldset($fieldset->name) as $field) {
				if (in_array('JSolrFilterable', class_implements($field)) == true) {
					if ($field->getFilter()) {
						$this->filtered = true;					
						$filters[] = $field->getFilter();
					}
				}
			}
		}
		
		// get sort fields.
		foreach ($form->getFieldsets() as $fieldset) {
			foreach ($form->getFieldset($fieldset->name) as $field) {
				if (in_array('JSolrSortable', class_implements($field)) == true) {
					if ($field->getSort()) {
						$sort[] = $field->getSort();
					}
				}
			}
		}

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
   		
   		$q = "*:*";
   		
   		if ($this->getState('query.q') != '*') {
   			$q = $this->getState('query.q', "*:*");
   		}

   		$query = JSolrSearchFactory::getQuery($q)
   			->spellcheck(true)
			->useQueryParser("edismax")
			->retrieveFields("*,score")
			->filters($filters)
			->highlight(200, "<strong>", "</strong>", 3, implode(" ", $hl))
			->limit($this->getState("list.limit", JFactory::getApplication()->getCfg('list.limit', 10)))
			->offset($this->getState("list.start", 0))
			->mergeParams(
				array(
					'mm'=>$this->get('params')->def('mm', self::MM_DEFAULT)
			));

   		if (count($sort)) {
   			$query->sort(implode(', ', $sort));
   		}
   		
   		if (count($qf)) {
   			$query->queryFields($qf);
   		}
   			
   		if (count($form->getFacets())) {
   			$query->facetFields($form->getFacets());
   			$query->facet(1, true, 10);
   		}

   		if ($extension = $this->getState('query.o', null)) {
   			$query->mergeFilters('extension:' . $extension);
   		}
   		
		return $query;
   }

	public function getItems()
	{
		if (!$this->getState('query.q')) {
			return null;
		}
		
		try {
			JPluginHelper::importPlugin("jsolrsearch");
			$dispatcher = JDispatcher::getInstance();

			$query = $this->_query();

			$response = null;
			$rows = 0;

			if ($this->getState('query.q') || ($this->getState('query.q') == "*" && $this->filtered)) {
				$response = $query->search();

				$headers = json_decode($response->getRawResponse())->responseHeader;

				$this->setState('total', $response->response->numFound);
				$this->setState('qTime.raw', $headers->QTime);
				$this->setState('qTime', round(((int)$this->state->get('qTime.raw'))/1000, 5, PHP_ROUND_HALF_UP));
				$rows = $headers->params->rows;

				$items = $response->response->docs;
          
				for ($i = 0; $i < count($items); $i++) {
					// Get Highlight fields for results.
					foreach ($dispatcher->trigger('onJSolrSearchURIGet', array($items[$i])) as $result) {
						if ($result) {							
							$items[$i]->link = $result;
						}
					}
				}

				// Provide extra info via user state so that other extensions 
				// can access the information.
				$app = JFactory::getApplication('site');
				$app->setUserState('com_jsolrsearch.facets', $response->facet_counts->facet_fields);
				$app->setUserState('com_jsolrsearch.highlighting', $response->highlighting);
				
				if (isset($response->spellcheck->suggestions->collation)) {
					$app->setUserState('com_jsolrsearch.suggestions', $response->spellcheck->suggestions->collation);
				} else { 
					$app->setUserState('com_jsolrsearch.suggestions', null);
				}

			} else {
				$items = array();
			}

			$this->pagination = new JPagination($this->getState('total'), $this->getState('list.start'), $rows);

			return $items;
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'jsolrsearch');
			$this->pagination = new JPagination($this->get('total', 0), 0, 0);
			return null;
		}
	}

	function getPagination()
	{
		return $this->pagination;
	}

	public function getQueryURI()
	{
		$uri = new JURI("index.php");
      
		$uri->setVar("option", "com_jsolrsearch");
		$uri->setVar("view", "basic");
		$uri->setVar("Itemid", JRequest::getVar('Itemid'));

		if ($this->getState('query.q', null)) {
			$uri->setVar('q', $this->getState('query.q'));
		}
		
		if ($this->getState('query.o', null)) {
			$uri->setVar('o', $this->getState('query.o'));
		}
      
		return $uri;
	}
	
	public function getSuggestionQueryURIs()
	{
		$uris = array();
		$i = 0;
		
		$uri = clone $this->getQueryURI();

		$uri->setVar('q', JFactory::getApplication()->getUserState('com_jsolrsearch.suggestions'));

		$uris[$i]['uri'] = $uri;
		$uris[$i]['title'] = JFactory::getApplication()->getUserState('com_jsolrsearch.suggestions');
		
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
      $form->loadFile($this->_getCustomFormPath(), false);
      
      parent::preprocessForm($form, $data, $group);
   }

   /**
    * (non-PHPdoc)
    * @see JModelForm::loadFormData()
    */
   protected function loadFormData()
   {
      $uri = JFactory::getURI();

      $query = $uri->getQuery(true);

      if (count($query)) {
        return $query;
      }

      $context = $this->get('option').'.edit.'.$this->getName().'.data';

      $data = (array)JFactory::getApplication()->getUserState($context.'.data', array());
      
      return array();
   }

   private function _getCustomFormPath()
   {
      $path = null;      

      $path = __DIR__ . '/forms/tools.xml';

      if ($this->getState('query.o')) {
        foreach ($this->getExtensions() as $item) {
          if (JArrayHelper::getValue($item, 'plugin') == $this->getState('query.o')) {
            $path = JArrayHelper::getValue($item, 'path');
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
      if (isset($this->_forms[$hash]) && !$clear)
      {
         return $this->_forms[$hash];
      }

      // Get the form.
      JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
      JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

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

  public function getExtensions()
  {
    JPluginHelper::importPlugin("jsolrsearch");
    $dispatcher = JDispatcher::getInstance();

    $array = $dispatcher->trigger('onJSolrSearchRegisterComponents');

    $array = array_merge(array(array('plugin' => '', 'name' => JText::_('Everything'))), $array);
    
    for ($i = 0; $i < count($array); $i++) {
    	$uri = $this->getQueryURI();
    	
    	if ($array[$i]['plugin'])
    		$uri->setVar('o', $array[$i]['plugin']);
    	else
    		$uri->delVar('o');
    	
    	$array[$i]['uri'] = (string)$uri;
    }
    
    return $array;
  }
}
