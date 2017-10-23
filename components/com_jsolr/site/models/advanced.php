<?php
/**
 * A model that provides advanced search capabilities.
 *
 * @package     JSolr.Search
 * @subpackage  Model
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('joomla.application.component.modelform');

use \JSolr\Search\Factory;
use \JSolr\Form\Form;
use \Joomla\Utilities\ArrayHelper;

class JSolrModelAdvanced extends \JSolr\Search\Model\Form
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->set('context', $this->get('option').'.'.$this->getName());
    }

    protected function populateState()
    {
        $application = JFactory::getApplication();

        $this->setState('query.q', $application->input->get("q", null, "html"));

        // Load the parameters.
        $params = $application->getParams();

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

            foreach (ArrayHelper::getValue($matches, 0) as $match) {
                $q[] = '-'.$match;
            }
        }

        if ($application->input->get('aq')) {
            $q[] = $application->input->getHtml('aq');
        }

        $query = trim(implode(" ", $q));

        $filters = $this->getFilters();

        if ($in = \Joomla\Utilities\ArrayHelper::getValue($filters, 'in')) {
            $query = $in.":".$query;
        }

        return $query;
    }

    /**
     * Gets the alias for the query field or qf value.
     *
     * The query field alias maps to an Apache Solr index field name and is
     * terminated with a colon (:). E.g. title:
     *
     * @return  The first occurence of the field name alias.
     */
    private function getQueryFieldAlias()
    {
        $query = JFactory::getApplication()->input->getHtml("q");

        preg_match('/(?<alias>.+):/', $query, $matches);

        return ArrayHelper::getValue($matches, 'alias');
    }

    public function parseQuery()
    {
        $query = JFactory::getApplication()->input->getHtml("q");

        $query = str_replace($this->getQueryFieldAlias().':', '', $query);

        $data = array();

        $nq = array();

        $matches = array();

        preg_match_all('/-"{1}.+"{1}|-\S+/', $query, $matches);

        foreach (ArrayHelper::getValue($matches, 0) as $match) {
            $nq[] = implode("", explode("-", $match, 2));

            $query = str_replace($match, '', $query);
        }

        $data['nq'] = implode(' ', $nq);

        preg_match('/"{1}.+?"{1}/', $query, $eq);

        if ($eq) {
            $data['eq'] = str_replace("\"", "", ArrayHelper::getValue($eq, 0));

            $query = str_replace(ArrayHelper::getValue($eq, 0), '', $query);
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
    public function getUri()
    {
        $uri = new JURI("index.php");

        $uri->setVar("option", "com_jsolr");
        $uri->setVar("view", "search");
        $uri->setVar("Itemid", JRequest::getVar('Itemid'));

        if ($query = $this->buildQuery()) {
            $uri->setVar('q', $query);
        }

        $vars = array('task', 'nq', 'oq', 'eq', 'aq', 'filters');

        foreach (JURI::getInstance()->getQuery(true) as $key=>$value) {
            if (array_search($key, $vars) === false && !empty($value)) {
                $uri->setVar($key, $value);
            }
        }

        // add the filters.
        foreach ($this->getFilters() as $key=>$value) {
            // 'in' filter is handled differently to other filters and
            // shouldn't be passed to search results in querystring.
            if (!empty($value) && $key !== 'in') {
                $uri->setVar($key, $value);
            }
        }

        // finally add the Itemid for basic search
        $uri->setVar('Itemid', \JSolr\Search\Factory::getSearchRoute()->getVar('Itemid'));

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
        if (!is_null($this->form)) {
            return $this->form;
        }

        $context = $this->get('option').'.'.$this->getName();

        $this->form = $this->loadForm($context, 'advanced', array('load_data'=>$loadData));

        if (empty($this->form)) {
            return false;
        }

        return $this->form;
    }

    /**
     * (non-PHPdoc)
     * @see JModelForm::preprocessForm()
     */
    protected function preprocessForm(JForm $form, $data, $group = 'plugin')
    {
        parent::preprocessForm($form, $data, $group);

        // Set 'filters' field group fields to their respective values using the
        // supplied 'data'.
        foreach ($data as $key=>$value) {
            if ($form->getField($key, 'filters') !== false) {
                $form->setValue($key, 'filters', trim($value));
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see JModelForm::loadFormData()
     */
    protected function loadFormData()
    {
        $query = \JSolr\Search\Factory::getSearchRoute()->getQuery(true);

        if (count($query)) {
            $query = array_merge($query, $this->parseQuery());

            $data = $query;
        }

        if ($alias = $this->getQueryFieldAlias()) {
            $data['in'] = $alias;
        }

        $context = $this->get('option').'.'.$this->getName();

        if (version_compare(JVERSION, "3.0", "ge")) {
            $this->preprocessData($this->get('context'), $data);
        }

        return $data;
    }

    protected function getFilters()
    {
        return JFactory::getApplication()->input->get('filters', array(), 'array');
    }
}
