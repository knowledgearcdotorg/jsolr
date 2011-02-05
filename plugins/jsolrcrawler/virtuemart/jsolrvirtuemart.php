<?php
/**
 * @author		$LastChangedBy: spauldingsmails $
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr Virtuemart Index plugin for Joomla!.

   The JSolr Virtuemart Index plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr Virtuemart Index plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr Virtuemart Index plugin for Joomla!.  If not, see 
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

jimport("joomla.filesystem.file");

require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolr".DS."helpers".DS."plugin.php");

class plgJSolrCrawlerJSolrVirtuemart extends JSolrCrawlerPlugin
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
		parent::__construct("virtuemart", $subject, $config);
	}

	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$record)
	{
		$doc = new SolrInputDocument();
		
		$created = JFactory::getDate($record->cdate);
		$modified = JFactory::getDate($record->mdate);
		
		$lang = $this->getLang($record);
		
		if ($lang) {
			$lang = "_".str_replace("-", "_", $lang);
		}
		
		$doc->addField('id',  "$this->_option." . $record->id);
		$doc->addField('created', $created->toISO8601());
		$doc->addField('modified', $modified->toISO8601());
		$doc->addField("sku", $record->product_sku);
		$doc->addField("title", $record->product_name);
		$doc->addField("title$lang", $record->product_name);
		$doc->addField("content", strip_tags($record->product_s_desc." ".$record->product_desc));
		$doc->addField("content$lang", strip_tags($record->product_s_desc." ".$record->product_desc));
		$doc->addField('option', $this->_option);
		$doc->addField('currency', $record->product_currency);
		$doc->addField('price', number_format($record->product_price, 2, ".", ""));
		
		foreach ($this->_getTags($record, array("h1")) as $item) {
			$doc->addField("tags_h1", $item);
			$doc->addField("tags_h1$lang", $item);
		}

		foreach ($this->_getTags($record, array("h2", "h3")) as $item) {
			$doc->addField("tags_h2_h3", $item);
			$doc->addField("tags_h2_h3$lang", $item);
		}
		
		foreach ($this->_getTags($record, array("h4", "h5", "h6")) as $item) {
			$doc->addField("tags_h4_h5_h6", $item);
			$doc->addField("tags_h4_h5_h6$lang", $item);
		}

		foreach ($this->_getCategories($record) as $item) {		
			$doc->addField("category", $item);
			$doc->addField("category$lang", $item);
		}
		
		return $doc;
	}
	
	private function _getCategories($record)
	{
		$query = "SELECT category_name " . 
				 "FROM #__vm_product_category_xref AS a " . 
				 "INNER JOIN #__vm_category AS b ON (a.category_id = b.category_id) " . 
				 "WHERE a.product_id = " . intval($record->id);
		
		$database = JFactory::getDBO();
		$database->setQuery($query);
		
		return $database->loadResultArray();
	}
	
	private function _getTags(&$record, $tags)
	{	
		$dom = new DOMDocument();
		@$dom->loadHTML(strip_tags($record->product_s_desc . $record->product_desc, implode(",", $tags)));
		$dom->preserveWhiteSpace = false;
	
		$text = array();		
		
		foreach ($tags as $tag) {
			$content = $dom->getElementsByTagname($tag);

		    foreach ($content as $item) {
	        	$text[] = $item->nodeValue;
		    }
		}

		return $text;
	}

	protected function buildQuery($rules)
	{
		$array = $this->parseRules($rules);

		$database = JFactory::getDBO();
		
		$query = "SELECT a.product_id AS id, a.cdate, a.mdate, a.product_sku, a.product_name, a.product_s_desc, a.product_desc, b.product_price, b.product_currency " .
				 "FROM #__vm_product AS a " . 
				 "INNER JOIN #__vm_product_price AS b ON (a.product_id = b.product_id)"; 

		if (JArrayHelper::getValue($array, "product", null)) {
			$query .= " AND a.product_id NOT IN (" . $database->getEscaped(JArrayHelper::getValue($array, "product", null)) . ")";
		}
		
		$query .= ";";

		return $query;
	}
	
	public function onIndex($rules)
	{
		if (!is_dir(JPATH_ROOT.DS."components".DS."com_virtuemart")) {
			throw new Exception("Virtuemart not installed. Cannot index virtuemart data.");
			return;
		}
		
		return parent::onIndex($rules);
	}
}