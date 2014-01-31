<?php
/**
 * A controller for managing Solr searching.
 * 
 * @package		JSolr.Search
 * @subpackage	Controller
 * @copyright	Copyright (C) 2012 KnowledgeARC Ltd. All rights reserved.
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class JSolrSearchController extends JControllerLegacy
{
	protected $default_view = 'configuration';

	public function test()
	{
		$model = $this->getModel($this->default_view);
		
		if ($success = $model->test()) {
			$msg = JText::_(strtoupper('com_'.$this->get('name').'_'.JRequest::getWord("view", "configuration"))."_PING_SUCCESS");
		} else {
			$msg = JText::_($model->getError());
		}

		$search = array("\n", "\r", "\u", "\t", "\f", "\b", "/", '"');
		$replace = array("\\n", "\\r", "\\u", "\\t", "\\f", "\\b", "\/", "\"");
		$msg = str_replace($search, $replace, $msg);
		
		echo json_encode(array("success"=>$success, "message"=>$msg));
	}

	function display($cachable = false, $urlparams = false)
	{
		parent::display($cachable, $urlparams);
		
		return $this;
	}
}