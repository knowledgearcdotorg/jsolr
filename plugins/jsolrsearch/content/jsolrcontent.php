<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * Format a content result to create a generic result.
 *
 * @version     $LastChangedBy$
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright   Copyright (C) 2011 inwardXpat Pty Ltd
 */

jimport('joomla.error.log');

require_once(JPATH_ROOT.DS."components".DS."com_content".DS."helpers".DS."route.php");

require_once JPATH_LIBRARIES."/joomla/database/table/section.php";
require_once JPATH_LIBRARIES."/joomla/database/table/category.php";
require_once JPATH_LIBRARIES."/joomla/database/table/content.php";

class plgJSolrSearchJSolrContent extends JPlugin 
{
	var $_plugin;
	
	var $_params;
		
	/**
	 * Constructor
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		
		$this->loadLanguage(null, JPATH_ADMINISTRATOR);
		
		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('jsolrsearch', 'jsolrcontent');
		$this->_params = new JParameter($this->_plugin->params);	
	}

	function onAddQF()
	{
		$qf = array();

		foreach ($this->_params->toArray() as $key=>$value) {
				if (strpos($key, "jsolr_boost") === 0) {
					$qfKey = str_replace("jsolr_boost_", "", $key);
					$qf[$qfKey] = floatval($value);
				}
		}

		return $qf;
	}
	
	function onAddHL()
	{
		$hl = array("title", "content", "metadescription");
		
		return $hl;
	}	
	
	function onFilterOptions()
	{		
		static $options = array();
		$options['com_content'] = JText::_("PLG_JSOLRSEARCH_JSOLRCONTENT_COM_CONTENT");
	
		return $options;
	}
	
	/**
	* Search method
	*
	* Format a com_content document and return a generic result item.
	* @param mixed $document
	* @param mixed $hl
	* @param int $hlFragSize
	* @param string $lang
	*/
	function onFormatResult($document, $hl, $hlFragSize, $lang) 
	{
		$result = null;
		
		$option = $this->onFilterOptions();
		$keys = array_keys($option);
		
		$title = "title$lang";
		$section = "section$lang";
		$category = "category$lang";

		if ($document->option == JArrayHelper::getValue($keys, 0)) {
			$result = new stdClass();
			
			$parts = explode(".", $document->id);
			$id = JArrayHelper::getValue($parts, 1, 0);

			$highlighting = JArrayHelper::getValue($hl, $document->id);
			
			if ($highlighting->offsetExists($title)) {
        		$hlTitle = JArrayHelper::getValue($highlighting->$title, 0);
			} else {
				$hlTitle = $document->$title;
			}
			
			$result->title = $hlTitle;
			$result->href = ContentHelperRoute::getArticleRoute($id);
			$result->text = $this->_getHlContent($document, $highlighting, $hlFragSize, $lang);
			$result->created = $document->created;
			$result->modified = $document->modified;
			$result->location = implode(", ", $document->$section) . "/" . implode(", ", $document->$category);
		}
		
		return $result;
	}
	
	function _getHlContent($solrDocument, $highlighting, $fragSize, $lang)
	{
		$hlContent = null;

		$metadescription = "metadescription$lang";
		$content = "content$lang";

		if ($this->_params->get("jsolr_use_hl_metadescription") == 1 && 
			$highlighting->offsetExists($metadescription)) {
			$hlContent = JArrayHelper::getValue($highlighting->$metadescription, 0);
		} else {
			if ($highlighting->offsetExists($content)) {
				foreach ($highlighting->$content as $item) {
					$hlContent .= "<span class=\"jsolr-separator\">...</span>".$item;	
				}

				$hlContent .= "<span class=\"jsolr-separator\">...</span>";
			}
		}
		
		return $hlContent;
	}
}