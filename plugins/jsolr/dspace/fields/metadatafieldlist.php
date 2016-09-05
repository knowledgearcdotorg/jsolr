<?php
/**
 * Provides a list of metadata fields available in DSpace.
 *
 * @package    JSpace
 * @copyright  Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

JLoader::import('joomla.form.formfield');
JLoader::import('joomla.form.helper');

JFormHelper::loadFieldClass('list');

class JSolrFormFieldMetadataFieldList extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var         string
     */
    protected $type = 'JSolr.MetadataFieldList';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        try {
            JPluginHelper::importPlugin("jsolr");

            $dispatcher = JEventDispatcher::getInstance();

            $result = $dispatcher->trigger("onListMetadataFields");

            $array = JArrayHelper::getValue($result, 0, array());

            foreach ($array as $item) {
                $tmp = JHtml::_(
                    'select.option', $item->name,
                    JText::alt(trim($item->name), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text'
                );

                $options[] = $tmp;
            }

            reset($options);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::DEBUG, 'jsolrcrawler');
        }

        return $options;
    }
}
