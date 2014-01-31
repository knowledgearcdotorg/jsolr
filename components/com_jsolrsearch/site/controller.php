<?php
/**
 * A controller for managing content sharing.
 * 
 * @package		JSolr.Search
 * @subpackage	Controller
 * @copyright	Copyright (C) 2010-2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.

   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch Component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Hayden Young <hayden@knowledgearc.com>
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com>
 */

defined('_JEXEC') or die('Restricted access');

jimport('jsolr.search.factory');

class JSolrSearchController extends JControllerLegacy 
{
	function advanced()
	{
		$model = $this->getModel("advanced");
		$this->setRedirect(JRoute::_((string)$model->getURI(), false));
	}
	
	public function search()
	{
		$this->setRedirect(JRoute::_(JSolrSearchFactory::getSearchRoute(), false));
	}

	public function display($cachable = false, $urlparams = false)
	{
		$default = "basic";
		
		$viewName = JFactory::getApplication()->input->get("view", $default, 'cmd');
		
		$modelName = $viewName;
		
		if ($modelName == $default) {
			$modelName = "search";
		}

		$model = $this->getModel($modelName);
		
		$view = $this->getView($viewName, JFactory::getApplication()->input->get("format", "html", 'cmd'));
		$view->setModel($model, true);

		if (($viewName == "" || $viewName == $default) && 
			(trim(JFactory::getApplication()->input->get("q", null, 'html')) || $model->getForm()->isFiltered())) {
			$view->setLayout("results");
		}

		return parent::display($cachable, $urlparams);    	
	}
}