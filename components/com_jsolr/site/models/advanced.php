<?php
/**
 * A model that provides advanced search capabilities.
 *
 * @package     JSolr.Search
 * @subpackage  Model
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.filesystem.path');
jimport('joomla.application.component.modelform');

use \JSolr\Search\Factory;
use \JSolr\Form\Form;

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

        $this->setState('query.o', $application->input->getString("o", null, "string"));

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

        $uri->setVar("option", "com_jsolr");

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
        $query = \JSolr\Search\Factory::getSearchRoute()->getQuery(true);

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
}
