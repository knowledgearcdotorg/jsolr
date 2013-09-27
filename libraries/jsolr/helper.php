<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2013 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr library for Joomla!.

   The JSolr library for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr library for Joomla! is distributed in the hope that it will be 
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

class JSolrHelper extends JObject
{
	public static function highlight($highlighting, $field, $default = null)
	{
		$array = array();

		if (isset($highlighting->$field)) {
			foreach ($highlighting->$field as $item) {
				$array[] = $item;
			}
				
			return implode("...", $array);
		}
		
		return $default;
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
			JPluginHelper::getPlugin('content');
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
	
	public static function getTags(&$article, $tags)
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
}