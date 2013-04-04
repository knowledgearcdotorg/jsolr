<?php 
/**
 * A model that provides advanced search capabilities.
 * 
 * @package    JSolr
 * @subpackage Search
 * @copyright  Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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
jimport('joomla.application.component.modelform');
jimport('jsolr.search.factory');
jimport('joomla.html.pagination');

jimport('jsolr.form.form');

require_once(JPATH_ROOT."/components/com_content/helpers/route.php");


class JSolrSearchModelSearch extends JModelForm
{
   protected $view_item = 'search';
   protected $form;
   protected $lang;
   protected $pagination;

   protected static $form_facet_filter = NULL;
   protected static $form_search_tools = NULL;

   static function getFacetFilterForm()
   {
     return self::$form_facet_filter;
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

   public function getItems()
   {
      try {
        $this->getComponentsList();
        JPluginHelper::importPlugin("jsolrsearch");
        $dispatcher = JDispatcher::getInstance();
        $start = JRequest::getVar('start', 0);

        $params = JComponentHelper::getParams($this->get('option'), true);

        $form = $this->getForm();

        $query = $form->fillQuery()->getQuery();

        $response = NULL;
        $rows = 0;

        if ($form->isFiltered()) {
          $query->offset($start);

          $plugin = $this->getCurrentPlugin();

          if (!empty($plugin)) {
            $query->mergeFilters('extension:' . $plugin);
          }

          $response = $query->search();
          
          $headers = json_decode($response->getRawResponse())->responseHeader;

          $this->set('total', $response->response->numFound);
          $this->set('qTime', $headers->QTime);
          $rows = $headers->params->rows;

          $items = array();

          $qparams = $query->params();

          foreach (json_decode($response->getRawResponse())->response->docs as $document) {
              $docs = $dispatcher->trigger('onJSolrSearchResultPrepare', array(
                  $document,
                  $response->highlighting,
                  $fragsize = JArrayHelper::getValue($qparams, "fl.fragsize"),
                  $this->getLanguage(false))
              );

              foreach ($docs as $document) {
                foreach ($document as $key => $value) {
                    if (is_array($value)) {
                        $document->$key = $value[0];
                    }
                }

                if (!in_array($document, $items)) {
                  $items[] = $document;
                }
              }
          }

        } else {
          $items = NULL;
        }

        $this->pagination = new JPagination($this->get('total'), $start, $rows);

        return $items;
      } catch (Exception $e) {
        return NULL;
      }

      return $response;
   }

   function getPagination()
   {
     return $this->pagination;
   }

   public function buildQueryURL($params)
   {
      $url = new JURI("index.php");
      
      $url->setVar("option", "com_jsolrsearch");
      $url->setVar("view", "basic");
      $plugin = $this->getCurrentPlugin();

      $point = empty($plugin) ? $params : $params[$plugin];

      foreach ($point as $key=>$value) {
         switch ($key) {
            case "task":
            case "eq":
            case "aq":
            case "oq0":
            case "oq1":
            case "oq2":
            case "nq":
            case "option":
               break;
      
            default:
               $url->setVar($key, $value);
               break;
         }
      }

      if (isset($params['o'])) {
        $url->setVar('o', $params['o']);
      }
      
      return JRoute::_($url->toString(), false);
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
      $plugin = $this->getCurrentPlugin();
      $this->form = $this->loadForm($context, $this->getName(), array('control' => $plugin, 'load_data' => $loadData));

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

      $currentPlugin = $this->getCurrentPlugin();

      $path = __DIR__ . '/forms/tools.xml';

      if (!empty($currentPlugin)) {
        foreach ($this->getComponentsList() as $component) {
          if ($component['plugin'] == $currentPlugin) {
            $path = $component['path'];
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

  public function getComponentsList()
  {
    JPluginHelper::importPlugin("jsolrsearch");
    $dispatcher = JDispatcher::getInstance();

    return $dispatcher->trigger('onJSolrSearchRegisterComponents');
  }

  public function getCurrentPlugin()
  {
    $uri = JFactory::getURI();

    $plugin = JRequest::getVar('o', NULL, 'post');

    if (!empty($plugin)) {
      return $plugin;
    }

    return $uri->getVar('o');
  }
}
