<?php
/**
 * Table representing a search dimension.
 *
 * @package    JSolr
 * @copyright  Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Contact Table class.
 */
class JSolrTableDimension extends JTable
{
    /**
     * Ensure the params in json encoded in the bind method
     *
     * @var    array
     */
    protected $_jsonEncode = array('params');

    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  Database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jsolr_dimensions', 'id', $db);
    }

    /**
     * Stores a dimension.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     */
    public function store($updateNulls = false)
    {
        // Transform the params field
        if (is_array($this->params)) {
            $registry = new Registry;
            $registry->loadArray($this->params);
            $this->params = (string) $registry;
        }

        $date   = JFactory::getDate()->toSql();
        $userId = JFactory::getUser()->id;

        $this->modified = $date;

        if ($this->id) {
            // Existing item
            $this->modified_by = $userId;
        } else {
            // New dimension. A dimension created and created_by field can be set by the user,
            // so we don't touch either of these if they are set.
            if (!(int) $this->created) {
                $this->created = $date;
            }

            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }
        }

        // Verify that the alias is unique
        $table = JTable::getInstance('Dimension', 'JSolrTable');

        if ($table->load(array('alias'=>$this->alias)) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(JText::_('COM_JSOLR_ERROR_UNIQUE_ALIAS'));

            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True on success, false on failure
     *
     * @see     JTable::check
     */
    public function check()
    {
        // Check for valid name
        if (trim($this->name) == '')
        {
            $this->setError(JText::_('COM_JSOLR_WARNING_PROVIDE_VALID_NAME'));

            return false;
        }

        // Generate a valid alias
        $this->generateAlias();

        return true;
    }

    /**
     * Generate a valid alias from title / date.
     * Remains public to be able to check for duplicated alias before saving
     *
     * @return  string
     */
    public function generateAlias()
    {
        if (empty($this->alias)) {
            $this->alias = $this->name;
        }

        $this->alias = JApplicationHelper::stringURLSafe($this->alias, $this->language);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
        }

        return $this->alias;
    }
}