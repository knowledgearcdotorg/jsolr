<?php
/**
 * @author		$LastChangedBy$
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
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

require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolr".DS."helpers".DS."plugin.php");

class plgJSolrCrawlerJSolrNewsfeeds extends JSolrCrawlerPlugin 
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
		parent::__construct("newsfeeds", $subject, $config);
	}

	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$item)
	{
		$doc = new SolrInputDocument();
		
		$lang = $this->getLang($item);
		
		if ($lang) {
			$lang = "_".str_replace("-", "_", $lang);
		}
		
		$doc->addField('id',  "$this->_option." . $item->id);
		$doc->addField("title", $item->name);
		$doc->addField("title$lang", $item->name);
		$doc->addField("link", $item->link);
		$doc->addField("link$lang", $item->link);
		$doc->addField('option', $this->_option);
		
		$category = new JTableCategory(JFactory::getDBO());
		
		if ($category->load($item->catid)) {
			$doc->addField("category", $category->title);
			$doc->addField("category$lang", $category->title);
		}
		
		return $doc;
	}
	
	protected function buildQuery($rules)
	{
		$array = $this->parseRules($rules);

		$database = JFactory::getDBO();
		
		$query = "SELECT id, name, link, catid " .
				 "FROM #__newsfeeds AS a WHERE published = 1 AND a.checked_out = 0"; 

		if (JArrayHelper::getValue($array, "newsfeed", null)) {
			$query .= " AND a.id NOT IN (" . $database->Quote(JArrayHelper::getValue($array, "newsfeed", null)) . ")";
		}

		if (JArrayHelper::getValue($array, "category", null)) {
			$query .= " AND a.catid NOT IN (" . $database->Quote(JArrayHelper::getValue($array, "category", null)) . ")";
		}

		$query .= ";";

		return $query;
	}
}