<?php
/**
 * A view for configuring the JSolrSearch component's settings.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class JSolrSearchViewConfiguration extends JViewLegacy
{
    protected $canDo;

    function display($tpl = null)
    {
        $this->canDo = JSolrSearchHelper::getActions();

        $this->modules = JModuleHelper::getModules('jsolrsearch');

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('Configuration'), 'config.png');

        if ($this->canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_jsolrsearch');

            JToolBarHelper::divider();
        }
    }
}
