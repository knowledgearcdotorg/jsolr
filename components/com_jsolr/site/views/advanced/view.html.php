<?php
/**
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     This file is part of the JSolr component for Joomla!.
 */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class JSolrViewAdvanced extends JViewLegacy
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
