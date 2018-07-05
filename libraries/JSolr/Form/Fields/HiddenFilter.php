<?php
/**
 * @copyright   Copyright (C) 20132016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

use \JFactory as JFactory;
use \Joomla\Utilities\ArrayHelper as JArrayHelper;
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
     * (nonPHPdoc)
     * @see JSolrFilterable::getFilters()
     */
    public function getFilter()
    {
        $application = JFactory::getApplication();

        $filter = null;

        if ($value = $application->input->getString($this->name, null)) {
            $filter = new \Solarium\QueryType\Select\Query\FilterQuery();
            $filter->setKey($this->name.".".$this->filter);
            $filter->setQuery($this->filter.":".$value);
        }

        return $filter;
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
                return $this->getAttribute($name, null);

                break;

            case 'label':
                // Give users two options for labels:
                // 1. define a label with the selected value available,
                // 2. no label will result in a name+value lang constant.
                $label = $this->getAttribute($name, null);

                if ($label) {
                    return JText::sprintf($label, $this->value);
                } else {
                    return JText::_('COM_JSOLR_FILTER_'.
                        JString::strtoupper($this->name)."_".
                        str_replace(' ', '', JString::strtoupper($this->value)));
                }

                break;

            default:
                return parent::__get($name);
        }
    }

    /**
     * Gets the filter to apply to a query.
     *
     * @return  \Solarium\QueryType\Select\Query\FilterQuery  The filter to apply.
     */

}