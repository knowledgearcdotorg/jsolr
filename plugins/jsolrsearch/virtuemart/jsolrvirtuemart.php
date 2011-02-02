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

class plgJSolrSearchJSolrVirtuemart extends JPlugin 
{
	var $_plugin;
	
	var $_params;
	
	var $_option = 'com_virtuemart';
		
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
		$this->_plugin = & JPluginHelper::getPlugin('jsolrsearch', 'jsolrvirtuemart');
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
		$hl = array("title", "content");
		
		return $hl;
	}	
	
	function onFilterOptions()
	{		
		static $options = array();
		$options[$this->_option] = JText::_("PLG_JSOLRSEARCH_JSOLRVIRTUEMART_COM_VIRTUEMART");
	
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
			$result->location = implode(", ", $document->$category);
			$result->created = null;
			$result->modified = null;			
			$result->attribs["price"] = $document->price;
			$result->attribs["currency"] = $document->currency;
			$result->attribs["thumbnail"] = $this->_getThumbnail($id);
		}

		return $result;
	}
	
	private function _getHlContent($solrDocument, $highlighting, $fragSize, $lang)
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
	
	private function _getThumbnail($id)
	{
		$query = "SELECT product_thumb_image FROM #__vm_product WHERE product_id = " . $id . ";";
		
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$thumb = $database->loadResult();

		$url = "";
		
		if ($thumb) {
			$url = JURI::base()."components/com_virtuemart/shop_image/product/".$thumb;
		}
		
		return $url;
	}
}