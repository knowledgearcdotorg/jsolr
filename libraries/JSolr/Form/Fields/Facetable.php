<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

/**
 * A facet interface.
 *
 * Implement this interface when the form field must provide facets for the
 * query.
 */
interface Facetable
{
    /**
     * Gets a facet to apply to a query.
     *
     * @return  \Solarium\QueryType\Select\Query\Component\Facet\AbstractFacet
     * The facet to apply.
     */
    public function getFacetQuery();
}
