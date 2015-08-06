<?php
/**
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.
 */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class JSolrSearchViewAdvanced extends JViewLegacy
{
    protected $form;

    public function display($tpl = null)
    {
        JHtml::_('behavior.framework', true);

        $document = JFactory::getDocument();

        $this->form    = $this->get('Form');

        $this->state = $this->get('State');

        parent::display($tpl);
    }
}
