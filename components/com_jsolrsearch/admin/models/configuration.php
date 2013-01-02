<?php 
/**
 * A model that provides configuration options for JSolrSearch.
 * 
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.registry.registry');
jimport('joomla.application.component.model');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."lib".DS."apache".DS."solr".DS."service.php");

class JSolrSearchModelConfiguration extends JModel
{	
	public function getHost()
	{
		$params = JComponentHelper::getParams($this->option);
		
		$url = $params->get('host');
		
		if ($params->get('username') && $params->get('password')) {
			$url = $params->get('username') . ":" . $params->get('password') . "@" . $url;
		}
		
		return $url;
	}
	
	public function test()
	{
		$params = JComponentHelper::getParams($this->option);
		
		$client = new JSolrApacheSolrService($this->getHost(), $params->get('port'), $params->get('path'));

		$response = $client->ping();
		
		if ($response === false) {
			$this->setError(JText::_("COM_JSOLRSEARCH_PING_FAILED"));
			return false;
		}

		return true;
	}
}