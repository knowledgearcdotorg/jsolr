<?php
/**
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

\JFormHelper::loadFieldClass('list');

class JSolrFormFieldContextList extends JFormFieldList
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

        $options = parent::getOptions();

        foreach ($facet as $value=>$count) {
            $options[] = JHtml::_('select.option', $value, $value);
        }

        return $options;
    }
}
