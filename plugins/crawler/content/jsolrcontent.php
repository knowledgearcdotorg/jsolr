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
jimport('joomla.language.helper');

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
	public function __construct(&$subject, $config = array())
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
	private function _getDocument(&$article)
	{
		$doc = new SolrInputDocument();
		
		$created = JFactory::getDate($article->created);
		$modified = JFactory::getDate($article->modified);
		
		$author = JFactory::getUser($article->created_by);
		
		$lang = $this->_getLang($article);
		
		if ($lang) {
			$lang = "_$lang";
		}
		
		$doc->addField('id',  "$this->_option." . $article->id);
		$doc->addField('created', $created->toISO8601());
		$doc->addField('modified', $modified->toISO8601());
		$doc->addField("title$lang", $article->title);
		$doc->addField("content$lang", strip_tags($article->introtext . $article->fulltext));
		$doc->addField("metakeywords$lang", $article->metakey);
		$doc->addField("metadescription$lang", $article->metadesc);
		$doc->addField("author$lang", $author->get("name"));
		$doc->addField('option', $this->_option);
		
		foreach ($this->_getTags($article, array("h1")) as $item) {
			$doc->addField("tags_h1$lang", $item);
		}

		foreach ($this->_getTags($article, array("h2", "h3")) as $item) {
			$doc->addField("tags_h2_h3$lang", $item);
		}
		
		foreach ($this->_getTags($article, array("h4", "h5", "h6")) as $item) {
			$doc->addField("tags_h4_h5_h6$lang", $item);
		}		
		
		$section = new JTableSection(JFactory::getDBO());
		if ($section->load($article->sectionid)) {
			$doc->addField("section$lang", $section->title);
		} else {
			$doc->addField("section$lang", JText::_("Uncategorised"));
		}
		
		$category = new JTableCategory(JFactory::getDBO());
		if ($category->load($article->catid)) {
			$doc->addField("category$lang", $category->title);
		} else {
			$doc->addField("category$lang", JText::_("Uncategorised"));
		}
		
		return $doc;
	}
	
	private function _getDeleteQueryById($ids)
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
	
	private function _getLang(&$article)
	{
		$params = new JParameter($article->attribs);
		
		$lang = $params->get("language", JLanguageHelper::detectLanguage());
	
		// Language code must take the form xx-XX.
		if (count(explode("-", $lang)) < 2) {
			$lang = JLanguageHelper::detectLanguage();
		}

		return $lang;
	}
	
	private function _getTags(&$article, $tags)
	{	
		$dom = new DOMDocument();
		@$dom->loadHTML(strip_tags($article->introtext . $article->fulltext, implode(",", $tags)));
		$dom->preserveWhiteSpace = false;
	
		$text = array();		
		
		foreach ($tags as $tag) {
			$content = $dom->getElementsByTagname($tag);

		    foreach ($content as $item) {
	        	$text[] = $item->nodeValue;
		    }
		}

		return $text;		
	}
	
	public function onIndex()
	{
		$query = "SELECT a.id, a.created, a.modified, a.title, a.introtext, a.fulltext, a.created_by, a.sectionid, a.catid, a.metakey, a.metadesc, a.attribs " .
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