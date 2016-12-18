<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

use \JFactory as JFactory;
use \JArrayHelper as JArrayHelper;
use \JText as JText;

\JLoader::import('joomla.form.helper');

class Sort extends SearchTool implements Queryable
{
    /**
     * The form field type.
     *
     * @var         string
     */
    protected $type = 'JSolr.Sort';

    protected function getOptions()
    {
        // Initialize variables.
        $options = array();

        foreach ($this->element->children() as $option) {

            // Only add <option /> elements.
            if ($option->getName() != 'option') {
                continue;
            }

            $attributes = current($option->attributes());

            $value = JArrayHelper::getValue($attributes, 'value', null, 'string');

            $selected = $value == $this->value;

            $uri = clone \JSolr\Search\Factory::getSearchRoute();

            if (!empty($value)) {
                $uri->setVar($this->name, $value);
            } else {
                $uri->delVar($this->name);
            }

            $link = '<a role="menuitem" tabindex="-1" href="'.htmlentities((string)$uri, ENT_QUOTES, 'UTF-8').'">'.
                JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)).
                '</a>';

            // Create a new option object based on the <option /> element.
            $tmp = '<li role="presentation" class="'.($selected ? 'active' : '').'" data-value="'.$value.'">'.$link.'</li>';


            // Add the option object to the result set.
            $options[] = $tmp;
        }

        reset($options);

        return $options;
    }

    /**
     * (non-PHPdoc)
     * @see \JSolr\Form\Fields\Queryable::apply()
     */
    public function apply($query)
    {
        $selected = JFactory::getApplication()->input->get($this->name);

        foreach ($this->element->children() as $option) {
            $attributes = current($option->attributes());

            $value = JArrayHelper::getValue(
                $attributes,
                'value',
                null,
                'string');

            if ($selected != "" && $selected == $value) {
                $sort = JArrayHelper::getValue(
                    $attributes,
                    'field',
                    null,
                    'string');

                $direction = JArrayHelper::getValue(
                    $attributes,
                    'direction',
                    "desc",
                    'string');

                $query->addSort($sort, $direction);
            }
        }

        return $query;
    }
}
