<?php
/**
 * A model that provides default search capabilities.
 *
 * @package     JSolr
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use \Joomla\Registry\Registry;
use \Joomla\Utilities\ArrayHelper;
use \JSolr\Form\Form;

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('joomla.html.pagination');
jimport('joomla.application.component.modelform');
jimport('joomla.filesystem.file');

class JSolrModelSearch extends \JSolr\Search\Model\Form
{
    const QF_DEFAULT = '_text_ title_txt_*^100 content_txt_*';

    const HL_DEFAULT = '_text_ title_txt_* content_txt_*';

    protected $pagination;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->context = $this->option.'.'.$this->name;

        JFactory::getApplication()->setUserState('com_jsolr.search.results', null);

        Jlog::addLogger(array());
    }

    /**
     * (non-PHPdoc)
     * @see JModelLegacy::populateState()
     */
    public function populateState()
    {
        $application = JFactory::getApplication('site');

        $this->setState('query.q', $application->input->get("q", null, "html"));

        if ($lang = $application->input->get("lang", null, "string")) {
            $this->setState('query.lang', $this->getLanguage($lang, false));
        }

        $limit = $application->input->get('limit', $application->getCfg('list_limit', 0));

        $this->setState('list.limit', $limit);

        $start = $application->input->get('limitstart', 0);

        $this->setState('list.start', $start);

        // Need to convert registry to array so that blank values are included
        // in the merge.
        $params = $application->getParams();
        $array = $params->toArray();

        if ($dimension = $application->input->getString("dim", null, "string")) {
            $this->setState('query.dimension', $dimension);

            if ($table = $this->fetchDimension($dimension)) {
                $tmp = new \Joomla\Registry\Registry($table->get('params'));
                $array = array_merge($array, $tmp->toArray());
            }
        }

        $params = new \Joomla\Registry\Registry($array);

        $this->setState('params', $params);
    }

    /**
     * Builds the query and returns the result.
     *
     * @return  \Solarium\QueryType\Select\Result  The query result.
     */
    public function getQuery()
    {
        $store = $this->getStoreId();

        if (!isset($this->cache[$store])) {
            if (!($this->getState('query.q') || $this->getAppliedFacetFilters())) {
                return null; // nothing passed. Get out of here.
            }

            JPluginHelper::importPlugin("jsolr");
            $dispatcher = JEventDispatcher::getInstance();

            $params = $this->getState('params', new \Joomla\Registry\Registry);

            try {
                $client = \JSolr\Search\Factory::getClient();

                $query = $client->createSelect();

                $q = str_replace(
                    \JSolr\Utilities\AliasHelper::getAliasNames(),
                    \JSolr\Utilities\AliasHelper::getFieldNames(),
                    $this->getState('query.q', "*:*"));

                $query->setQuery($q);

                $query->setStart($this->getState("list.start", 0));

                $limit = $this->getLimit();

                $query->setRows($limit);

                // set query fields.
                $qf = $params->get('qf', self::QF_DEFAULT);

                $qf = \JSolr\Helper::localize($qf);

                // set minimum match.
                if ($mm = $params->get('mm', null)) {
                    $query->getEDisMax()->setMinimumMatch($mm);
                }

                $query->getEDisMax()->setQueryFields($qf);

                // set up spellcheck
                $query->getSpellcheck()
                    ->setQuery($this->getState('query.q'))
                    ->setCount(10)
                    ->setBuild(true)
                    ->setCollate(true)
                    ->setExtendedResults(true)
                    ->setCollateExtendedResults(true)
                    ->setCollateParam("maxResultsForSuggest", 1);

                // set highlighting fields.
                $hl = $params->get('hl', self::HL_DEFAULT);

                $hl = \JSolr\Helper::localize($hl);

                // set up highlighting.
                $query->getHighlighting()
                    ->setFields($hl)
                    ->setSimplePrefix('<b>')
                    ->setSimplePostfix('</b>');

                // set access.
                if ($params->get('fq_access', 1)) {
                    $viewLevels = JFactory::getUser()->getAuthorisedViewLevels();
                    $access = implode(' OR ', array_unique($viewLevels));

                    if ($access) {
                        $query
                            ->addFilterQuery(
                                array(
                                    'key'=>'access',
                                    'query'=>'access_i:'.'('.$access.') OR null'));
                    }
                }

                if ($lang = $this->getState('query.lang')) {
                    $query
                        ->addFilterQuery(
                            array(
                                'key'=>'lang',
                                'query'=>'lang_s:('.$lang. " OR *)"));
                }

                if ($fq = $params->get('fq')) {
                    $query
                        ->addFilterQuery(
                            array(
                                'key'=>'param_fq',
                                'query'=>$fq));
                }

                if ($pf = $params->get('pf')) {
                    $query->getEDisMax()->setPhraseFields($pf);

                    if ($ps = $params->get('ps')) {
                        $query->getEDisMax()->setPhraseSlop($ps);
                    }
                }

                if ($qs = $params->get('qs')) {
                    $query->getEDisMax()->setQueryPhraseSlop($qs);
                }

                if ($tie = $params->get('tie')) {
                    $query->getEDisMax()->setTie($tie);
                }

                if ($bq = $params->get('bq')) {
                    $query->getEDisMax()->setBoostQuery($bq);
                }

                if ($bf = $params->get('bf')) {
                    $query->getEDisMax()->setBoostFunctions($bf);
                }

                if ($uf = $params->get('uf')) {
                    $query->getEDisMax()->setUserFields($uf);
                }

                if ($pf2 = $params->get('pf2')) {
                    $query->getEDisMax()->setPhraseBigramFields($pf2);

                    if ($ps2 = $params->get('ps2')) {
                        $query->getEDisMax()->setPhraseBigramSlop($ps2);
                    }
                }

                if ($pf3 = $params->get('pf3')) {
                    $query->getEDisMax()->setPhraseTrigramFields($pf3);

                    if ($ps3 = $params->get('ps3')) {
                        $query->getEDisMax()->setPhraseTrigramSlop($ps3);
                    }
                }

                if ($boost = $params->get('boost')) {
                    $query->getEDisMax()->setBoostFunctionsMult($boost);
                }

                $query->getFacetSet()->setMinCount(1);

                $query->addFilterQueries($this->getFormFieldFilters());

                $query->addSorts($this->getFormFieldSorts());

                $query->getFacetSet()->addFacets($this->getFormFieldFacetQueries());

                $dispatcher->trigger('onJSolrSearchBeforeQuery', array($query, $this->getState()));

                $response = $client->select($query);

                $dispatcher->trigger('onJSolrSearchAfterQuery', array($response, $this->getState()));

                $this->cache[$store] = $response;

                JFactory::getApplication()->setUserState('com_jsolr.search.results', $this->cache[$store]);
            } catch (Exception $e) {
                JLog::add($e->getCode().' '.$e->getMessage(), JLog::ERROR, 'jsolr');
                JLog::add((string)$e, JLog::ERROR, 'jsolr');

                throw $e;
            }
        }

        return $this->cache[$store];
    }

    /**
     * A convenience method for getting a list of search results.
     *
     * A link to the item is added to each search result document.
     *
     * @return  \Solarium\QueryType\Select\Result\Document[]  A list of search
     * results.
     */
    public function getItems()
    {
        JPluginHelper::importPlugin("jsolr");
        $dispatcher = JEventDispatcher::getInstance();

        $result = $this->getQuery();

        if (!is_null($result)) {
            $documents = $result->getDocuments();

            for ($i = 0; $i < count($documents); $i++) {
                // Jump some hoops to allow plugins to modify document data.
                $tmp = new \Solarium\QueryType\Update\Query\Document\Document($documents[$i]->getFields());

                $dispatcher->trigger('onJSolrSearchPrepareData', array($tmp));

                $documents[$i] = $tmp;
            }

            return $documents;
        }

        return $result;
    }

    public function getHighlighting()
    {
        $result = $this->getQuery();

        if (!is_null($result)) {
            return $result->getHighlighting();
        }

        return $result;
    }

    /**
     * A convenience method for getting the Did You Mean text and a url for
     * changing the search query to the Did You Mean text.
     */
    public function getDidYouMean()
    {
        if ($spellcheck = $this->getQuery()->getSpellcheck()) {
            if (!$spellcheck->getCorrectlySpelled()) {
                $collation = array_shift($spellcheck->getCollations());

                $correction = array_shift($collation->getCorrections());

                if ($correction) {
                    $url = clone JUri::getInstance();
                    $url->setVar('q', $correction);

                    $didYouMean = new StdClass();
                    $didYouMean->value = $correction;
                    $didYouMean->url = (string)$url;

                    return $didYouMean;
                }
            }
        }

        return null;
    }

    public function getTotal()
    {
        return $this->getQuery()->getNumFound();
    }

    public function getStart()
    {
        return $this->getState("list.start", 0);
    }

    public function getLimit()
    {
        return $this->getState("list.limit", JFactory::getApplication()->getCfg('list.limit', 10));
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
     */
    protected function getStoreId($id = '')
    {
        // Add some of the querying params to the store id.
        $id .= ':'.$this->getState('query.q');

        if ($params = $this->getState('params')) {
            $id .= ':'.$params->get('fq');
        }

        if ($dimension = $this->getState('query.dimension')) {
            $id .= ':'.$dimension;
        }

        // Add the list state to the store id.
        $id .= ':'.$this->getState('list.start');
        $id .= ':'.$this->getState('list.limit');
        $id .= ':'.$this->getState('list.ordering');
        $id .= ':'.$this->getState('list.direction');

        return md5($this->context.':'.$id);
    }

    /**
     * Method to get the search form.
     *
     * @param   array  $data      An optional array of data for the form to
     * interrogate.
     * @param   bool   $loadData  True if the form is to load its own data
     * (default case), false if not.
     *
     * @return  JForm  A JForm object on success, false on failure.
     */
    public function getForm($data = array(), $loadData = true)
    {
        if (!is_null($this->form)) {
            return $this->form;
        }

        $context = $this->get('option').'.'.$this->getName();

        // load a custom form xml based on the dimension alias.
        $source = 'search';

        if ($alias = $this->getState('query.dimension')) {
            if ($table = $this->fetchDimension($alias)) {
                $template = JFactory::getApplication()->getTemplate();
                $overridePath = JPATH_ROOT.'/templates/'.$template.'/html/com_jsolr/forms/'.$table->alias.'.xml';

                if (JFile::exists($overridePath)) {
                    $source = $table->alias;
                }
            }
        }

        $this->form = $this->loadForm($context, $source, array('load_data'=>$loadData));

        if (empty($this->form)) {
            return false;
        }

        return $this->form;
    }

    /**
     * (non-PHPdoc)
     * @see JModelForm::loadFormData()
     */
    protected function loadFormData()
    {
        $data = array();

        $query = JUri::getInstance()->getQuery(true);

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
     * Get's the language, either from the item or from the Joomla environment.
     *
     * @param   bool  $includeRegion  True if the region should be included, false
     * otherwise. E.g. If true, en-AU would be returned, if false, just en
     * would be returned.
     *
     * @return  string  The language code.
     */
    protected function getLanguage($lang, $includeRegion = true)
    {
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
     * @return  array  A list of applied filters based on any currently selected facets.
     */
    public function getAppliedFacetFilters()
    {
        $fields = array();

        foreach ($this->getForm()->getFieldset('facets') as $facet) {
            if (array_search("JSolr\Form\Fields\Facetable", class_implements($facet)) !== false) {
                if(isset($facet->value)){
                    $fields[] = $facet;
		}else{
			$fields[] = $facet;

		}
                
            }
        }

        return $fields;
    }

    /**
     * Gets a list of dimensions.
     *
     * @return  array  A list of dimensions.
     */
    public function getDimensions()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $db->quoteName(explode(', ', 'a.id, a.name, a.alias, a.access')));

        $query->from($db->quoteName('#__jsolr_dimensions', 'a'));

        $query->order('a.ordering ASC');

        $db->setQuery($query);

        $results = $db->loadObjectList();

        $url = clone JUri::getInstance();
        $url->delVar('dim');

        $dimensions = array();

        $search = new stdClass();
        $search->name = JText::_('COM_JSOLR_SEARCH_DIMENSIONS_ALL');
        $search->alias = null;
        $search->url = (string)$url;

        if (!$this->getState('query.dimension')) {
            $search->active = true;
        }

        $dimensions[] = $search;

        foreach ($results as $result) {
            $url->setVar('dim', $result->alias);

            $result->url = (string)$url;

            if ($this->getState('query.dimension') == $result->alias) {
                $result->active = true;
            }

            $dimensions[] = $result;
        }

        if ((int)$this->getState('params')->get('advanced_link') == 2) {
            $url->delVar('dim');
            $url->setVar('view', 'advanced');

            $advanced = new stdClass();
            $advanced->name = JText::_('COM_JSOLR_SEARCH_DIMENSIONS_ADVANCED');
            $advanced->alias = null;
            $advanced->url = (string)$url;

            $dimensions[] = $advanced;
        }

        return $dimensions;
    }

    /**
     * Fetch the dimension based on the alias.
     *
     * @param   string               $alias  The dimension alias.
     *
     * @return  JSolrTableDimension  The dimension record or null if no record
     * is found.
     */
    private function fetchDimension($alias)
    {
        $table = $this->getTable('Dimension', 'JSolrTable');

        if ($table->load(array('alias'=>$alias))) {
            return $table;
        } else {
            return null;
        }
    }

    /**
     * Get the template name associated with the dimension or "default" if no
     * template is found.
     *
     * The dimension's alias is used to load a unique results template for the
     * dimension.
     *
     * This method will search the current template's com_jsolr html overrides,
     * looking for a file called results_[dimension_alias].
     *
     * @return  string  The template name assoicated with the dimension or
     * "default" if no template is found.
     */
    public function getDimensionTemplate()
    {
        $tmpl = 'default';

        if ($alias = $this->getState('query.dimension')) {
            if ($table = $this->fetchDimension($alias)) {
                $template = JFactory::getApplication()->getTemplate();
                $overridePath = JPATH_ROOT.'/templates/'.$template.'/html/com_jsolr/search/results_'.$table->alias.'.php';

                if (JFile::exists($overridePath)) {
                    $tmpl = $table->alias;
                }
            }
        }

        return $tmpl;
    }

    /**
     * Applies additional \JSolr\Form\Fields queries to the main query object.
     *
     * @return  \Solarium\QueryType\Select\Query\FilterQuery[]  An array of
     * filter queries.
     */
    protected function getFormFieldFilters()
    {
        $filters = array();

        foreach ($this->getForm()->getFieldsets() as $fieldset) {
            foreach ($this->getForm()->getFieldset($fieldset->name) as $field) {
                if (array_search("JSolr\Form\Fields\Filterable", class_implements($field)) !== false) {
                    if ($filter = $field->getFilter()) {
                        $filters[] = $filter;
                    }
                }
            }
        }

        return $filters;
    }

    /**
     * Applies \JSolr\Form\Fields facets configuration to the main query object.
     *
     * @return
     * \Solarium\QueryType\Select\Query\Component\Facet\AbstractFacet[]  An
     * array of facet fields.
     */
    protected function getFormFieldFacetQueries()
    {
        $facets = array();

        foreach ($this->getForm()->getFieldset('facets') as $facet) {
            if (array_search("JSolr\Form\Fields\Facetable", class_implements($facet)) !== false) {
                $facets[] = $facet->getFacetQuery();
            }
        }

        return $facets;
    }

    /**
     * Applies \JSolr\Form\Fields sorts to the main query object.
     *
     * @return  array  An array of sorts.
     */
    protected function getFormFieldSorts()
    {
        $sorts = array();

        foreach ($this->getForm()->getFieldsets() as $fieldset) {
            foreach ($this->getForm()->getFieldset($fieldset->name) as $sort) {
                if (array_search("JSolr\Form\Fields\Sortable", class_implements($sort)) !== false) {
                    $sorts = array_merge($sorts, $sort->getSort());
                }
            }
        }

        return $sorts;
    }
}
