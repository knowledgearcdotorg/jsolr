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

class SearchTool extends \JFormFieldList
{
    /**
     * The form field type.
     *
     * @var         string
     */
    protected $type = 'JSolr.SearchTool';

    protected function getInput()
    {
        $layout = new \JLayoutFile('jsolr.form.fields.searchtool');
        return $layout->render($this);
    }

    /**
     * Gets the currently selected label.
     *
     * @return  string  The currently selected label.
     */
    protected function getSelectedLabel()
    {
        foreach ($this->element->children() as $option) {
            // Only add <option /> elements.
            if ($option->getName() != 'option') {
                continue;
            }

            $selected = ((string) $option['value']) == $this->value;

            if( $selected ) {
                return trim((string) $option);
            }

        }

        return "";
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

        foreach ($this->element->children() as $option) {

            // Only add <option /> elements.
            if ($option->getName() != 'option') {
                continue;
            }

            $selected = ((string)$option['value']) == $this->value;

            // Create a new option object based on the <option /> element.
            $tmp = '<li role="presentation" class="' . ($selected ? 'active' : '') . '" data-value="' . ((string) $key) . '">' . $link . '</li>';


            // Add the option object to the result set.
            $options[] = $tmp;
        }

        reset($options);

        return $options;
    }

    /**
     * Render contents of <li>
     * @param unknown_type $element
     * @return string
     */
    protected function getOption($option)
    {
        return trim((string) $option);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'options':
                return $this->getOptions();
                break;

            case 'label':
                return $this->getSelectedLabel();
                break;

            default:
                return parent::__get($name);
        }
    }
}
