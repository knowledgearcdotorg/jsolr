<?php
/**
 * A view for configuring the JSolrIndex component's settings.
 *
 * @package     JSolr.Index
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use \JSolr\Helper;

class JSolrIndexViewConfiguration extends JViewLegacy
{
    protected $canDo;

    function display($tpl = null)
    {
        $this->canDo = JSolrIndexHelper::getActions();

        $this->modules = JModuleHelper::getModules('jsolrindex');

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('Configuration'), 'config.png');

        if ($this->canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_jsolrindex');

            JToolBarHelper::divider();
        }
    }
}
