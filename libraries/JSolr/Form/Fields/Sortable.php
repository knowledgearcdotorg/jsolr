<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

interface Sortable
{
    /**
     * Gets the sort configuration to apply to a query.
     *
     * @return  array  The sort to apply.
     *
     * The returned array must take the form:
     * array("sort field name"=>"asc|desc")
     */
    public function getSort();
}
