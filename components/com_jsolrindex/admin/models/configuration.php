<?php 
/**
 * A model that provides configuration options for JSolrIndex.
 * 
 * @author		$LastChangedBy$
 * @package		Wijiti
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.registry.registry');
jimport('joomla.filesystem.file');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS."lib".DS."apache".DS."solr".DS."service.php");

class JSolrIndexModelConfiguration extends JModel
{	
	var $configuration;
	
	public function __construct()
	{
		parent::__construct();

		require_once($this->getConfig());
		
		$this->configuration = new JSolrIndexConfig();		
	}
	
	/**
	 * Gets the configuration file path.
	 * 
	 * @return The configuration file path.
	 */
	public function getConfig()
	{
		return JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."configuration.php";
	}
	
	public function getHost()
	{
		$url = $this->configuration->host;
		
		if ($this->configuration->username && $this->configuration->password) {
			$url = $this->configuration->username . ":" . $this->configuration->password . "@" . $url;
		}
		
		return $url;
	}

	public function getParam($name)
	{
		return $this->configuration->$name;
	}
	
	public function save($array)
	{	
		require_once($this->getConfig());
		
		$config = new JRegistry('solrindexconfig');
		$config_array = array();

		$config_array["host"] = JArrayHelper::getValue($array, "host");
		$config_array["port"] = JArrayHelper::getValue($array, "port");		
		$config_array["username"] = JArrayHelper::getValue($array, "username");
		$config_array["password"] = JArrayHelper::getValue($array, "password");
		$config_array["path"] = JArrayHelper::getValue($array, "path");
		$config->loadArray($config_array);
		
		JFile::write($this->getConfig(), $config->toString("PHP", "solrindexconfig", array("class"=>"JSolrIndexConfig")));

		$this->configuration = new JSolrIndexConfig();
	}
	
	public function test()
	{
		$client = new Apache_Solr_Service($this->getHost(), $this->configuration->port, $this->configuration->path);

		$response = $client->ping();
		
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
		
	    $rules = file($this->getRobotsFile(), FILE_IGNORE_NEW_LINES);

    	$dispatcher =& JDispatcher::getInstance();
    	
		JPluginHelper::importPlugin("jsolrcrawler", null, true, $dispatcher);

		try {
			$array = $dispatcher->trigger('onIndex', array($rules));
			
			return true;
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			
			return false;
		}	
	}
	
	public function purge()
	{
		if (!$this->test()) {
			return false;
		}
		
		$client = new Apache_Solr_Service($this->getHost(), $this->configuration->port, $this->configuration->path);;
		
		try {
			$client->deleteByQuery("*:*");
			$client->commit();
			return true;
		} catch (Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}		
	}
	
    public function getRobotsFile()
    {
    	return JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."ignore.txt";
    }
}