<?php
/**
 * @author		$LastChangedBy$
 * @paackage	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr K2 Items Index plugin for Joomla!.

   The JSolr K2 Items Index plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr K2 Items Index plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr K2 Items Index plugin for Joomla!.  If not, see 
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

require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."helpers".DS."plugin.php");

class plgJSolrCrawlerJSolrK2Attachments extends JSolrCrawlerPlugin
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
	public function __construct(&$subject, $config = array())
	{
		parent::__construct("k2attachments", $subject, $config);
	}

	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$record)
	{
		$extractedDoc = $this->_extract(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments'.DS.$record->filename);
		
		$doc = new Apache_Solr_Document();
		
		$created = JFactory::getDate($extractedDoc->metadata->created);
		$modified = JFactory::getDate($extractedDoc->metadata->modified);
		
		$lang = $this->getLang($record);
		
		if ($lang) {
			$lang = "_".str_replace("-", "_", $lang);
		}
		
		$title = "";
		
		if ($record->title) {
			$title = $record->title;
		} else if ($extractedDoc->metadata->title) {
			$title = JArrayHelper::getValue($metadata->title, 0);
		} else {
			$title = JArrayHelper::getValue($extractedDoc->metadata->fileName, 0);
		}
		
		$doc->addField('id', $this->getOption() . "." . $record->id);
		$doc->addField('created', $created->toISO8601());
		$doc->addField('modified', $modified->toISO8601());
		$doc->addField("title", $title);		
		$doc->addField("title$lang", $title);
		$doc->addField("content", trim(strip_tags($extractedDoc->content)));
		$doc->addField("content$lang", trim(strip_tags($extractedDoc->content)));
		$doc->addField("metakeywords", $extractedDoc->metadata->metakeywords);
		$doc->addField("metakeywords$lang", $extractedDoc->metadata->metakeywords);
		$doc->addField("metadescription", $extractedDoc->metadata->metadescription);
		$doc->addField("metadescription$lang", $extractedDoc->metadata->metadescription);
		$doc->addField("author", $extractedDoc->metadata->author);
		$doc->addField("author$lang", $extractedDoc->metadata->author);
		$doc->addField("file_name", $extractedDoc->metadata->fileName);
		$doc->addField("content_type", $extractedDoc->metadata->type);
		$doc->addField("content_size", $extractedDoc->metadata->size);
		$doc->addField('item_id', "k2items." . $record->itemID);
		$doc->addField("item_title", $record->itemTitle);
		$doc->addField("item_title$lang", $record->itemTitle);
		$doc->addField('option', $this->getOption());
		
		foreach ($this->_getTags($content, array("h1")) as $item) {
			$doc->addField("tags_h1", $item);
			$doc->addField("tags_h1$lang", $item);
		}

		foreach ($this->_getTags($content, array("h2", "h3")) as $item) {
			$doc->addField("tags_h2_h3", $item);
			$doc->addField("tags_h2_h3$lang", $item);
		}
		
		foreach ($this->_getTags($content, array("h4", "h5", "h6")) as $item) {
			$doc->addField("tags_h4_h5_h6", $item);
			$doc->addField("tags_h4_h5_h6$lang", $item);
		}
		
		$doc->addField("category", $record->categoryTitle);
		$doc->addField("category$lang", $record->categoryTitle);

		return $doc;
	}
	
	protected function getLang(&$item)
	{
		return JLanguageHelper::detectLanguage();
	}
	
	private function _getTags(&$article, $tags)
	{	
		$dom = new DOMDocument();
		@$dom->loadHTML(strip_tags($article->introtext . " " . $article->fulltext, implode(",", $tags)));
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
	
	private function _extract($path)
	{
		require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."configuration.php");
		$configuration = new JSolrIndexConfig();
		
		$document = new stdClass();
		
		switch ($configuration->extractor) {
			case "local":
				ob_start();
				passthru("java -jar ".$configuration->tika_app_path." ".$path);
				$result = ob_get_contents();
				ob_end_clean();
				
				$document->content = $result;
				
				ob_start();
				passthru("java -jar ".$configuration->tika_app_path." --json ".$path);
				$result = ob_get_contents();
				ob_end_clean();

				$response = json_decode($result, true);
				
				$document->metadata = $this->_extractMetadata($response);

				break;
				
			case "remote":
				$url = $configuration->tika_host;
				
				if ($configuration->tika_username && $configuration->tika_password) {
					$url = $configuration->tika_username . ":" . $configuration->tika_password . "@" . $url;
				}
				
				$client = new Apache_Solr_Service($url, $configuration->tika_port, $configuration->tika_path);
		
				$extraction = $client->extract($path, array("extractOnly"=>"true"));
				
				$response = json_decode($extraction->getRawResponse(), true);

				$document->content = $response[""];
				$document->metadata = $this->_extractMetadata($response["null_metadata"]);
				
				break;
				
			case "solr":
				$client = $this->getClient();
		
				$extraction = $client->extract($path, array("extractOnly"=>"true"));
				
				$response = json_decode($extraction->getRawResponse(), true);
		
				$document->content = $response[""];
				$document->metadata = $this->_extractMetadata($response["null_metadata"]);
				
				break;
				
			default:
				
				break;
		}
		
		return $document;
	}
	
	private function _extractMetadata($array) 
	{
		$metadata = new stdClass();

		$metadata->type = $this->_extractMetadataItem(JArrayHelper::getValue($array, "Content-Type", null));
		
		if (JArrayHelper::getValue($array, "Content-Length", null)) {
			$metadata->size = intval($this->_extractMetadataItem(JArrayHelper::getValue($array, "Content-Length", null)));	
		} else if (JArrayHelper::getValue($array, "stream_size", null)) {
			$metadata->size = intval($this->_extractMetadataItem(JArrayHelper::getValue($array, "stream_size", null)));
		} else {
			$metadata->size = 0;
		}
		
		
		if (JArrayHelper::getValue($array, "Author")) {
			$metadata->author = $this->_extractMetadataItem(JArrayHelper::getValue($array, "Author", null));
		} else if (JArrayHelper::getValue($array, "creator")) {
			$metadata->author = $this->_extractMetadataItem(JArrayHelper::getValue($array, "creator", null));
		} else {
			$metadata->author = "";
		}
		
		$metadata->created = $this->_extractMetadataItem(JArrayHelper::getValue($array, "Creation-Date", null));
		
		$metadata->modified = $this->_extractMetadataItem(JArrayHelper::getValue($array, "date", null));
		
		if (!$metadata->created && $metadata->modified) {
			$metadata->created = $metadata->modified;
		}
		
		if ($metadata->created && !$metadata->modified) {
			$metadata->modified = $metadata->created;
		}
		
		$metadata->fileName = $this->_extractMetadataItem(JArrayHelper::getValue($array, "resourceName", null));
		
		$metadata->title = $this->_extractMetadataItem(JArrayHelper::getValue($array, "title", null));
		
		$metadata->metadescription = $this->_extractMetadataItem(JArrayHelper::getValue($array, "description", null));
		
		$metadata->metakeywords = $this->_extractMetadataItem(JArrayHelper::getValue($array, "Keywords", null));

		return $metadata;
	}
	
	private function _extractMetadataItem($item)
	{
		if (is_array($item)) {
			$metadata = JArrayHelper::getValue($item, 0, null);
		} else {
			$metadata = $item;
		}
		
		return $metadata;
	}

	protected function buildQuery($rules)
	{
		$array = $this->parseRules($rules);

		$database = JFactory::getDBO();
		
		$query = "SELECT a.id, a.itemID, a.filename, a.title, a.titleAttribute, " . 
				 "b.title AS itemTitle, c.name AS categoryTitle " .
				 "FROM #__k2_attachments AS a " .
				 "INNER JOIN #__k2_items AS b ON (a.itemID = b.id) " . 
				 "INNER JOIN #__k2_categories AS c ON (b.catid = c.id) " . 
				 "WHERE b.published = 1 AND b.checked_out = 0 AND b.trash = 0"; 
		
		if (JArrayHelper::getValue($array, "item", null)) {
			$query .= " AND a.id NOT IN (" . $database->getEscaped(JArrayHelper::getValue($array, "attachment", null)) . ")";
		}

		if (JArrayHelper::getValue($array, "item", null)) {
			$query .= " AND b.itemID NOT IN (" . $database->getEscaped(JArrayHelper::getValue($array, "item", null)) . ")";
		}
		
		if (JArrayHelper::getValue($array, "category", null)) {
			$query .= " AND b.catid NOT IN (" . $database->getEscaped(JArrayHelper::getValue($array, "category", null)) . ")";
		}

		$query .= ";";
		
		return $query;
	}
}