<?php
/**
 * A model that provides JSolr-specific form functionality.
 *
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Search\Model;

use \JFactory as JFactory;
use \JFile as JFile;
use \JPluginHelper as JPluginHelper;
use \JText as JText;
use \JArrayHelper as JArrayHelper;

abstract class Form extends \JModelForm
{
    /**
     * Override to use JSorlForm.
     * Method to get a form object.
     *
     * @param   string   $name     The name of the form.
     * @param   string   $source   The form source. Can be XML string if file flag is set to false.
     * @param   array    $options  Optional array of options for the form creation.
     * @param   boolean  $clear    Optional argument to force load a new form.
     * @param   string   $xpath    An optional xpath to search for the fields.
     *
     * @return  mixed  JForm object on success, False on error.
     *
     * @see     JForm
     */
    protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
    {
        // Handle the optional arguments.
        $options['control'] = JArrayHelper::getValue($options, 'control', false);

        // Create a signature hash.
        $hash = md5($source . serialize($options));

        // Check if we can use a previously loaded form.
        if (isset($this->_forms[$hash]) && !$clear) {
            return $this->_forms[$hash];
        }

        // also try loading form overrides from template.
        $template = JFactory::getApplication()->getTemplate();
        \JSolr\Form\Form::addFormPath(JPATH_ROOT.'/templates/'.$template.'/html/com_jsolr/forms');

        try {
            $form = \JSolr\Form\Form::getInstance($name, $source, $options, false, $xpath); //JSolrForm instead of JForm

            if (isset($options['load_data']) && $options['load_data']) {
                // Get the data for the form.
                $data = $this->loadFormData();
            } else {
                $data = array();
            }

            // Allow for additional modification of the form, and events to be triggered.
            // We pass the data because plugins may require it.
            $this->preprocessForm($form, $data);

            // Load the data into the form after the plugins have operated.
            $form->bind($data);
        } catch (Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Store the form for later.
        $this->_forms[$hash] = $form;

        return $form;
    }
}
