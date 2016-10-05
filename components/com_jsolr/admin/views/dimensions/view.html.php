<?php
/**
 * View for managing search dimensions.
 *
 * @package    JSolr
 * @copyright  Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class JSolrViewDimensions extends JViewLegacy
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The model state
     *
     * @var  object
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  JForm
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        JSolrHelper::addSubmenu('dimensions');

        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        return parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        $canDo = JHelperContent::getActions('com_jsolr');

        $user  = JFactory::getUser();

        JToolbarHelper::title(JText::_('COM_JSOLR_DIMENSIONS_HEADING'));

        if ($canDo->get('core.create')) {
            JToolbarHelper::addNew('dimension.add');
        }

        if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) {
            JToolbarHelper::editList('dimension.edit');
        }

        if ($canDo->get('core.edit.state')) {
            JToolbarHelper::publish('dimensions.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('dimensions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::archiveList('dimensions.archive');
            JToolbarHelper::checkin('dimensions.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'dimensions.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.edit.state')) {
            JToolbarHelper::trash('dimensions.trash');
        }

        if ($user->authorise('core.admin', 'com_jsolr') || $user->authorise('core.options', 'com_jsolr')) {
            JToolbarHelper::preferences('com_jsolr');
        }

        JToolbarHelper::help('JHELP_COMPONENTS_JSOLR_DIMENSIONS');

        JHtmlSidebar::setAction('index.php?option=com_jsolr');
    }
}