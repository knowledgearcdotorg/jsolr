<?php
/**
 * @author		$LastChangedBy: spauldingsmails $
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
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
 
// no direct access
defined('_JEXEC') or die();

jimport('joomla.error.log');
jimport('joomla.language.helper');

abstract class JSolrCrawlerPlugin extends JPlugin 
{
	var $_plugin;
	
	var $_params;
	
	var $_client;
	
	var $_option;
	
	/**
	 * Constructor
	 *
	 * @param	string $name The name of the plugin.
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	public function __construct($name, &$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('jsolrcrawler', "jsolr".$name);
		$this->_params = new JParameter($this->_plugin->params);
		
		$this->_option = "com_".$name;
		
		require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."configuration.php");

		$configuration = new JSolrIndexConfig();
		
		$url = $configuration->host;
		
		if ($configuration->username && $configuration->password) {
			$url = $configuration->username . ":" . $configuration->password . "@" . $url;
		}
		
		$this->_client = new Apache_Solr_Service($url, $configuration->port, $configuration->path);
	}

	/**
	* Prepares an article for indexing.
	*/
	protected abstract function getDocument(&$record);
	
	protected function getDeleteQueryById($ids)
	{
		$i = 0;
		
		$query = "option:".$this->_option." AND -id:(";
		
		foreach ($ids as $id) {
			if ($i > 0) {
				$query .= " OR ";	
			}
			
			$query .= $this->_option.".".intval($id);
			
			$i++;	
		}
		
		$query .= ")";
		
		return $query;
	}
	
	protected function getLang(&$item = null)
	{
		$lang = JLanguageHelper::detectLanguage();

		return $lang;
	}
	
	protected function parseRules($rules)
	{
		$array = array();
		
		foreach ($rules as $rule) {
			if (strpos($rule, $this->_option) === 0) {
				$item = JArrayHelper::getValue(explode(";", $rule), 1);
				$array[JArrayHelper::getValue(explode("=", $item), 0)] = JArrayHelper::getValue(explode("=", $item), 1);
			}
		}
		
		return $array;
	}
	
	abstract protected function buildQuery($rules);
	
	protected function getItems($rules)
	{
		$database = JFactory::getDBO();
		$database->setQuery($this->buildQuery($rules));
		return $database->loadObjectList();
	}
	
	public function onIndex($rules)
	{	
		$items = $this->getItems($rules);

		$ids = array();
		$documents = array();

		foreach ($items as $item) {
			$documents[] = $this->getDocument($item);
			$ids[] = $item->id;
		}

		try {
			$response = @ $this->_client->ping();
			
			$this->_client->addDocuments($documents);

			$this->_client->deleteByQuery($this->getDeleteQueryById($ids));
			
			$this->_client->commit();
		} catch (Exception $e) {
			$log = JLog::getInstance();
			$log->addEntry(array("c-ip"=>"", "comment"=>$e->getMessage()));
			
			throw $e;
		}
	}
}