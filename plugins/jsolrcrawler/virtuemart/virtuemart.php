<?php
/**
 * @paackage	JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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

require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."helpers".DS."plugin.php");

if (!class_exists('VmConfig')) require(JPATH_ADMINISTRATOR.DS.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');

class plgJSolrCrawlerVirtuemart extends JSolrCrawlerPlugin
{
	protected $extension = 'com_virtuemart';
	
	protected $view = 'product';
	
	protected function getItems()
	{
		$languages = VmConfig::get('active_languages', array());
		
		$items = array(); 
		
		foreach ($languages as $language) {
			$database = JFactory::getDBO();
			$database->setQuery($this->buildQuery($language));

			$items = array_merge($items, $database->loadObjectList());
		}

		return $items;
	}
	
	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$record)
	{
		$doc = new Apache_Solr_Document();
		
		$created = JFactory::getDate($record->created_on);
		$modified = JFactory::getDate($record->modified_on);

		$lang = $this->getLanguage($record, false);

		$doc->addField('created', $created->format('Y-m-d\TH:i:s\Z', false));
		$doc->addField('modified', $modified->format('Y-m-d\TH:i:s\Z', false));
		$doc->addField("sku_s", $record->product_sku);
		$doc->addField("title", $record->product_name);
		$doc->addField("title_$lang", $record->product_name);
		$doc->addField("summary_$lang", strip_tags($record->summary));
		$doc->addField("body_$lang", strip_tags($record->body));
		$doc->addField('currency_s', $record->currency);

		$doc->addField('price_c', number_format($record->product_price, 2, ".", "").','.$record->currency);		
		
		// store price in float as well for range faceting.
		$doc->addField('price_f', number_format($record->product_price, 2, ".", ""));

		foreach ($this->_getThumbs($record->id, $record->language) as $item) {
			$doc->addField('thumbnail_s_multi', $item->file_url_thumb);
		}

		foreach ($this->_getTags($record, array("<h1>")) as $item) {
			$doc->addField("tags_h1_$lang", $item);
		}

		foreach ($this->_getTags($record, array("<h2>", "<h3>")) as $item) {
			$doc->addField("tags_h2_h3_$lang", $item);
		}
	
		foreach ($this->_getTags($record, array("<h4>", "<h5>", "<h6>")) as $item) {
			$doc->addField("tags_h4_h5_h6_$lang", $item);
		}
	
		foreach ($this->_getCategories($record->id, $record->language) as $item) {
			$doc->addField("category_$lang", $item->category_name);
		}

		return $doc;
	}
	
	private function _getTags(&$record, $tags)
	{		
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		@$dom->loadHTML(strip_tags($record->summary . " " . $record->body, implode("", $tags)));
	
		$text = array();		

		foreach ($tags as $tag) {
			$content = $dom->getElementsByTagname(str_replace(array('<','>'), '', $tag));

		    foreach ($content as $item) {
	        	$text[] = $item->nodeValue;
		    }
		}

		return $text;
	}

	protected function buildQuery($language = '')
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
	
		$query->select('a.virtuemart_product_id AS id, a.product_sku');
		$query->select('a.product_params, a.hits, a.created_on'); 
		$query->select('a.modified_on, a.created_by');
		$query->select('\''.$language .'\' AS language');
		$query->from('#__virtuemart_products AS a');
		
		$query->select('b.product_price');
		$query->innerJoin('#__virtuemart_product_prices AS b ON (a.virtuemart_product_id = b.virtuemart_product_id)');

		$query->select('c.product_name, c.product_desc as summary');
		$query->select('c.product_desc as body, c.metakey, c.metadesc');
		$query->innerJoin('#__virtuemart_products_'.str_replace('-', '_', strtolower($language)).' AS c ON (a.virtuemart_product_id = c.virtuemart_product_id)');

		$query->leftJoin('#__virtuemart_product_categories AS d ON (a.virtuemart_product_id = d.virtuemart_product_id)');
		
		$conditions = array();
		
		$categories = $this->params->get('categories');
		
		if (is_array($categories)) {
			if (JArrayHelper::getValue($categories, 0) != 0) {
				JArrayHelper::toInteger($categories);
				$categories = implode(',', $categories);
				$conditions[] = 'd.virtuemart_category_id IN ('.$categories.')';
			}
		}

		if (count($conditions)) {
			$query->where($conditions);
		}

		$query->select('e.currency_code_3 AS currency');
		$query->innerJoin('#__virtuemart_currencies AS e ON (b.product_currency = e.virtuemart_currency_id)');
				
		return $query;
	}
	
	private function _buildCategoriesQuery($productId, $language)
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
	
		$query->select('a.virtuemart_category_id AS id');		
		$query->from('#__virtuemart_product_categories AS a');

		$query->select('b.category_name, b.category_description');
		$query->innerJoin('#__virtuemart_categories_'.str_replace('-', '_', strtolower($language)).' AS b ON ( a.virtuemart_category_id = b.virtuemart_category_id )');
		
		$query->where('a.virtuemart_product_id = '.$productId);
		
		return $query;
	}
	
	private function _getCategories($productId, $language)
	{
		$database = JFactory::getDBO();
		$database->setQuery($this->_buildCategoriesQuery($productId, $language));

		return $database->loadObjectList();		
	}
	
	private function _buildThumbsQuery($productId, $language)
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
	
		$query->from('#__virtuemart_product_medias AS a');

		$query->select('b.file_url_thumb');
		$query->innerJoin('#__virtuemart_medias AS b ON ( a.virtuemart_media_id = b.virtuemart_media_id )');
		
		$query->where('a.virtuemart_product_id = '.$productId);
		
		return $query;
	}
	
	private function _getThumbs($productId, $language)
	{
		$database = JFactory::getDBO();
		$database->setQuery($this->_buildThumbsQuery($productId, $language));

		return $database->loadObjectList();		
	}
	
	public function onIndex()
	{	
		if (!JComponentHelper::isEnabled('com_virtuemart')) {
			throw new Exception("Virtuemart not installed. Cannot index virtuemart data.");
			return;
		}
		
		return parent::onIndex();
	}
}