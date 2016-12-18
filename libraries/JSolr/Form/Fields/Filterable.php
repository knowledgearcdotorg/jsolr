<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

/**
 * A filter interface.
 *
 * Implement this interface when the form field must provide filters for the
 * query (E.g. Solr field fq).
 */
interface Filterable
{
    /**
     * Applies filter configuration directly to the Solr query.
     *
     * @param   \Solarium\QueryType\Select\Query\Query  $query  The query being executed.
     *
     * @return  \Solarium\QueryType\Select\Query\Query  The query being executed (for chaining).
     */
    //public function applyFilter($query);
}
