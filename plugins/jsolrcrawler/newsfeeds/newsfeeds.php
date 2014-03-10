<?php
/**
 * @package		JSolr.Plugin
 * @subpackage	Index
 * @copyright	Copyright (C) 2012-2014 KnowledgeARC Ltd. All rights reserved.
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
 * Hayden Young					<hayden@knowledgearc.com> 
 * 
 */
 
// no direct access
defined('_JEXEC') or die();

jimport('jsolr.index.crawler');

class plgJSolrCrawlerNewsfeeds extends JSolrIndexCrawler
{
	protected $context = 'com_newsfeeds.newsfeed';

	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$record)
	{
		$doc = new JSolrApacheSolrDocument();
		
		$created = JFactory::getDate($record->created);
		$modified = JFactory::getDate($record->modified);
		
		$lang = $this->getLanguage($record, false);

		$doc->addField('created', $created->format('Y-m-d\TH:i:s\Z', false));
		$doc->addField('modified', $modified->format('Y-m-d\TH:i:s\Z', false));		
		$doc->addField("title", $record->title);
		$doc->addField("title_$lang", $record->title);
		$doc->addField("link_$lang", $record->link);
		$doc->addField("access", $record->access);
		
		foreach (explode(',', $record->metakey) as $metakey) {
			$doc->addField("metakeywords_$lang", trim($metakey));
		}
		
		$doc->addField("metadescription_$lang", $record->metadesc);
		$doc->addField("author", $record->author);
		
		if ($record->catid) {
			$doc->addField("parent_id", $record->catid);
			$doc->addField("category_$lang", $record->category);
			$doc->addField("category_fc", $record->category); // facet
		}
		
		return $doc;
	}
	
	protected function buildQuery()
	{
		$db	= JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('a.id, a.catid, a.name AS title, a.alias, a.link AS link');
		$query->select('a.published AS state, a.ordering, a.created, a.params, a.access');
		$query->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
		$query->select('a.metakey, a.metadesc, a.metadata, a.language');
		$query->select('a.created_by, a.created_by_alias, a.modified, a.modified_by');
		
		$query->from('#__newsfeeds AS a');

		// Join over the users for the author.
		$query->select('ua.name AS author');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		
		$query->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');		
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');		

		$conditions = array();
		
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