<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * Format a K2 result to create a generic result.
 *
 * @version     $LastChangedBy: spauldingsmails $
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright   Copyright (C) 2011 Wijiti Pty Ltd
 */

jimport('joomla.error.log');

require_once JPATH_ROOT.DS."components".DS."com_k2".DS."helpers".DS."route.php";
require_once JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrsearch".DS."helpers".DS."plugin.php";

class plgJSolrSearchJSolrK2 extends JSolrSearchPlugin
{
	/**
	 * Constructor
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */

	public function __construct(&$subject, $config)
	{
		$config["option"] = "com_k2items,com_k2attachments";
		parent::__construct($subject, $config);
	}

	public function onAddQF()
	{
		$qf = array();

		foreach ($this->get("params")->toArray() as $key=>$value) {
			if (strpos($key, "jsolr_boost") === 0) {
				$qfKey = str_replace("jsolr_boost_", "", $key);
				$qf[$qfKey] = floatval($value);
			}
		}

		return $qf;
	}
	
	public function onAddHL()
	{
		$hl = array("title", "content", "metadescription");
		
		return $hl;
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
	public function onFormatResult($document, $hl, $hlFragSize, $lang) 
	{
		$result = null;
		
		$keys = explode(",", implode(",", array_keys($this->onFilterOptions())));

		$id = $document->id;
		$title = "title$lang";
		$category = "category$lang";
		$parentTitle = "item_title$lang";

		if (array_search($document->option, $keys) !== false) {
			$result = new stdClass();
			
			$parts = explode(".", $id);
			$itemId = JArrayHelper::getValue($parts, 1, 0);
			
			if (isset($hl->$id->$title)) {
        		$hlTitle = JArrayHelper::getValue($hl->$id->$title, 0);
			} else {
				$hlTitle = $document->$title;
			}
			
			$result->title = $hlTitle;
			$result->text = $this->_getHlContent($document, $hl, $hlFragSize, $lang);
			$result->created = $document->created;
			$result->modified = $document->modified;
			$result->location = $document->$category;
			$result->option = $document->option;
			
			if ($document->option == "com_k2attachments") {
				$result->contentType = $document->content_type;
				$result->parentTitle = $document->$parentTitle;
				
				$parts = explode(".", $document->item_id);
				$parentId = JArrayHelper::getValue($parts, 1, 0);
			
				$result->parentId = $parentId;
				$result->parentHref = JRoute::_(K2HelperRoute::getItemRoute($result->parentId));
				$result->href = JURI::base()."media/k2/attachments/".$document->file_name;
			} else {
				$result->href = JRoute::_(K2HelperRoute::getItemRoute($itemId));
			}
		}
		
		return $result;
	}
	
	private function _getHlContent($document, $highlighting, $fragSize, $lang)
	{
		$id = $document->id;
		$hlContent = null;

		$metadescription = "metadescription$lang";
		$content = "content$lang";

		if ($this->get("params")->get("jsolr_use_hl_metadescription") == 1 && 
			isset($highlighting->$id->$metadescription)) {
			$hlContent = JArrayHelper::getValue($highlighting->$id->$metadescription, 0);
		} else {
			if (isset($highlighting->$id->$content)) {
				foreach ($highlighting->$id->$content as $item) {
					$hlContent .= "<span class=\"jsolr-separator\">...</span>".$item;	
				}

				$hlContent .= "<span class=\"jsolr-separator\">...</span>";
			}
		}
		
		return $hlContent;
	}
}