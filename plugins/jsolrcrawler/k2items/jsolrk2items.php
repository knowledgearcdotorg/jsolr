<?php
/**
 * @author		$LastChangedBy$
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr K2 Items Index plugin for Joomla!.

   The JSolr K2 Items Index plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr K2 Items Index plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr K2 Items Index plugin for Joomla!.  If not, see 
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

require_once JPATH_ROOT.DS."components".DS."com_k2".DS."models".DS."itemlist.php";
require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."helpers".DS."plugin.php");

class plgJSolrCrawlerJSolrK2Items extends JSolrCrawlerPlugin
{
	var $_plugin;
	
	var $_params;
	
	var $_client;
	
	/**
	 * Constructor
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct("k2", $subject, $config);
	}

	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$record)
	{
		$doc = new Apache_Solr_Document();
		
		$created = JFactory::getDate($record->created);
		$modified = JFactory::getDate($record->modified);
		
		$author = JFactory::getUser($record->created_by);
		
		$lang = $this->getLang($record);
		
		if ($lang) {
			$lang = "_".str_replace("-", "_", $lang);
		}
		
		$doc->addField('id',  "$this->_option." . $record->id);
		$doc->addField('created', $created->toISO8601());
		$doc->addField('modified', $modified->toISO8601());
		$doc->addField("title", $record->title);		
		$doc->addField("title$lang", $record->title);
		$doc->addField("content", strip_tags($record->introtext . " " . $record->fulltext));
		$doc->addField("content$lang", strip_tags($record->introtext . " " . $record->fulltext));
		$doc->addField("metakeywords", $record->metakey);
		$doc->addField("metakeywords$lang", $record->metakey);
		$doc->addField("metadescription", $record->metadesc);
		$doc->addField("metadescription$lang", $record->metadesc);
		$doc->addField("author", $author->get("name"));		
		$doc->addField("author$lang", $author->get("name"));
		$doc->addField('option', $this->_option);
		
		foreach ($this->_getTags($record, array("h1")) as $item) {
			$doc->addField("tags_h1", $item);
			$doc->addField("tags_h1$lang", $item);
		}

		foreach ($this->_getTags($record, array("h2", "h3")) as $item) {
			$doc->addField("tags_h2_h3", $item);
			$doc->addField("tags_h2_h3$lang", $item);
		}
		
		foreach ($this->_getTags($record, array("h4", "h5", "h6")) as $item) {
			$doc->addField("tags_h4_h5_h6", $item);
			$doc->addField("tags_h4_h5_h6$lang", $item);
		}
		
		$category = $this->getCategory($record->catid);
		
		if (isset($category->id)) {
			$doc->addField("category", $category->name);
			$doc->addField("category$lang", $category->name);
		}
		
		return $doc;
	}
	
	private function getCategory($cid)
	{
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE id=".intval($cid);
		$db->setQuery($query, 0, 1);
		return $db->loadObject();
	}
	
	protected function getLang(&$item)
	{
		return JLanguageHelper::detectLanguage();
	}
	
	private function _getTags(&$article, $tags)
	{	
		$dom = new DOMDocument();
		@$dom->loadHTML(strip_tags($article->introtext . " " . $article->fulltext, implode(",", $tags)));
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

	protected function buildQuery($rules)
	{
		$array = $this->parseRules($rules);

		$database = JFactory::getDBO();
		
		$query = "SELECT a.id, a.created, a.modified, a.title, a.introtext, a.fulltext, a.created_by, a.catid, a.metakey, a.metadesc " .
				 "FROM #__k2_items AS a WHERE a.published = 1 AND a.checked_out = 0 AND a.trash = 0"; 
		
		if (JArrayHelper::getValue($array, "item", null)) {
			$query .= " AND a.id NOT IN (" . $database->getEscaped(JArrayHelper::getValue($array, "item", null)) . ")";
		}

		if (JArrayHelper::getValue($array, "category", null)) {
			$query .= " AND a.catid NOT IN (" . $database->getEscaped(JArrayHelper::getValue($array, "category", null)) . ")";
		}

		$query .= ";";
		
		return $query;
	}
}