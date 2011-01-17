<?php 
/**
 * A model that provides configuration options for JSolrSearch.
 * 
 * @author		$LastChangedBy$
 * @package		Wijiti
 * @subpackage	JSolrSearch
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
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
jimport('joomla.filesystem.file');

class JSolrSearchModelConfiguration extends JModel
{	
	var $configuration;
	
	public function __construct()
	{
		parent::__construct();

		require_once($this->getConfig());
		
		$this->configuration = new JSolrSearchConfig();		
	}
	
	/**
	 * Gets the configuration file path.
	 * 
	 * @return The configuration file path.
	 */
	public function getConfig()
	{
		return JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrsearch".DS."configuration.php";
	}

	public function getParam($name)
	{
		return $this->configuration->$name;
	}
	
	public function save($array)
	{	
		require_once($this->getConfig());
		
		$config = new JRegistry('solrconfig');
		$config_array = array();

		$config_array["host"] = JArrayHelper::getValue($array, "host");
		$config_array["port"] = JArrayHelper::getValue($array, "port");		
		$config_array["username"] = JArrayHelper::getValue($array, "username");
		$config_array["password"] = JArrayHelper::getValue($array, "password");
		$config_array["path"] = JArrayHelper::getValue($array, "path");
		$config->loadArray($config_array);
		
		JFile::write($this->getConfig(), $config->toString("PHP", "solrconfig", array("class"=>"JSolrSearchConfig")));

		$this->configuration = new JSolrSearchConfig();
	}
	
	public function test()
	{
		$options = array(
			'hostname' => $this->configuration->host,
			'port'     => $this->configuration->port,
			'path'     => $this->configuration->path,
			'login'	   => $this->configuration->username,
			'password' => $this->configuration->password
		);

		$client = new SolrClient($options);

		try {
			$response = @ $client->ping();

			if ($response->getHTTPStatus() == "200") {
				return true;
			}
		} catch (SolrClientException $e) {
			$this->setError($e->getMessage());
			return false;
		}
	}
}