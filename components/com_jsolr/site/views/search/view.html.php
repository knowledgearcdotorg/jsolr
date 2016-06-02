<?php
/**
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.

 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.path');
jimport('joomla.utilities.arrayhelper');
jimport('joomla.application.module');
jimport('joomla.application.component');

use \JSolr\Helper;

class JSolrViewSearch extends JViewLegacy
{
    protected $items;

    protected $state;

    protected $form;

    protected $params;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');

        $this->items = $this->get('Items');

        $this->state = $this->get('State');

        $this->params = $this->state->get('params');

        $this->plugins = $this->get('ComponentsList');

        $this->params->set('o', JFactory::getApplication()->input->get('o', null, 'cmd'));

        parent::display($tpl);
    }
}
