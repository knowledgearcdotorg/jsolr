<?php 
/**
 * A view for configuring the JSolrIndex component's settings.
 * 
 * @package		JSolr.Index
 * @subpackage	View
 * @copyright	Copyright (C) 2012-2014 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrIndex component for Joomla!.

   The JSolrIndex component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrIndex component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrIndex component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@knowledgearc.com> 
 * 
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('jsolr.helper');

if (version_compare(JVERSION, "3.0", "l"))
	jimport('joomla.application.component.view');
 
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