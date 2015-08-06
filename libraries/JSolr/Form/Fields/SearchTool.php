<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
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
        $encoded = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
        $label = JText::_($this->getSelectedLabel());
        $options = implode($this->getOptions());

        $html =
<<<HTML
<input
    type="hidden"
    name="$this->name"
    id="$this->id"
    value="$encoded"/>
<div class="jsolr-searchtool">
    <a
        class="dropdown-toggle"
        id="$this->name-selected"
        role="button"
        data-toggle="dropdown"
        data-target="#"
        data-original="$this->value">
        $label
        <b class="caret"></b>

        <ul
            class="dropdown-menu"
            role="menu"
            aria-labelledby="$this->name">$options</ul>
</div>
HTML;

        return $html;
    }

    protected function getSelectedLabel() {
        $ret = "";
        foreach ($this->element->children() as $option)
        {
            // Only add <option /> elements.
            if ($option->getName() != 'option')
            {
                continue;
            }

            $selected = ((string) $option['value']) == $this->value;
            if( $selected ) {
                return trim((string) $option);
            }

        }

        return $ret;
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   11.1
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = array();

        foreach ($this->element->children() as $option)
        {

            // Only add <option /> elements.
            if ($option->getName() != 'option')
            {
                continue;
            }

            $selected = ((string) $option['value']) == $this->value;

            // Create a new option object based on the <option /> element.
            $tmp = '<li role="presentation" class="' . ( $selected ? 'active' : '' ) . '" data-value="' . ((string) $key) . '">' . $link . '</li>';


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
    protected function getOption( $option ) {
        return trim((string) $option);
    }
}
