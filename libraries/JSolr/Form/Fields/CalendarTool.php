<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

use \JText as JText;
use \JArrayHelper as JArrayHelper;
use \JFactory as JFactory;

/**
 * Renders a calendar search tool form field. Filters the results displayed by
 * a period of time.
 */
class CalendarTool extends SearchTool implements Filterable
{
    /**
     * The form field type.
     *
     * @var  string
     */
    protected $type = 'JSolr.CalendarTool';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = array();

        foreach ($this->element->children() as $option) {
            // Only add <option /> elements.
            if ($option->getName() != 'option') {
                continue;
            }

            $value = (string)$option->attributes()->value;

            $selected = $value == $this->value;

            $uri = clone \JSolr\Search\Factory::getSearchRoute();

            if (!empty($value)) {
                $uri->setVar($this->name, $value);
            } else {
                $uri->delVar($this->name);
            }

            $link = '<a role="menuitem" tabindex="-1" href="'.((string)$uri).'">'.JText::_(trim((string)$option)).'</a>';

            $tmp = '<li role="presentation"'.($selected ? ' class="active" ' : '').' data-value="'.$value.'">'.$link.'</li>';

            // Add the option object to the result set.
            $options[] = $tmp;
        }

        reset($options);

        return $options;
    }

    protected function getSelectedLabel() {
        foreach ($this->element->children() as $option) {
            // Only add <option /> elements.
            if ($option->getName() != 'option') {
                continue;
            }

            $selected = ((string)$option['value']) == $this->value;

            if ($selected) {
                return trim((string)$option);
            }
        }

        return "";
    }

    /**
     * Gets the date filter based on the currently selected value.
     *
     * @return array An array containing a single date filter based on the
     * currently selected value.
     *
     * @see Filterable::getFilters()
     */
    public function getFilters()
    {
        $filters = array();

        foreach ($this->element->xpath('option') as $option) {
            $value = (string)$option['value'];

            if ($this->value && $value == $this->value) {
                $filter = (string)$option['filter'];

                $filters[] = $this->filter.":".$filter;

                continue;
            }
        }

        return (count($filters)) ? $filters : array();
    }

    public function __get($name)
    {
        switch ($name) {
            case 'filter':
                return $this->getAttribute($name, null);

                break;

            case 'filter_quoted':
            case 'show_custom':
                if ($this->getAttribute($name, null) === 'true')
                    return true;
                else
                    return false;

                    break;

            default:
                return parent::__get($name);
        }
    }
}
