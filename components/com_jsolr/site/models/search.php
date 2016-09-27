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

    protected $form;

    protected $lang;

    protected $pagination;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->context = $this->option.'.'.$this->name;

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
        $menuParams = new Registry;

        if ($menu = $application->getMenu()->getActive()) {
            $menuParams->loadString($menu->params);
        }

        $mergedParams = clone $menuParams;
        $mergedParams->merge($params);

        $this->setState('params', $mergedParams);

        parent::populateState($ordering, $direction);
    }

    /**
     * Builds the query and returns the result.
     *
     * @return  \Solarium\QueryType\Select\Result  The query result.
     */
    public function getQuery()
    {
        $filters = $this->getForm()->getFilters();

        if (!($this->getState('query.q') || $this->getAppliedFacetFilters())) {
            return null; // nothing passed. Get out of here.
        }

        $store = $this->getStoreId();

        if (!isset($this->cache[$store])) {
            if ($fq = $this->getState('params')->get('fq')) {
                $filters['fq'] = $fq;
            }

            try {
                $client = \JSolr\Factory::getClient();

                $query = $client->createSelect();
                $query->setQuery($this->getState('query.q', "*:*"));

                $query->setStart($this->getState("list.start", 0));

                $limit = $this->getLimit();

                $query->setRows($limit);

                if ($contexts = $this->getState('params')->get('context')) {
                    $query
                        ->createFilterQuery('contexts')
                            ->setQuery(
                                "context_s:(".implode(' OR ', $contexts).")");
                }

                // set query fields.
                $qf = $this->getState('params')->get('qf', self::QF_DEFAULT);

                if ($this->getState('params')->get('menu-qf')) {
                    $qf = $this->getState('params')->get('menu-qf');
                }

                $qf = \JSolr\Helper::localize($qf);

                // set minimum match.
                $mm = $this->getState('params')->get('mm', null);
                $mm = $this->getState('params')->get('menu-mm', $mm);

                if ($mm) {
                    $query->getEDisMax()->setMinimumMatch($mm);
                }

                $query->getEDisMax()->setQueryFields($qf);

                /*
                // set up spellcheck
                $query->getSpellcheck()
                    ->setQuery($this->getState('query.q'))
                    ->setCount(10)
                    ->setBuild(true)
                    ->setCollate(true)
                    ->setExtendedResults(true)
                    ->setCollateExtendedResults(true)
                    ->setCollateParam("maxResultsForSuggest", 1);
                */

                // set highlighting fields.
                $hl = $this->getState('params')->get('hl', self::HL_DEFAULT);

                if ($this->getState('params')->get('menu-hl')) {
                    $hl = $this->getState('params')->get('menu-hl');
                }

                $hl = \JSolr\Helper::localize($hl);

                // set up highlighting.
                $query->getHighlighting()
                    ->setFields($hl)
                    ->setSimplePrefix('<b>')
                    ->setSimplePostfix('</b>');

                // set access.
                if ($this->getState('params')->get('fq_access', 1)) {
                    $viewLevels = JFactory::getUser()->getAuthorisedViewLevels();
                    $access = implode(' OR ', array_unique($viewLevels));

                    if ($access) {
                        $filters['access'] = 'access_i:'.'('.$access.') OR null';
                    }
                }

                // set applied user filters.
                foreach ($filters as $key=>$value) {
                    $query->createFilterQuery($key)->setQuery($value);
                }

                if ($pf = $this->getState('params')->get('pf')) {
                    $query->getEDisMax()->setPhraseFields($pf);
                }

                $query->getFacetSet()->createFacetField('author')->setField('author_s');

                $response = $client->select($query);

                $this->cache[$store] = $response;
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
        $result = $this->getQuery();

        if (!is_null($result)) {
            $documents = $result->getDocuments();
            $linkedDocuments = array();

            foreach ($documents as $document) {
                // Get item url.
                $fields = $document->getFields();
                $fields['link'] = \JSolr\Helper::getUri($document);

                $linkedDocuments[] = new \Solarium\QueryType\Select\Result\Document($fields);
            }

            return $linkedDocuments;
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
        // Add the list state to the store id.
        $id .= ':' . $this->getState('list.start');
        $id .= ':' . $this->getState('list.limit');
        $id .= ':' . $this->getState('list.ordering');
        $id .= ':' . $this->getState('list.direction');

        return md5($this->context . ':' . $id);
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

        $this->form = $this->loadForm($context, 'search', array('load_data'=>$loadData));

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
