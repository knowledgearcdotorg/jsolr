<?php
/**
 * @package	JSolr
 * @subpackage Index
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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

require_once JPATH_LIBRARIES."/joomla/database/table/category.php";
require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."helpers".DS."plugin.php");

class plgJSolrCrawlerContent extends JSolrCrawlerPlugin
{	
	protected $extension = 'com_content';
	
	protected $view = 'article';
	
	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$record)
	{	
		$doc = new Apache_Solr_Document();
		
		$created = JFactory::getDate($record->created);
		$modified = JFactory::getDate($record->modified);
		
		$lang = $this->getLanguage($record, false);

		$doc->addField('created', $created->format('Y-m-d\TH:i:s\Z', false));
		$doc->addField('modified', $modified->format('Y-m-d\TH:i:s\Z', false));
		$doc->addField("title", $record->title);		
		$doc->addField("title_$lang", $record->title);

		$record->summary = self::prepareContent($record->summary, $record->params);
		$record->body = self::prepareContent($record->body, $record->params);

		$doc->addField("body_$lang", strip_tags($record->summary));	
		$doc->addField("body_$lang", strip_tags($record->body));
		
		foreach (explode(',', $record->metakey) as $metakey) {
			$doc->addField("metakeywords_$lang", trim($metakey));
		}
		
		$doc->addField("metadescription_$lang", $record->metadesc);
		$doc->addField("author", $record->author);
		
		foreach ($this->_getTags($record, array("<h1>")) as $item) {
			$doc->addField("tags_h1_$lang", $item);
		}

		foreach ($this->_getTags($record, array("<h2>", "<h3>")) as $item) {
			$doc->addField("tags_h2_h3_$lang", $item);
		}
		
		foreach ($this->_getTags($record, array("<h4>", "<h5>", "<h6>")) as $item) {
			$doc->addField("tags_h4_h5_h6_$lang", $item);
		}
		
		$doc->addField("hits_i", (int)$record->hits);
		
		if ($record->catid) {
			$doc->addField("parent_id", $record->catid);
			$doc->addField("category_$lang", $record->category);
		}
		
		return $doc;
	}
	
	private function _getTags(&$article, $tags)
	{		
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		@$dom->loadHTML(strip_tags($article->summary . " " . $article->body, implode("", $tags)));
	
		$text = array();		

		foreach ($tags as $tag) {
			$content = $dom->getElementsByTagname(str_replace(array('<','>'), '', $tag));

		    foreach ($content as $item) {
	        	$text[] = $item->nodeValue;
		    }
		}

		return $text;
	}
	
	// @todo This method is adapted from the com_finder preparecontent method 
	// but it doesn't really do anything (loadmodule and loadposition still 
	// appear in the content even though they should be parsed out).
	// Currently, it is assumed that this method handles other content manipulation 
	// such as BBCode (used by certain 3rd party plugins to add complex javascript, 
	// css and html to an article.
	// Instead, this method should do more to clear out the markup including module 
	// loading and other 3rd party content manipulation plugins.
	public static function prepareContent($text, $params = null)
    {
		static $loaded;
		
		// Get the dispatcher.
		$dispatcher = JDispatcher::getInstance();

		// Load the content plugins if necessary.
		if (empty($loaded)) {
			JPluginHelper::importPlugin('content');
			$loaded = true;
		}

		// Instantiate the parameter object if necessary.
		if (!($params instanceof JRegistry)) {
			$registry = new JRegistry;
			$registry->loadString($params);
			$params = $registry;
		}

		// Create a mock content object.
		$content = JTable::getInstance('Content');
		$content->text = $text;

		// Fire the onContentPrepare event with the com_finder context to avoid 
		// errors with loadmodule/loadposition plugins.
		$dispatcher->trigger('onContentPrepare', array('com_finder.indexer', &$content, &$params, 0));
 
		return $content->text;
	}

	protected function buildQuery()
	{
		// Create a new query object.
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();

		// Select the required fields from the table.
		$query->select('a.id, a.title, a.alias, a.introtext AS summary, a.fulltext AS body');
		$query->select('a.state, a.catid, a.created, a.created_by, a.hits');
		$query->select('a.created_by_alias, a.modified, a.modified_by, a.attribs AS params');
		$query->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.version, a.ordering');
		$query->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
		
		$query->from('#__content AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the categories.
		$query->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author.
		$query->select('ua.name AS author');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		$conditions = array();

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$conditions[] = 'a.access IN ('.$groups.')';
		}

		$categories = $this->params->get('categories');

		if (is_array($categories)) {
			if (JArrayHelper::getValue($categories, 0) != 0) {
				JArrayHelper::toInteger($categories);
				$categories = implode(',', $categories);
				$conditions[] = 'a.catid IN ('.$categories.')';
			}
		}

		if (count($conditions)) {
			$query->where($conditions);
		}

		return $query;
	}
}