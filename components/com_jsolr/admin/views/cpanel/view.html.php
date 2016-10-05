<?php
/**
 * A view for configuring the JSolr component's settings.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class JSolrViewCPanel extends JViewLegacy
{
    protected $item;

    protected $canDo;

    function display($tpl = null)
    {
        JSolrHelper::addSubmenu('dimensions');

        $this->item = $this->get("Item");

        $this->canDo = JSolrHelper::getActions();

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_JSOLR_CPANEL_HEADING'), 'config.png');

        if ($this->canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_jsolr');

            JToolBarHelper::divider();
        }
    }
}
