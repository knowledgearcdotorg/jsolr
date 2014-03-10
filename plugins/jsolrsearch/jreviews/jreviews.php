<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
* A plugin for searching JReviews listings.
 *
 * @package		JSolr.Plugin
 * @subpackage	Search
 * @copyright	Copyright (C) 2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolr Search JReviews plugin for Joomla!.

   The JSolr Search JReviews plugin for Joomla! is free software: you can 
   redistribute it and/or modify it under the terms of the GNU General Public 
   License as published by the Free Software Foundation, either version 3 of 
   the License, or (at your option) any later version.

   The JSolr Search JReviews plugin for Joomla! is distributed in the hope 
   that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr Search JReviews plugin for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<hayden@knowledgearc.com> 
 * 
 */

jimport('joomla.error.log');
jimport('joomla.cache.cache');

jimport('jsolr.search.search');

class plgJSolrSearchJReviews extends JSolrSearchSearch
{
	protected $context = 'com_jreviews.listing';

	public function __construct(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		
		$this->set('highlighting', array("title", "title_*", "body_*", "metadescription_*", "category_*"));
		
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		$query
			->select(array('name', 'title'))
			->from('#__jreviews_fields')
			->where("location='content'");
		
		$db->setQuery($query);
		$array = array();
		
		foreach ($db->loadObjectList() as $item) {
			$key = JString::strtolower(JStringNormalise::toVariable($item->name)).'_fc';
			$array[$key] = $item->title; 
		}
		
		$this->set('operators', $array);
	}

	public function onJSolrSearchURIGet($document)
	{
		if ($this->get('context') == $document->context) {
			require_once(JPATH_ROOT."/components/com_content/helpers/route.php");
			
			return ContentHelperRoute::getArticleRoute($document->id, $document->parent_id);
		}
		
		return null;
	}

	/**
	 * A convenience event handler to obtain the text related to an option's 
	 * value.
	 * 
	 * The event cache's the options for quicker lookup and to reduce load on 
	 * the database. Therefore, there may be some delay between new items 
	 * being added to JReviews and what is retrieved by this event. 
	 * 
	 * @param string $value The option's value.
	 * @return string The text related to the option's value.
	 */
	public function onJSolrSearchOptionLookup($value)
	{	
		$conf = JFactory::getConfig();
		$options = array(
				'defaultgroup' => 'plg_jsolrsearch_jreviews',
				'cachebase' => $conf->getValue('config.cache_path'),
				'lifetime' => $conf->getValue('config.cachetime') * 60, // minutes to seconds
				'language' => $conf->getValue('config.language'),
				'storage' => $conf->getValue('config.storage', 'file'));
		
		$cache = new JCache($options);
		$cache->setCaching(true);
		
		if (!$list = $cache->get('options', $options['defaultgroup'])) {	
			$database = JFactory::getDbo(); 
			
			$query = $database->getQuery(true);
			$query
				->select(array('text', 'value'))
				->from('#__jreviews_fieldoptions');
			
			$database->setQuery($query);
			
			$list = $database->loadObjectList();
			
			// cache these options so we don't need to keep loading from db.
			$cache->store($list, $options['defaultgroup']);
		}
		
		$found = false;
		$text = "";
		while (!$found && $item = current($list)) {
			if ($item->value == $value) {
				$found = true;
				$text = $item->text;
			}
			
			next($list);
		}
		
		return $text;
	}
}