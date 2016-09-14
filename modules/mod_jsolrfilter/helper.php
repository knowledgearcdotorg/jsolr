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
            $form = static::getForm();

            if (count($form->getFieldset('facets'))) {
                if ($form->isFiltered() || JFactory::getApplication()->input->get("q", null, "string")) {
                    $show = true;
                }
            }
        }

        return $show;
    }

    public function getForm()
    {
        return Form::getInstance('com_jsolr.search');
    }
}
