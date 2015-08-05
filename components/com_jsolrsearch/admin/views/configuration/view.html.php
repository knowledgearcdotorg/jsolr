<?php

/**

 * A view for configuring the JSolrSearch component's settings.

 *

 * @package		JSolr.Search

 * @subpackage	View

 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.

 * @license     This file is part of the JSolrSearch component for Joomla!.



   The JSolrSearch component for Joomla! is free software: you can redistribute it

   and/or modify it under the terms of the GNU General Public License as

   published by the Free Software Foundation, either version 3 of the License,

   or (at your option) any later version.



   The JSolrSearch component for Joomla! is distributed in the hope that it will be

   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of

   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

   GNU General Public License for more details.



   You should have received a copy of the GNU General Public License

   along with the JSolrSearch component for Joomla!.  If not, see

   <http://www.gnu.org/licenses/>.



 * Contributors

 * Please feel free to add your name and email (optional) here if you have

 * contributed any source code changes.

 * Name							Email

 * Hayden Young					<haydenyoung@knowledgearc.com>

 *

 */



defined( '_JEXEC' ) or die( 'Restricted access' );



if (version_compare(JVERSION, "3.0", "l"))

	jimport('joomla.application.component.view');



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