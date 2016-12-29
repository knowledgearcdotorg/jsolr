<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

\JLoader::import('joomla.form.formfield');
\JLoader::import('joomla.form.helper');

\JFormHelper::loadFieldClass('list');

use \JText as JText;

class FilterList extends Dropdown implements Filterable
{
    /**
     * The form field type.
     *
     * @var         string
     */
    protected $type = 'JSolr.FilterList';

    /**
     * Gets the date filter based on the currently selected value.
     *
     * @return array An array containing a single date filter based on the
     * currently selected value.
     *
     * @see Filterable::getFilters()
     */
    public function getFilter()
    {
        $filter = new \Solarium\QueryType\Select\Query\FilterQuery();

        foreach ($this->element->xpath('option') as $option) {
            $value = (string)$option['value'];

            if ($this->value && $value == $this->value) {
                $selected = (string)$option['filter'];

                $filter->setKey($this->name.".".$this->filter);
                $filter->setQuery($this->filter.":".$selected);

                continue;
            }
        }

        return $filter;
    }
}