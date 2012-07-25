<?php
/**
 * @paackage	JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr K2 Attachments Index plugin for Joomla!.

   The JSolr K2 Attachments Index plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr K2 Attachments Index plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr K2 Attachments Index plugin for Joomla!.  If not, see 
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

define('JSOLRCRAWLER_K2ATTACHMENTS_PATH', JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments');

class plgJSolrCrawlerK2Attachments extends JSolrCrawlerPlugin
{	
	protected $extension = 'com_k2';
	
	protected $view = 'attachment';
	
	/**
	* Prepares an article for indexing.
	*/
	protected function getDocument(&$record)
	{
		$doc = new Apache_Solr_Document();
		
		$title = "";
		
		if ($record->title) {
			$title = $record->title;
		} else if ($record->metadata->get('title')) {
			$title = $record->metadata->get('title');
		} else {
			$title = $record->metadata->get('resourceName');
		}
		
		if ($record->metadata->get('Creation-Date')) {
			$created = JFactory::getDate($record->metadata->get('Creation-Date'));
			$doc->addField('created', $created->format('Y-m-d\TH:i:s\Z', false));
		}
		
		if ($record->metadata->get('date')) {
			$modified = JFactory::getDate($record->metadata->get('date'));
			$doc->addField('modified', $modified->format('Y-m-d\TH:i:s\Z', false));
		}
		
		$lang = $this->getLanguage($record, false);
		
		$doc->addField("title", $title);
		$doc->addField("title_$lang", $title);
		$doc->addField("body_$lang", trim(strip_tags($record->body)));
		
		if ($record->metadata->get('metakeywords')) {
			$doc->addField("metakeywords_$lang", $record->metadata->get('metakeywords'));
		}
		
		if ($record->metadata->get('metadescription')) {
			$doc->addField("metadescription_$lang", $record->metadata->get('metadescription'));
		}
		
		if ($record->metadata->get('creator')) {
			$doc->addField("author", $record->metadata->get('creator'));
		} elseif ($record->metadata->get('Author')) {
			$doc->addField("author", $record->metadata->get('Author'));
		}
		
		if ($record->metadata->get('resourceName')) {
			$doc->addField("file_name_s", $record->metadata->get('resourceName'));
		}
		
		if ($record->metadata->get('Content-Type')) {
			$doc->addField("content_type_s", $record->metadata->get('Content-Type'));
		}
		
		if ($record->metadata->get('Content-Length')) {
			$doc->addField("content_size_i", $record->metadata->get('Content-Length'));
		}
		
		// Index document-specific metadata
		
		if ($record->metadata->get('Character Count')) {
			$doc->addField("character_count_i", $record->metadata->get('Character Count'));
		}

		// Index video/audio-specific metadata

		if ($record->metadata->get('xmpDM:audioSampleRate')) {
			$doc->addField("sample_rate_i", $record->metadata->get('xmpDM:audioSampleRate'));
		} elseif ($record->metadata->get('samplerate')) {
			$doc->addField("sample_rate_i", $record->metadata->get('samplerate'));
		}

		if ($record->metadata->get('xmpDM:genre')) {
			$doc->addField("genre_s", $record->metadata->get('xmpDM:genre'));
		}

		if ($record->metadata->get('xmpDM:album')) {
			$doc->addField("album_s", $record->metadata->get('xmpDM:album'));
		}
		
		$doc->addField('parent_id', $record->itemID);
		
		foreach ($this->_getTags($record, array("<h1>")) as $item) {
			$doc->addField("tags_h1_$lang", $item);
		}

		foreach ($this->_getTags($record, array("<h2>", "<h3>")) as $item) {
			$doc->addField("tags_h2_h3_$lang", $item);
		}
		
		foreach ($this->_getTags($record, array("<h4>", "<h5>", "<h6>")) as $item) {
			$doc->addField("tags_h4_h5_h6_$lang", $item);
		}
		
		if ($record->catid) {
			$doc->addField("category_$lang", $record->category);
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
	
	private function _extract($path)
	{
		$params = JComponentHelper::getParams("com_jsolrindex", true);
		
		$document = new stdClass();

		switch ($params->get('extractor')) {
			case "local":
				ob_start();
				passthru("java -jar ".$params->get('local_tika_app_path')." ".$path);
				$result = ob_get_contents();
				ob_end_clean();
				
				$document->body = $result;
				
				ob_start();
				passthru("java -jar ".$params->get('local_tika_app_path')." -j ".$path);
				$result = ob_get_contents();
				ob_end_clean();

				$document->metadata = new JRegistry();
				$document->metadata->loadString($result);

				break;
				
			case "remote":
				/** @todo This option is currently disabled. */
				/*$url = $params->get('remote_tika_host');
				
				if ($params->get('remote_tika_username') && $params->get('remote_tika_password')) {
					$url = $params->get('remote_tika_username') . ":" . 
						$params->get('remote_tika_password') . "@" . $url;
				}
				
				$solr = new Apache_Solr_Service($url, $params->get('remote_tika_port'), $params->get('remote_tika_path'));
		
				$extraction = $solr->extract($path, array("extractOnly"=>"true"));
				
				$response = json_decode($extraction->getRawResponse(), true);

				$document->content = $response[""];
				$document->metadata = $response["null_metadata"];
				
				break;*/
				
			case "solr":
				$url = $params->get('host');
				
				if ($params->get('username') && $params->get('password')) {
					$url = $params->get('username') . ":" . $params->get('password') . "@" . $url;
				}
		
				$solr = new Apache_Solr_Service($url, $params->get('port'), $params->get('path'));
								
				$extraction = $solr->extract($path, array("extractOnly"=>"true"));
				
				$response = json_decode($extraction->getRawResponse(), true);
		
				$document->content = $response[""];
				
				$metadata = array();
				
				foreach ($response->null_metadata as $key=>$value) {
					$metadata[$key] = JArrayHelper::getValue($value, 0);
				}
				
				$metadata = new JRegistry();
				$metadata->loadArray($data);
				
				$document->metadata = new JRegistry();
				$document->metadata->loadArray($metadata);

				break;
				
			default:
				
				break;
		}
		
		return $document;
	}

	/**
	 * (non-PHPdoc)
	 * @see JSolrCrawlerPlugin::getItems()
	 */
	protected function getItems()
	{
		$database = JFactory::getDBO();
		$database->setQuery($this->buildQuery());

		$items = $database->loadObjectList();
		
		for ($i = 0; $i < count($items); $i++) {
			$document = $this->_extract(JSOLRCRAWLER_K2ATTACHMENTS_PATH.DS.$items[$i]->filename);

			$items[$i]->body = $document->body;
			$items[$i]->metadata = $document->metadata;
		}
		
		return $items;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JSolrCrawlerPlugin::buildQuery()
	 */
	protected function buildQuery()
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();
		
		$query->select('a.id, a.itemID, a.filename, a.title, a.titleAttribute');  
		$query->from('#__k2_attachments AS a'); 

		$query->select('b.catid');
		$query->join('inner', '#__k2_items AS b ON a.itemID = b.id');  
		$query->where('b.published = 1 AND b.checked_out = 0 AND b.trash = 0');
		
		$query->select('c.name AS category, c.published AS cat_state, c.access AS cat_access');
		$query->join('LEFT', '#__k2_categories AS c ON c.id = b.catid');		
		
		$categories = $this->params->get('categories');

		$conditions = array();
		
		if (is_array($categories)) {
			if (JArrayHelper::getValue($categories, 0) != 0) {
				JArrayHelper::toInteger($categories);
				$categories = implode(',', $categories);
				$conditions[] = 'a.catid IN ('.$categories.')';
			}
		}

		if (count($conditions)) {
			$query->where($conditions);
		}

		return $query;
	}
}