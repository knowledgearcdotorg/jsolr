<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Form\Fields;

use \JFactory as JFactory;
use \Joomla\Utilities\ArrayHelper;
use \JText as JText;

\JLoader::import('joomla.form.helper');

\JFormHelper::loadFieldClass('list');

class Sort extends Dropdown implements Sortable
{
    /**
     * The form field type.
     *
     * @var         string
     */
    protected $type = 'JSolr.Sort';

    /**
     * (non-PHPdoc)
     * @see \JSolr\Form\Fields\Sortable::getSort()
     */
    public function getSort()
    {
        $selected = $this->value;

        foreach ($this->element->children() as $option) {
            $attributes = current($option->attributes());

            $value = ArrayHelper::getValue(
                $attributes,
                'value',
                null,
                'string');

            if ($selected != "" && $selected == $value) {
                $sort = ArrayHelper::getValue(
                    $attributes,
                    'sort',
                    null,
                    'string');

                $direction = ArrayHelper::getValue(
                    $attributes,
                    'direction',
                    "desc",
                    'string');

                if (!$sort) {
                    throw new \Exception("Sort parameter required for JSolr.Sort form field \"$this->name\".");
                }

                return array($sort=>$direction);
            }
        }

        return array();
    }
}
