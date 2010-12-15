<?php
/**
 * @author		$LastChangedBy$
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr Content Index plugin for Joomla!.

   The JSolr Content Index plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr Content Index plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr Content Index plugin for Joomla!.  If not, see 
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

require_once JPATH_LIBRARIES."/joomla/database/table/section.php";
require_once JPATH_LIBRARIES."/joomla/database/table/category.php";

class plgCrawlerJSolrContent extends JPlugin 
{
	var $_plugin;
	
	var $_params;
	
	var $_client;
	
	var $_option = 'com_content';
	
	/**
	 * Constructor
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('crawler', 'jsolrcontent');
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
	function _getDocument(&$article)
	{
		$doc = new SolrInputDocument();
		
		$created = JFactory::getDate($article->created);
		$modified = JFactory::getDate($article->modified);
		
		$author = JFactory::getUser($article->created_by);
		
		$doc->addField('id',  "$this->_option." . $article->id);
		$doc->addField('created', $created->toISO8601());
		$doc->addField('modified', $modified->toISO8601());
		$doc->addField('title', $article->title);
		$doc->addField('content', strip_tags($article->introtext . $article->fulltext));
		$doc->addField('metakeywords', $article->metakey);
		$doc->addField('metadescription', $article->metadesc);
		$doc->addField('author', $author->get("name"));
		$doc->addField('option', $this->_option);
		
		$section = new JTableSection(JFactory::getDBO());
		if ($section->load($article->sectionid)) {
			$doc->addField('section', $section->title);
		} else {
			$doc->addField('section', JText::_("Uncategorised"));
		}
		
		$category = new JTableCategory(JFactory::getDBO());
		if ($category->load($article->catid)) {
			$doc->addField('category', $category->title);
		} else {
			$doc->addField('category', JText::_("Uncategorised"));
		}
		
		return $doc;
	}
	
	function _getDeleteQueryById($ids)
	{
		$i = 0;
		
		$query = null;
		
		foreach ($ids as $id) {
			if ($i > 0) {
				$query .= " OR ";	
			}
			
			$query .= "-id:$id";
			
			$i++;	
		}
		
		return $query;
	}
	
	function onIndex()
	{
		$query = "SELECT a.id, a.created, a.modified, a.title, a.introtext, a.fulltext, a.created_by, a.sectionid, a.catid, a.metakey, a.metadesc " .
				 "FROM #__content AS a WHERE a.state = 1 AND a.checked_out = 0;";
		
		$database = JFactory::getDBO();
		$database->setQuery($query);

		$articles = $database->loadObjectList("id");
		
		$ids = array();
		$documents = array();
		
		foreach ($articles as $article) {
			$documents[] = $this->_getDocument($article);
			$ids[] = $article->id;
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