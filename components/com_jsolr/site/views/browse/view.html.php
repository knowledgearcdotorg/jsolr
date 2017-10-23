<?php
/**
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.filesystem.path');
jimport('joomla.utilities.arrayhelper');

class JSolrViewBrowse extends JViewLegacy
{
    protected $state;

    protected $items;

    protected $params;

    public function display($tpl = null)
    {
        $this->state = $this->get("State");

        $this->items = $this->get("Items");

        $this->params = $this->state->get('params');

        $active = JFactory::getApplication()->getMenu()->getActive();

        if (isset($active->query['layout'])) {
            $this->setLayout($active->query['layout']);
        }

        parent::display($tpl);
    }
}
