<?php
/**
 * Model for managing a search dimension.
 *
 * @package    JSolr
 * @copyright  Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

JLoader::register('JSolrHelper', JPATH_ADMINISTRATOR . '/components/com_jsolr/helpers/jsolr.php');

class JSolrModelDimension extends JModelAdmin
{
    /**
     * The type alias for this content type.
     *
     * @var    string
     */
    public $typeAlias = 'com_jsolr.dimension';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->published != -2) {
                return;
            }

            return JFactory::getUser()->authorise('core.delete');
        }
    }

    /**
     * Returns a Table object, always creating it
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     */
    public function getTable($type = 'Dimension', $prefix = 'JSolrTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|boolean  A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jsolr.dimension', 'dimension', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object)$data)) {
            // Disable fields for display.
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();

        // Check the session for previously entered form data.
        $data = $app->getUserState('com_jsolr.edit.dimension.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_jsolr.dimension', $data);

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   JTable  $table  The JTable object
     *
     * @return  void
     */
    protected function prepareTable($table)
    {
        $date = JFactory::getDate()->toSql();

        $table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);

        $table->generateAlias();

        if (empty($table->id)) {
            // Set the values
            $table->created = $date;
        } else {
            // Set the values
            $table->modified = $date;
            $table->modified_by = JFactory::getUser()->id;
        }
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $category_id  The id of the parent.
     * @param   string   $alias        The alias.
     * @param   string   $name         The title.
     *
     * @return  array  Contains the modified title and alias.
     */
    protected function generateNewTitle($category_id, $alias, $name)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias))) {
            if ($name == $table->name) {
                $name = JString::increment($name);
            }

            $alias = JString::increment($alias, 'dash');
        }

        return array($name, $alias);
    }
}