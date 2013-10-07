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
	const FACET_DELIMITER = '|||';
	
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

	/**
	 * Converts a facet into a value which can be used for case-insensitive lookup. 
	 * 
	 * @param string $facet The raw facet.
	 * @param string $delimiter The delimiter which should be used to store 
	 * the facet in two ways; case-insensitive + case-sensitive.
	 * 
	 * @return string A facet which can be used for case-insensitive lookup.
	 */
	public static function toCaseInsensitiveFacet($facet, $delimiter = self::FACET_DELIMITER)
	{
		return JString::strtolower($facet).$delimiter.$facet;
	}
	
	/**
	 * Parses a case-insensitive facet, converting it into its various parts.
	 * 
	 * This method will return in an array with index 0 representing the lower 
	 * case version of the facet, and index 1 representing the raw facet value 
	 * (with case if applicable).
	 * 
	 * E.g. if the facet is test|||Test, the parsed output will be:
	 * 
	 * $result[0] = 'test';
	 * $result[1] = 'Test';
	 * 
	 * @param unknown_type $facet The facet to parse.
	 * @param unknown_type $delimiter The delimiter to split the facet into 
	 * its parts.
	 * 
	 * @return array An array with two values, the first being the 
	 * case-insensitive or lower case value and the second being the original, 
	 * raw value. 
	 */
	public static function parseCaseInsensitiveFacet($facet, $delimiter = self::FACET_DELIMITER)
	{
		return explode($delimiter, $facet);
	}
	
	/**
	 * Get the original facet value.
	 * 
	 * @param string $facet The facet value to get the original facet value 
	 * from. 
	 * @param string $delimiter The delimiter to split the facet on.
	 * 
	 * @return string The original facet value.
	 */
	public static function getOriginalFacet($facet, $delimiter = self::FACET_DELIMITER)
	{
		$facet = self::parseCaseInsensitiveFacet($facet, $delimiter);
		
		if (is_array($facet)) {
			if (count($facet) == 2) {
				return JArrayHelper::getValue($facet, 1);
			} else { 
				return JArrayHelper::getValue($facet, 0);
			}
		} else {
			return $facet;
		}
	} 
}