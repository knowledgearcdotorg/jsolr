<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

/**
 * A facet interface.
 *
 * Implement this interface when the form field must provide facets for the
 * query (E.g. Solr field fq).
 */
interface Facetable
{
    /**
     * Gets the facet HTML input (a list of links).
     *
     * @return string The facet HTML input (a list of links).
     */
    function getFacetInput();
}
