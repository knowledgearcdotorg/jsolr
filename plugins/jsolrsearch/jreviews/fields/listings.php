<?php
/**
 * Renders a list of facets.
 *
 * @package     JSolr.Plugin
 * @subpackage  Form
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die;

use \JSolr\Form\Fields\Facets;

class JSolrFormFieldListings extends Facets
{
    protected $type = 'JSolr.Listings';

    protected function getOptions()
    {
        JPluginHelper::importPlugin("jsolrsearch");
        $dispatcher =& JDispatcher::getInstance();

        // Initialize variables.
        $options = array();

        $facets = $this->getFacets();

        foreach ($facets as $key=>$value) {
            $class = '';

            if ($this->isSelected($key)) {
                $class = ' class="selected"';
            }

            $count = '';

            if (JArrayHelper::getValue($this->element, 'count', 'false', 'string') === 'true') {
                $count = '<span>('.$value.')</span>';
            }

            $text = JArrayHelper::getValue($dispatcher->trigger("onJSolrSearchOptionLookup", array($key)), 0);

            $options[] = '<li'.$class.'><a href="'.$this->getFilterURI($key).'">'.$text.'</a>'.$count.'</li>';
        }

        reset($options);

        return $options;
    }
}
