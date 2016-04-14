<?php
/**
 * @copyright   Copyright (C) 2011-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Search;

abstract class Search extends \JPlugin
{
    protected $highlighting = array();

    protected $operators = array();

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->loadLanguage();
    }

    /**
     * Gets the destination URI for the individual search result.
     *
     * @param mixed $document An individual search result document.
     *
     * @return string The destination URI for the individual search result.
     */
    public abstract function onJSolrSearchURIGet($document);

    /**
     * Lists fields and the boosts to associate with each.
     *
     * Override this method to add more query field values.
     */
    public function onJSolrSearchQFAdd()
    {
        $qf = array();

        $boosts = explode(',', $this->get('params')->get('boosts', null));

        foreach ($boosts as $boost) {
            if ($boost)
                $qf[] = \JSolr\Helper::localize($boost);
        }

        return $qf;
    }

    /**
     * Define additional boost queries and add to the search query.
     *
     * Override this method to add more boost queries.
     */
    public function onJSolrSearchPrepareBoostQueries()
    {
        $bq = array();

        $boosts = explode(',', $this->get('params')->get('query_boosts', null));

        foreach ($boosts as $boost) {
            if ($boost)
                $bq[] = \JSolr\Helper::localize($boost);
        }

        return $bq;
    }

    /**
     * Gets a list of operator mappings for this search plugin.
     *
     * Each operator takes the form array[facet_name] = [search_name] where
     * [facet_name] is the field to browse on and [search_name] is the
     * corresponding field to search on when navigating from browse to search.
     *
     * The [search_name] is used for stripping the correct operators off of
     * the query.
     */
    final public function onJSolrSearchOperatorsGet()
    {
        return $this->operators;
    }

    /**
     * Lists fields that have highlighting applied on the found text.
     */
    final public function onJSolrSearchHLAdd()
    {
        $hl = array();

        foreach ($this->highlighting as $higlighting) {
            if ($higlighting) {
                $hl[] = \JSolr\Helper::localize($higlighting);
            }
        }

        return $hl;
    }

    /**
     * Registers the plugin details.
     *
     * Includes the name, label and context of the plugin.
     *
     * @return array An array of plugin details.
     */
    public function onJSolrSearchRegisterPlugin()
    {
        return array(
            'name'=>$this->_name,
            'label'=>'PLG_JSOLRSEARCH_'.JString::strtoupper($this->_name).'_LABEL',
            'context'=>$this->get('context')
        );
    }
}
