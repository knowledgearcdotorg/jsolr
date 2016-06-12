<?php
/**
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

\JFormHelper::loadFieldClass('checkboxes');

/**
 * The ContextList form field provides a list of contexts.
 */
class ContextList extends \JFormFieldCheckboxes
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
