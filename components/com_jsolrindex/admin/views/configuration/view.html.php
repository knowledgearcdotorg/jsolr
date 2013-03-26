<?php 
/**
 * A view for configuring the JSolrIndex component's settings.
 * 
 * @author		$LastChangedBy$
 * @package	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
class JSolrIndexViewConfiguration extends JViewLegacy
{
    function display($tpl = null)
    {
    	JHtml::_('behavior.framework', true);
    	
    	$document = JFactory::getDocument();

    	$document->addStyleSheet(JURI::root()."media/com_jsolrindex/css/jsolrindex.css");
    	$document->addScript(JURI::root() . "media/com_jsolrindex/js/jsolrindex.js");        
    	
    	$this->addToolbar();

		JSolrIndexHelper::addSubmenu(JRequest::getCmd('view', 'configuration'));
		    	
        parent::display($tpl);
    }
    
    protected function addToolbar()
    {
    	JToolBarHelper::title(JText::_('Configuration'), 'config.png');
    	
		JToolBarHelper::preferences('com_jsolrindex');
		JToolBarHelper::divider();
    }
}