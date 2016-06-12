<?php
/**
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

\JFormHelper::loadFieldClass('checkboxes');

class JSolrFormFieldContextList extends JFormFieldCheckboxes
{
    protected $type = 'JSolr.ContextList';

    protected function getOptions()
    {
        $client = \JSolr\Factory::getClient();

        $query = $client->createSelect();

        $query
            ->setQuery('*:*')
            ->setRows(0)
            ->getFacetSet()
                ->createFacetField('contexts')
                    ->setField('context_s');

        $result = $client->select($query);

        $facet = $result->getFacetSet()->getFacet('contexts');

        foreach ($facet as $value=>$count) {
            $this->element->addChild("option", $value)->addAttribute("value", $value);
        }

        return parent::getOptions();
    }
}
