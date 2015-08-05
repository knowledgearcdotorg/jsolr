<?php
/**
 * A helper for the filter module.
 *
 * @package     JSolr.Module
 * @copyright   Copyright (C) 2011-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use \JSolr\Form\Form;

class modJSolrFilterHelper
{
    public static function showFilter()
    {
        $form = Form::getInstance('com_jsolrsearch.search');

        $show = false;

        if (count($form->getFieldset('facets'))) {
            if ($form->isFiltered() || JFactory::getApplication()->input->get("q", null, "string")) {
                $show = true;
            }
        }

        return $show;
    }

    /**
     *
     * @return JSolrForm
     */
    public static function getForm()
    {
        return Form::getInstance('com_jsolrsearch.search');
    }
}