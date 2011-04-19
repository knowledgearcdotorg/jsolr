<?php 
/**
 * A model that provides configuration options for JSolrIndex.
 * 
 * @author		$LastChangedBy: spauldingsmails $
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

class JSolrIndexModelAttachments extends JModel
{	
	var $configuration;
	
	public function __construct()
	{
		parent::__construct();
		
		require_once($this->getConfigFile());
		
		$this->configuration = new JSolrIndexConfig();		
	}
	
	public static function getConfigFile()
	{
		return JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."configuration.php";
	}
	
	/**
	 * Gets configuration details.
	 * 
	 * @return Configuration details.
	 */
	public function getConfig()
	{
		return $this->configuration;
	}
	
	public function getHost()
	{
		$url = "";
		
		switch ($this->getParam("extractor")) {
			case "remote":
				$url = $this->getParam("tika_host");
				
				if ($this->getParam("tika_username") && $this->getParam("tika_password")) {
					$url = $this->getParam("tika_username") . ":" . $this->getParam("tika_password") . "@" . $url;
				}
				
				break;
				
			case "solr":
				$url = $this->getParam("host");
				
				if ($this->getParam("username") && $this->getParam("password")) {
					$url = $this->getParam("username") . ":" . $this->getParam("password") . "@" . $url;
				}

				break;
		
			default:
				
				break;
		}
					
		return $url;
	}

	public function getParam($name)
	{
		return $this->getConfig()->$name;
	}
	
	public function save($array)
	{		
		$config = new JRegistry('solrindexconfig');

		$config->loadObject($this->getConfig());
		
		foreach(array_keys($config->toArray()) as $key) {
			if ($value = JArrayHelper::getValue($array, $key)) {
				$config->setValue($key, $value);
			}
		}

		JFile::write($this->getConfigFile(), $config->toString("PHP", "solrindexconfig", array("class"=>"JSolrIndexConfig")));

		$this->configuration = new JSolrIndexConfig();
	}
	
	public function test()
	{
		switch ($this->getParam("extractor")) {
			case "local":
				if (!JFile::exists($this->getParam("tika_app_path"))) {
					$this->setError(JText::_("COM_JSOLRINDEX_TIKA_CANNOT_FIND_LOCAL_PATH"));
					return false;
				}				
				
				break;
				
			case "remote":
				try {
					$client = new Apache_Solr_Service($this->getHost(), $this->getParam("tika_port"), $this->getParam("tika_path"));
					$response = $client->extract(
						JPATH_COMPONENT_ADMINISTRATOR.DS."test.odt",
						array("extractOnly"=>"true")
					);

					if ($response->getHttpStatus() != 200) {
						$this->setError($response->getHttpMessage());
						return false;
					}
				} catch (Apache_Solr_Exception $e) {
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
}