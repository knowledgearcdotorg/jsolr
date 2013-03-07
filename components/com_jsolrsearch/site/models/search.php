<?php 
/**
 * A model that provides advanced search capabilities.
 * 
 * @package    JSolr
 * @subpackage Search
 * @copyright  Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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
 * Name                    Email
 * Hayden Young               <haydenyoung@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('joomla.application.component.modelform');
jimport('jsolr.search.factory');

jimport('jsolr.form.form');

require_once(JPATH_ROOT.DS."components".DS."com_content".DS."helpers".DS."route.php");


class JSolrSearchModelSearch extends JModel
{
   protected $view_item = 'search';
   protected $form;
   protected $lang;

   public function getItems()
   {
      try {
        JPluginHelper::importPlugin("jsolrsearch");
        $dispatcher =& JDispatcher::getInstance();

        $params = JComponentHelper::getParams($this->get('option'), true);

        $query = $this->getForm()->fillQuery()->getQuery();
        $response = $query->search();
        
        $headers = json_decode($response->getRawResponse())->responseHeader;

        $this->set('total', $response->response->numFound);
        $this->set('qTime', $headers->QTime);

        $items = array();

        foreach (json_decode($response->getRawResponse())->response->docs as $document) {
            $docs = $dispatcher->trigger('onJSolrSearchResultPrepare', array(
                $document,
                $response->highlighting,
                JArrayHelper::getValue($query->params(), "fl.fragsize"),
                $this->getLanguage(false))
            );

            foreach ($docs as $document) {
              foreach ($document as $key => $value) {
                  if (is_array($value)) {
                      $document->$key = $value[0];
                  }
              }

              $items[] = $document;
            }
        }

        return $items;
      } catch (Exception $e) {
        return NULL;
      }

      return $response;
   }

   public function buildQueryURL($params)
   {
      $url = new JURI("index.php");
      
      $url->setVar("option", "com_jsolrsearch");
      $url->setVar("view", "basic");

      foreach ($params['jform'] as $key=>$value) {
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
      
      return JRoute::_($url->toString(), false);
   }
   
   public function buildQuery($params)
   {
      $q = "";
   
      if (JArrayHelper::getValue($params, "aq")) {
         $q .= JArrayHelper::getValue($params, "aq");
      }
   
      if (JArrayHelper::getValue($params, "eq")) {
         $q .= "\"".JArrayHelper::getValue($params, "eq")."\"";
      }
   
      $oq = array();
   
      for ($i=0; $i<3; $i++) {
         if (trim(JArrayHelper::getValue($params, "oq".$i))) {
            $oq[] = trim(JArrayHelper::getValue($params, "oq".$i));
         }
      }
   
      if (count($oq)) {
         $q .= " " . implode(" OR ", $oq);
      }
   
      if (JArrayHelper::getValue($params, "nq")) {
         $q .= " -".preg_replace('!\s+!', ' -', JArrayHelper::getValue($params, "nq"));
      }
   
      return trim($q);
   }

   /**
    * Method to get the advanced search form.
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
      $this->form = $this->loadForm($context, $this->getName(), array('control' => 'jform', 'load_data' => $loadData));

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
      $context = $this->get('option').'.edit.'.$this->getName().'.data';
      $data = (array)JFactory::getApplication()->getUserState($context.'.data', array());
      
      return $data;
   }

   private function _getCustomFormPath()
   {
      $path = null;

      if (JRequest::getString("o")) {
         $extension = JArrayHelper::getValue(explode("_", JRequest::getCmd("o"), 2), 1);

         $path = JPath::find(JPATH_PLUGINS.DS."jsolrsearch".DS.$extension.DS."forms", "advanced.xml");
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

    // Language code must take the form xx-XX.
    if (!$lang || count(explode("-", $lang)) < 2) {
      $lang = JLanguageHelper::detectLanguage();
    }

    if ($includeRegion) {
      return $lang;
    } else {
      $parts = explode('-', $lang);

      // just return the xx part of the xx-XX language.
      return JArrayHelper::getValue($parts, 0);
    }
  }

  private function _localizeDateTime($dateTime)
  {
    $date = JFactory::getDate($dateTime);
    
    return $date->format(JText::_("DATE_FORMAT_LC2"));
  }
}
