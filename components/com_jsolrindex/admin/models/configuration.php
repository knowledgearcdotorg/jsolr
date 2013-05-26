<?php 
/**
 * A model that provides configuration options for JSolrIndex.
 * 
 * @package		Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.registry.registry');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.helper');

// only import model item for j! 2.5.
if (version_compare(JVERSION, "3.0", "l"))
	jimport('joomla.application.component.modelitem');

jimport('jsolr.index.factory');

class JSolrIndexModelConfiguration extends JModelItem
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getHost()
	{
		$params = JComponentHelper::getParams('com_jsolrindex');
		
		$url = $params->get('host');
		
		if ($params->get('username') && $params->get('password')) {
			$url = $params->get('username') . ":" . $params->get('password') . "@" . $url;
		}
		
		return $url;
	}
	
	public function getItem()
	{
		if (!$this->_item) {
			$params = JComponentHelper::getParams('com_jsolrindex');
			
			$this->_item = new JObject();
			$this->_item->set('host', $params->get('host', null));
			$this->_item->set('port', $params->get('port', null));
			$this->_item->set('path', $params->get('path', null));
			$this->_item->set('index', null);
			
			$client = JSolrIndexFactory::getService();
	
			if ($this->test()) {
				$response = $client->luke();
				$this->_item->set('index', $params->set('index', $response->index));
			} else {
				$this->_item->set('index', $params->set('index', null));
			}
		}
		
		return $this->_item;
	}
	
	public function test()
	{
		$client = JSolrIndexFactory::getService();

		$response = $client->ping(10);
		
		if ($response === false) {
			$this->setError(JText::_("COM_JSOLRINDEX_PING_FAILED"));
			return false;
		}

		return true;
	}
	
	public function index()
	{	
		if (!$this->test()) {
			return false;
		}

    	$dispatcher = JDispatcher::getInstance();
    	
		JPluginHelper::importPlugin("jsolrcrawler", null, true, $dispatcher);

		try {
			$array = $dispatcher->trigger('onIndex');
			
			return true;
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			
			return false;
		}	
	}
	
	public function purge()
	{
		$params = JComponentHelper::getParams('com_jsolrindex');
		
		if (!$this->test()) {
			return false;
		}		
		
		$client = JSolrIndexFactory::getService();
		
		try {
			$client->deleteByQuery("*:*");
			$client->commit();
			return true;
		} catch (Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}		
	}
	
	public function testTika()
	{
		$params = JComponentHelper::getParams('com_jsolrindex');
		
		switch ($params->get("extractor")) {
			case "local":
				if (!JFile::exists($params->get("local_tika_app_path"))) {
					$this->setError(JText::_("COM_JSOLRINDEX_TIKA_CANNOT_FIND_LOCAL_PATH"));
					return false;
				}				
				
				break;
				
			case "remote":
				try {
					$client = JSolrIndexFactory::getService();
					$response = $client->extract(
						JPATH_COMPONENT_ADMINISTRATOR."/test.odt",
						array("extractOnly"=>"true")
					);

					if ($response->getHttpStatus() != 200) {
						$this->setError($response->getHttpMessage());
						return false;
					}
				} catch (JSolrApacheSolrException $e) {
					$this->setError($e->getMessage());
					return false;
				}				

				break;
				
			case "solr":

				break;
				
			default:
				
				break;
		}

		return true;
	}
	
	public function getAttachmentHost()
	{
		$params = JComponentHelper::getParams('com_jsolrindex');
		
		$url = "";
		
		switch ($params->get("extractor")) {
			case "remote":
				$url = $params->get("remote_tika_host");
				
				if ($params->get("remote_tika_username") && $params->get("remote_tika_password")) {
					$url = $params->get("remote_tika_username") . ":" . $params->get("remote_tika_password") . "@" . $url;
				}
				
				break;
				
			case "solr":
				$url = $this->getHost();

				break;
		
			default:
				
				break;
		}
					
		return $url;
	}
}