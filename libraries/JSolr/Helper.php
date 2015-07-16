<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JSolr;

use \JArrayHelper as JArrayHelper; // @TODO: Eventually replace with \Joomla\Utilities\ArrayHelper
use \JTable as JTable;
use \JFactory as JFactory;
use \JString as JString;

class Helper extends \JObject
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
		if (!($params instanceof \Joomla\Registry\Registry)) {
			$registry = new \Joomla\Registry\Registry;
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

	public static function localize($field)
	{
		$code = JFactory::getApplication()->input->getString('lr', null);

		if (!$code) {
			$code = JFactory::getLanguage()->getTag();
		}

		$parts = explode('-', $code);
		$code = JArrayHelper::getValue($parts, 0);

		return str_replace("*", $code, $field);
	}
}