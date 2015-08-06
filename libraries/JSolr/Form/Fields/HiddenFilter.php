<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

use \JFactory as JFactory;
use \JArrayHelper as JArrayHelper;
use \JString as JString;
use \JText as JText;
use \JFormFieldHidden as JFormFieldHidden;

\JLoader::import('joomla.form.formfield');
\JLoader::import('joomla.form.helper');

\JFormHelper::loadFieldClass('hidden');

class HiddenFilter extends JFormFieldHidden implements Filterable
{
    /**
     * The form field type.
     *
     * @var         string
     */
    protected $type = 'JSolr.HiddenFilter';

    /**
     * (non-PHPdoc)
     * @see JSolrFilterable::getFilters()
     */
    public function getFilters()
    {
        $application = JFactory::getApplication();

        $filters = array();

        if ($value = $application->input->getString($this->name, null)) {
            $filters[] = $this->filter.":".$value;
        }

        return (count($filters)) ? $filters : array();
    }

    /**
     * Gets the remove url for the applied hidden filter.
     *
     * @return string The filter uri for the current facet.
     */
    protected function getFilterURI()
    {
        $url = clone \JSolr\Search\Factory::getSearchRoute();

        $url->delVar($this->name);

        return (string)$url;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'filter':
                return JArrayHelper::getValue($this->element, $name, null, 'string');

                break;

            case 'label':
                // Give users two options for labels:
                // 1. define a label with the selected value available,
                // 2. no label will result in a name+value lang constant.
                $label = JArrayHelper::getValue($this->element, $name, null, 'string');

                if ($label) {
                    return JText::sprintf($label, $this->value);
                } else {
                    return JText::_('COM_JSOLRSEARCH_FILTER_'.
                        JString::strtoupper($this->name)."_".
                        str_replace(' ', '', JString::strtoupper($this->value)));
                }

                break;

            default:
                return parent::__get($name);
        }
    }
}
