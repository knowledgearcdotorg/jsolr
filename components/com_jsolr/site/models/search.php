<?php
/**
 * A model that provides default search capabilities.
 *
 * @package     JSolr
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('joomla.html.pagination');
jimport('joomla.application.component.modelform');
jimport('joomla.filesystem.file');

class JSolrModelSearch extends \JSolr\Search\Model\Form
{
    const MM_DEFAULT = '1';

    protected $form;

    protected $lang;

    protected $pagination;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->set('option', 'com_jsolr');

        $this->set('context', $this->get('option').'.search');

        JFactory::getApplication()->setUserState('com_jsolr.facets', null);

        Jlog::addLogger(array());
    }

    /**
        * (non-PHPdoc)
        * @see JModelList::populateState()
        */
    public function populateState($ordering = null, $direction = null)
    {
        $application = JFactory::getApplication('site');

        $this->setState('query.q', $application->input->get("q", null, "html"));

        $this->setState('query.o', $application->input->getString("o", null, "string"));

        $value = $application->input->get('limit', $application->getCfg('list_limit', 0));

        $this->setState('list.limit', $value);

        $value = $application->input->get('limitstart', 0);

        $this->setState('list.start', $value);

        $params = $application->getParams();

        $this->setState('params', $params);

        parent::populateState($ordering, $direction);
    }

    /**
     * Builds the query and returns the result.
     *
     * @return  \Solarium\QueryType\Select\Result  The query result.
     */
    public function getQuery()
    {
        //$filters = $this->getForm()->getFilters();

        /*
            $access = implode(' OR ', JFactory::getUser()->getAuthorisedViewLevels());

            if ($access) {
                $access = 'access:'.'('.$access.') OR null';
                $filters[] = $access;
            }
        */

        /*if (!$this->getState('query.q')) {
            if (!$this->getAppliedFacetFilters()) {
                return null; // nothing passed. Get out of here.
            }
        }*/

        $store = $this->getStoreId();

        if (!isset($this->cache[$store])) {
            try {
                if (is_null($this->getState('query.q'))) {
                    return null;
                }

                $client = \JSolr\Factory::getClient();

                $query = $client->createSelect();
                $query->setQuery($this->getState('query.q', "*:*"));

                $query->setStart($this->getState("list.start", 0));

                $limit = $this->getState(
                            "list.limit",
                            JFactory::getApplication()->getCfg('list.limit', 10));

                $query->setRows($limit);

                /*
                $query->getSpellcheck()
                    ->setQuery("latest submision")
                    ->setCount(10)
                    ->setBuild(true)
                    ->setCollate(true)
                    ->setExtendedResults(true)
                    ->setCollateExtendedResults(true)
                    ->setCollateParam("maxResultsForSuggest", 1);
                */

                $query->getEDisMax()->setQueryFields("title_txt_en^100");

                $query->getHighlighting()
                    ->setFields('title_txt_en, content_txt_en')
                    ->setSimplePrefix('<b>')
                    ->setSimplePostfix('</b>');

                $response = $client->select($query);

                //JFactory::getApplication()->setUserState('com_jsolr.facets', $results->getFacets());

                //JFactory::getApplication()->setUserState('com_jsolr.facets.ranges', $results->getFacetRanges());

                $this->cache[$store] = $response;
            } catch (Exception $e) {
                JLog::add($e->getCode().' '.$e->getMessage(), JLog::ERROR, 'jsolr');
                JLog::add((string)$e, JLog::ERROR, 'jsolr');

                throw $e;
            }
        }

        return $this->cache[$store];
    }

    public function getItems()
    {
        return $this->getQuery()->getDocuments();
    }

    /**
     * A convenience method for getting the Did You Mean text and a url for
     * changing the search query to the Did You Mean text.
     */
    public function getDidYouMean()
    {
        $spellcheck = $this->getQuery()->getSpellcheck();

        if ($spellcheck->getCorrectlySpelled()) {
            $collation = array_shift($spellcheck->getCollations());

            $correction = array_shift($collation->getCorrections());

            if ($correction) {
                return $correction;
            }
        } else {
            return null;
        }
    }

    public function getTotal()
    {
        return $this->getQuery()->getNumFound();
    }

    public function getStart()
    {
        return $this->getState("list.start", 0);
    }

    public function getPagination()
    {
        $limit = (int)$this->getState('list.limit') - (int)$this->getState('list.links');
        $page = new JPagination($this->getTotal(), $this->getStart(), $limit);

        return $page;
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since   12.2
     */
    protected function getStoreId($id = '')
    {
        // Add the list state to the store id.
        $id .= ':' . $this->getState('list.start');
        $id .= ':' . $this->getState('list.limit');
        $id .= ':' . $this->getState('list.ordering');
        $id .= ':' . $this->getState('list.direction');

        return md5($this->context . ':' . $id);
    }

    /**
     * Gets a list of featured items.
     * @TODO This is a proof of concept. Needs much more work.
     */
    public function getFeaturedItems()
    {
/*        try {
            $filters = array('context:com_content.article');

            //$query = $this->_getListQuery();

            $query
                ->filters($filters)
                ->limit(5)
                ->mergeParams(array('mm'=>'100%'));

            if (is_null($query)) {
                return $query;
            }

            return $query->search();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'jsolr');

            return null;
        }
*/
        return array();
    }

    public function getSuggestionQueryURIs()
    {
        $uris = array();

        $i = 0;

        $uri = \JSolr\Search\Factory::getQueryRouteWithPlugin();

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

        $this->form = $this->loadForm($context, $this->getCustomFormPath('search'), array('load_data'=>$loadData));

        if (empty($this->form)) {
            return false;
        }

        return $this->form;
    }

    protected function preprocessForm(JForm $form, $data, $group = 'plugin')
    {
        // load additional filters.
        $form->loadFile($this->getCustomFormPath('filters'), false);

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
      JForm::addFieldPath(JPATH_BASE.'/libraries/JSolr/Form/Fields/Legacy');

      try {
         $form = \JSolr\Form\Form::getInstance($name, $source, $options, false, $xpath); //JSolrForm instead of JForm

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
            if (is_a($field, 'JSolrFormFieldHiddenFilter') || is_subclass_of($field, 'JSolrFormFieldHiddenFilter')) {
                if ($field->value) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }
}
