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
		$hl = array("title", "link");
		
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

		$id = $document->id;
		$title = "title$lang";
		$category = "category$lang";

		if ($document->option == JArrayHelper::getValue($keys, 0)) {
			$result = new stdClass();
			
			$parts = explode(".", $id);
			$newsFeedId = JArrayHelper::getValue($parts, 1, 0);

			if (isset($hl->$id->$title)) {
        		$hlTitle = JArrayHelper::getValue($hl->$id->$title, 0);
			} else {
				$hlTitle = $document->$title;
			}
			
			$result->title = $hlTitle;
			$result->href = JRoute::_("index.php?option=".$this->_option."&view=newsfeed&id=".$newsFeedId);
			$result->text = $this->_getHlContent($document, $hl, $hlFragSize, $lang);
			$result->location = $document->$category;
			$result->option = $document->option;
			$result->created = null;
			$result->modified = null;
		}

		return $result;
	}
	
	function _getHlContent($document, $highlighting, $fragSize, $lang)
	{
		$id = $document->id;
		$hlContent = null;

		$link = "link$lang";

		if (isset($highlighting->$id->$link)) {
			foreach ($highlighting->$id->$link as $item) {
				$hlContent .= $item;
			}
		}
		
		return $hlContent;
	}
}