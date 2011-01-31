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

require_once JPATH_LIBRARIES."/joomla/database/table/category.php";

class plgJSolrSearchJSolrNewsfeeds extends JPlugin 
{
	var $_plugin;
	
	var $_params;
	
	var $_option = 'com_newsfeeds';
		
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
		$this->_plugin = & JPluginHelper::getPlugin('jsolrsearch', 'jsolrnewsfeeds');
		$this->_params = new JParameter($this->_plugin->params);	
	}

	function onAddQF()
	{
		$qf = array();
		
		$qf["title"] = $this->_params->get("jsolr_title");
		
		return $qf;
	}
	
	function onAddHL()
	{
		$hl = array("title", "content");
		
		return $hl;
	}	
	
	function onFilterOptions()
	{		
		static $options = array();
		$options[$this->_option] = JText::_("PLG_JSOLRSEARCH_JSOLRNEWSFEEDS_COM_NEWSFEEDS");
	
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
			$result->href = JRoute::_("index.php?option=".$this->_option."&id=".$id);
			$result->text = $this->_getHlContent($document, $highlighting, $hlFragSize, $lang);
			$result->location = $document->$category;
			$result->created = null;
			$result->modified = null;
		}

		return $result;
	}
	
	function _getHlContent($solrDocument, $highlighting, $fragSize, $lang)
	{
		$hlContent = null;

		$content = "content$lang";

		if ($highlighting->offsetExists($content)) {
			foreach ($highlighting->$content as $item) {
				$hlContent .= $item;	
			}
		}
		
		return $hlContent;
	}
}