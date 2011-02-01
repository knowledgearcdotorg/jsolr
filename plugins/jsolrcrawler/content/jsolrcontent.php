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

require_once JPATH_LIBRARIES."/joomla/database/table/section.php";
require_once JPATH_LIBRARIES."/joomla/database/table/category.php";
require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolr".DS."helpers".DS."plugin.php");

class plgJSolrCrawlerJSolrContent extends JSolrCrawlerPlugin
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
		parent::__construct("content", $subject, $config);
	}

	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$record)
	{
		$doc = new SolrInputDocument();
		
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
		
		$section = new JTableSection(JFactory::getDBO());
		if ($section->load($record->sectionid)) {
			$doc->addField("section", $section->title);
			$doc->addField("section$lang", $section->title);
		} else {
			$doc->addField("section", JText::_("Uncategorised"));
			$doc->addField("section$lang", JText::_("Uncategorised"));
		}
		
		$category = new JTableCategory(JFactory::getDBO());
		if ($category->load($record->catid)) {
			$doc->addField("category", $category->title);
			$doc->addField("category$lang", $category->title);
		} else {
			$doc->addField("category", JText::_("Uncategorised"));
			$doc->addField("category$lang", JText::_("Uncategorised"));
		}
		
		return $doc;
	}
	
	protected function getLang(&$item)
	{
		$params = new JParameter($item->attribs);
		
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
		
		$query = "SELECT a.id, a.created, a.modified, a.title, a.introtext, a.fulltext, a.created_by, a.sectionid, a.catid, a.metakey, a.metadesc, a.attribs " .
				 "FROM #__content AS a WHERE a.state = 1 AND a.checked_out = 0"; 

		if (JArrayHelper::getValue($array, "article", null)) {
			$query .= " AND a.id NOT IN (" . $database->Quote(JArrayHelper::getValue($array, "article", null)) . ")";
		}

		if (JArrayHelper::getValue($array, "section", null)) {
			$query .= " AND a.sectionid NOT IN (" . $database->Quote(JArrayHelper::getValue($array, "section", null)) . ")";
		}

		if (JArrayHelper::getValue($array, "category", null)) {
			$query .= " AND a.catid NOT IN (" . $database->Quote(JArrayHelper::getValue($array, "category", null)) . ")";
		}

		$query .= ";";
		
		return $query;
	}
}