<?php
/**
 * A controller for managing Solr indexing and searching.
 * 
 * @author		$LastChangedBy$
 * @package		JSolr
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr component for Joomla!.

   The JSolr component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class JSolrController extends JController 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function save()
	{
		$model = $this->getModel(JRequest::getWord("view"));
		
		$model->save(JRequest::get("post"));

		$view = $this->getView(JRequest::getWord("view"), JRequest::getWord("format", "html"));
		$view->setModel($model, true);
		
		$url = new JURI("index.php");
		$url->setVar("option", JRequest::getWord("option"));
		$url->setVar("view", JRequest::getWord("view"));
		
		$this->setRedirect($url->toString(), JText::_("Configuration successfully saved."));
	}
	
	public function test()
	{
		$model = $this->getModel("configuration");
		
		if ($success = $model->test()) {
			$msg = JText::_("Ping successful");
		} else {
			$msg = JText::_($model->getError());
		}
		
		echo json_encode(array("success"=>$success, "message"=>$msg));
	}
	
	function display()
	{
		$model = $this->getModel(JRequest::getWord("view"));
		
		$view = $this->getView(JRequest::getWord("view"), JRequest::getWord("format", "html"));
		$view->setModel($model, true);
		
		$view->display();
	}
}