<?php
/**
 * @author		$LastChangedBy: spauldingsmails $
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr News Feeds Index plugin for Joomla!.

   The JSolr News Feeds Index plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr News Feeds Index plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr News Feeds Index plugin for Joomla!.  If not, see 
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

class plgJSolrCrawlerJSolrNewsfeeds extends JPlugin 
{
	var $_plugin;
	
	var $_params;
	
	var $_client;
	
	var $_option = 'com_newsfeeds';
	
	/**
	 * Constructor
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('jsolrcrawler', 'jsolrnewsfeeds');
		$this->_params = new JParameter($this->_plugin->params);
		
		require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolr".DS."configuration.php");

		$configuration = new JSolrConfig();
		
		$options = array(
    		'hostname' => $configuration->host,
    		'login'    => $configuration->username,
    		'password' => $configuration->password,
    		'port'     => $configuration->port,
			'path'	   => $configuration->path
		);
		
		$this->_client = new SolrClient($options);
	}

	/**
	* Prepares an article for indexing.
	*/
	private function _getDocument(&$item)
	{
		$doc = new SolrInputDocument();
		
		$lang = $this->_getLang($item);
		
		if ($lang) {
			$lang = "_".str_replace("-", "_", $lang);
		}
		
		$doc->addField('id',  "$this->_option." . $item->id);
		$doc->addField("title", $item->name);
		$doc->addField("title$lang", $item->name);
		$doc->addField("content", $item->link);
		$doc->addField("content$lang", $item->link);
		$doc->addField('option', $this->_option);
		
		$category = new JTableCategory(JFactory::getDBO());
		
		if ($category->load($item->catid)) {
			$doc->addField("category", $category->title);
			$doc->addField("category$lang", $category->title);
		}
		
		return $doc;
	}
	
	private function _getDeleteQueryById($ids)
	{
		$i = 0;
		
		$query = "*:* AND -id:(";
		
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
	
	private function _getLang(&$article)
	{
		$lang = JLanguageHelper::detectLanguage();

		return $lang;
	}
	
	private function _parseRules($rules)
	{
		$array = array();
		
		foreach ($rules as $rule) {
			if (strpos($rule, "newsfeeds") === 0) {
				$item = JArrayHelper::getValue(explode(";", $rule), 1);
				$array[JArrayHelper::getValue(explode("=", $item), 0)] = JArrayHelper::getValue(explode("=", $item), 1);
			}
		}
		
		return $array;
	}
	
	public function onIndex($rules)
	{
		$array = $this->_parseRules($rules);

		$database = JFactory::getDBO();
		
		$query = "SELECT id, name, link, catid " .
				 "FROM jos_newsfeeds AS a WHERE published = 1 AND a.checked_out = 0"; 

		if (JArrayHelper::getValue($array, "newsfeed", null)) {
			$query .= " AND a.id NOT IN (" . $database->Quote(JArrayHelper::getValue($array, "newsfeed", null)) . ")";
		}

		if (JArrayHelper::getValue($array, "category", null)) {
			$query .= " AND a.catid NOT IN (" . $database->Quote(JArrayHelper::getValue($array, "category", null)) . ")";
		}

		$query .= ";";

		$database->setQuery($query);

		$items = $database->loadObjectList("id");

		$ids = array();
		$documents = array();

		foreach ($items as $item) {
			$documents[] = $this->_getDocument($item);
			$ids[] = $item->id;
		}

		try {		
			$this->_client->addDocuments($documents);

			$this->_client->deleteByQuery($this->_getDeleteQueryById($ids));
			
			$this->_client->commit();
		} catch (SolrClientException $e) {
			$log = JLog::getInstance();
			$log->addEntry(array("c-ip"=>"", "comment"=>$e->getMessage()));
		}
	}
}