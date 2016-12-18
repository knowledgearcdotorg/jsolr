<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

use \JFactory as JFactory;
use \Joomla\Utilities\ArrayHelper;

\JFormHelper::loadFieldClass('hidden');

class QueryField extends \JFormFieldHidden implements Queryable
{
    /**
     * The form field type.
     *
     * @var         string
     */
    protected $type = 'JSolr.QueryField';

    /**
     * (non-PHPdoc)
     * @see \JSolr\Form\Fields\Queryable::apply()
     */
    public function apply($query)
    {
        $application = JFactory::getApplication();

        $selected = JFactory::getApplication()->input->get($this->name);

        foreach ($this->element->children() as $option) {
            $attributes = current($option->attributes());

            $value = ArrayHelper::getValue(
                $attributes,
                'value',
                null,
                'string');

            if ($selected != "" && $selected == $value) {
                $filter = ArrayHelper::getValue(
                    $attributes,
                    'filter',
                    null,
                    'string');

                if (!$filter) {
                    throw new \Exception("Filter parameter required for JSolr.QueryField form field \"$this->name\".");
                }

                $filter = \JSolr\Helper::localize($filter);

                $query->getEDisMax()->setQueryFields(\JSolr\Helper::localize($filter));
            }
        }

        return $query;
    }
}
