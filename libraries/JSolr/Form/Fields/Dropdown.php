<?php
/**
 * @copyright   Copyright (C) 2013-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

\JLoader::import('joomla.form.formfield');
\JLoader::import('joomla.form.helper');

\JFormHelper::loadFieldClass('list');

use \JText as JText;
use \Joomla\Utilities\ArrayHelper;

class Dropdown extends \JFormFieldList
{
    /**
     * The form field type.
     *
     * @var         string
     */
    protected $type = 'JSolr.Dropdown';

    protected $layout = 'jsolr.form.fields.dropdown';

    protected function getInput()
    {
        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['options'] = $this->getOptions();

        $data['selected'] = $this->getSelected();

        return $data;
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = array();
        foreach ($this->element->children() as $item) {
            // Only add <option /> elements.
            if ($item->getName() != 'option') {
                continue;
            }

            $option = array();
            $option['label'] = (string)$item;
            $option = array_merge($option, current($item->attributes()));

            $value = ArrayHelper::getValue($option, 'value', null, 'string');

            $option['selected'] = $value == $this->value;

            $uri = clone \JSolr\Search\Factory::getSearchRoute();

            if (!empty($value)) {
                $uri->setVar($this->name, $value);
            } else {
                $uri->delVar($this->name);
            }

            $option['uri'] = htmlentities((string)$uri, ENT_QUOTES, 'UTF-8');

            // Add the option object to the result set.
            $options[] = $option;
        }

        return $options;
    }

    protected function getSelected()
    {
        $selected = null;

        foreach ($this->getOptions() as $option) {
            if ($option['selected']) {
                $selected = $option;
            }
        }

        return $selected;
    }
}
