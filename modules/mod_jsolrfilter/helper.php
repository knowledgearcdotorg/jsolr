<?php
/**
 * A helper for the filter module.
 *
 * @package     JSolr.Module
 * @copyright   Copyright (C) 2011-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use \JSolr\Form\Form;

class ModJSolrFilterHelper
{
    public static function showFilter()
    {
        $show = false;

        if (JFactory::getApplication()->input->get("view", null, "string") == 'search') {
            $facets = JFactory::getApplication()->getUserState('com_jsolr.facets');

            if (isset($facets)) {
                if (count($facets)) {
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
