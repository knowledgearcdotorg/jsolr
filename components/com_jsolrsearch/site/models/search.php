<?php
/**
 * A model that provides default search capabilities.
 *
 * @package     JSolr
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('joomla.html.pagination');
jimport('joomla.application.component.modelform');
jimport('joomla.filesystem.file');

class JSolrSearchModelSearch extends \JSolr\Search\Model\Form
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

        $this->setState('query.o', $application->input->getString("o", null, "string"));

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
        try {
            $query = $this->_getListQuery();

            if (is_null($query)) {
                return $query;
            }

            $bq = array();

            JPluginHelper::importPlugin("jsolrsearch");

            $dispatcher = JEventDispatcher::getInstance();

            // get query filter params and boosts from plugin.
            foreach ($dispatcher->trigger('onJSolrSearchPrepareBoostQueries') as $result) {
                $bq = array_merge($bq, $result);
            }

            $query->boostQueries($bq);

            $results = $query->search();

            JFactory::getApplication()->setUserState('com_jsolrsearch.facets', $results->getFacets());

            JFactory::getApplication()->setUserState('com_jsolrsearch.facets.ranges', $results->getFacetRanges());

            $this->pagination = new JPagination($results->get('numFound'), $this->getState('list.start'), $this->getState('list.limit'));

            return $results;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'jsolrsearch');
            $this->pagination = new JPagination($this->get('total', 0), 0, 0);

            return null;
        }
    }

    /**
     * Gets a list of featured items.
     * @TODO This is a proof of concept. Needs much more work.
     */
    public function getFeaturedItems()
    {
        try {
            $filters = array('context:com_content.article');

            $query = $this->_getListQuery();

            $query
                ->filters($filters)
                ->limit(5)
                ->mergeParams(array('mm'=>'100%'));

            if (is_null($query)) {
                return $query;
            }

            return $query->search();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'jsolrsearch');

            return null;
        }
    }

    /**
     * Gets the Solr query for the list.
     *
     * @return  JSolrSearchQuery  An instance of the JSolrSearchQuery class.
     */
    protected function _getListQuery()
    {
        $hl = array();

        $filters = array();

        $facets = array();

        $qf = array();

        $sort = array();

        $filters = $this->getForm()->getFilters();

        if (!$this->getState('params')->get('override_schema', 0)) {
            $access = implode(' OR ', JFactory::getUser()->getAuthorisedViewLevels());

            if ($access) {
                $access = 'access:'.'('.$access.') OR null';
                $filters[] = $access;
            }
        }

        if (!$this->getState('query.q')) {
            if (!$this->getAppliedFacetFilters()) {
                return null; // nothing passed. Get out of here.
            }
        }

        $sort = $this->getForm()->getSorts();

        $facets = $this->getForm()->getFacets();

        JPluginHelper::importPlugin("jsolrsearch");

        $dispatcher = JEventDispatcher::getInstance();

        // Get any additional filters which may be needed as part of the search query.
        foreach ($dispatcher->trigger("onJSolrSearchFQAdd") as $result) {
            $filters = array_merge($filters, $result);
        }

        // Get Highlight fields for results.
        foreach ($dispatcher->trigger('onJSolrSearchHLAdd') as $result) {
            $hl = array_merge($hl, $result);
        }

        // get query filter params and boosts from plugin.
        foreach ($dispatcher->trigger('onJSolrSearchQFAdd') as $result) {
            $qf = array_merge($qf, $result);
        }

        // get context.
        if ($this->getState('query.o', null)) {
            foreach ($dispatcher->trigger('onJSolrSearchRegisterPlugin') as $result) {
                if (JArrayHelper::getValue($result, 'name') == $this->getState('query.o', null)) {
                    $filters = array_merge($filters, array('context:'.JArrayHelper::getValue($result, 'context')));
                }
            }
        }

        $q = $this->getState('query.q', "*:*");

        $query = \JSolr\Search\Factory::getQuery($q)
            ->spellcheck(true)
            ->useQueryParser("edismax")
            ->retrieveFields("*,score")
            ->filters($filters)
            ->highlight(200, "<mark>", "</mark>", 3, implode(" ", $hl))
            ->limit($this->getState("list.limit", JFactory::getApplication()->getCfg('list.limit', 10)))
            ->offset($this->getState("list.start", 0))
            ->mergeParams(
                array(
                    'mm'=>$this->getState('params')->get('mm', self::MM_DEFAULT)));

        if (count($sort)) {
            $query->sort(implode(', ', $sort));
        }

        if (count($qf)) {
            $query->queryFields($qf);
        }

        if (count($facets)) {
            foreach ($facets as $facet) {
                $query->mergeParams($facet);
            }

            $query->facet(1, true, 10);
        }

        return $query;
    }

    public function getPagination()
    {
        return $this->pagination;
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
