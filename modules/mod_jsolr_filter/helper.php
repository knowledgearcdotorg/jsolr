<?php
/**
 * A helper for the filter module.
 *
 * @package     JSolr.Module
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use \JSolr\Form\Form;

class ModJSolrFilterHelper
{
    public static function showFilter()
    {
        $show = false;

        if (JFactory::getApplication()->input->get("view", null, "string") == 'search') {
            $results = JFactory::getApplication()->getUserState('com_jsolr.search.results');

            $facetSet = $results->getFacetSet();

            if (isset($facetSet)) {
                if (count($facetSet)) {
                    $show = true;
                }
            }
        }

        return $show;
    }

    public static function getForm()
    {
        return Form::getInstance('com_jsolr.search');
    }
}
