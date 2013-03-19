<?php
/**
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr JSpace Index plugin for Joomla!.

   The JSolr JSpace Index plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr JSpace Index plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr JSpace Index plugin for Joomla!.  If not, see 
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

jimport('joomla.log.log');
jimport('jrest.client.client');

jimport('jsolr.index.crawler');

class plgJSolrCrawlerJSpace extends JSolrIndexCrawler
{
	protected $extension = 'com_jspace';
	
	protected $view = 'item';
	
	/**
	 * Gets all DSpace items using the JSpace component and DSpace REST API.
	 * 
	 * @return array A list of DSpace items.
	 */
	protected function getItems()
	{
		$items = array();

		$params = null;
		
		if ($this->get('params')->get('use_jspace_connection_params')) {		
			if (!JComponentHelper::isEnabled("com_jspace", true)) {
				JLog::add(JText::_('PLG_JSOLRCRAWLER_JSPACE_COM_JSPACE_NOT_FOUND'), JLog::ERROR, 'jsolrcrawler');
				return;	
			}

			$params = JComponentHelper::getParams('com_jspace');		
		} else {
			$params = $this->params;
		}
		
		try {
			$items = array();
			
			$url = JFactory::getURI($params->get("rest_url").'/items.json');
			$url->setVar("user", $params->get("user"));
			$url->setVar("pass", $params->get("pass"));
				
			$client = new JRestClient($url->toString(), 'get');
			$client->execute();
			
			if (JArrayHelper::getValue($client->getResponseInfo(), "http_code") == 200) {
	        	$response = json_decode($client->getResponseBody());
	        	$items = $response->items;
			} else {
				JLog::add($client->getResponseInfo(). " " . $client->getResponseBody(), JLog::ERROR, 'jsolrcrawler');			
			}

		} catch (Exception $e) {
        	JLog::add($client->getResponseInfo()." ".$e->toString(), JLog::ERROR);
		}
		
		return $items;			
	}
	
	/**
	 * Prepares an article for indexing.
	 */
	protected function getDocument(&$record)
	{	
		$doc = new JSolrApacheSolrDocument();
		
		$lang = $this->getLanguage($record, false);
		
		$doc->addField('handle_s', $record->handle);
		$doc->addField('title', $record->name);
		$doc->addField('title_'.$lang, $record->name);
		
		foreach ($record->metadata as $item) {
			$field = $item->schema.'.'.$item->element;

			if ($item->qualifier) {
				$field .= '.'.$item->qualifier;	
			}

			switch ($item->element) {
				case 'date':
					// @todo Dates are confusing in DSpace as they are never
					// guaranteed to be generated. There may need to be a 
					// better method devised for handling them.
					
					$datePattern = "/[0-9]{4}-[0-9]{2}-[0-9]{2}[Tt][0-9]{2}:[0-9]{2}:[0-9]{2}[Zz]/";
					$suffix = 's';
					$value = $item->value;
					
					if (preg_match($datePattern, $item->value) > 0) {
						$suffix = 'dt';
						$date = JFactory::getDate($item->value);
						$value = $date->format('Y-m-d\TH:i:s\Z', false);
						
						if ($item->qualifier == 'available') {
							$doc->addField('created', $value);
							$doc->addField('modified', $value);
						}
					}
					
					$doc->addField($field.'_'.$suffix, $value);
					
					break;

				case 'contributor':
					if ($item->qualifier == 'author') {
						$doc->addField('author', $item->value);
						$doc->addField('author_'.$lang, $item->value);
						$doc->addField("author_fc", $item->value); // for faceting
						$doc->addField("author_ac", $item->value); // for auto complete
						$doc->addField("author_sort", $item->value); // for auto complete
					}
					
					$doc->addField($field.'_s_multi', $item->value);
					
					break;
					
				case 'subject':
					if (!$item->qualifier) {
						$doc->addField($field.'_'.$lang, $item->value);
						$doc->addField("keywords_fc", $item->value); // for faceting
						$doc->addField("keywords_ac", $item->value); // for auto complete
					}
					
					$doc->addField($field.'_s_multi', $item->value);
					
					break;
				
				case 'type':
					if (!$item->qualifier) {
						$doc->addField($field.'_'.$lang, $item->value);
						$doc->addField("type_fc", $item->value); // for faceting
						$doc->addField("type_ac", $item->value); // for auto complete
					} else {
						$doc->addField($field.'_s_multi', $item->value); 
					}					
					break;
					
				case 'description':
					if (!$item->qualifier) {
						$doc->addField('body_'.$lang, $item->value);
						$doc->addField($field.'_'.$lang, $item->value);
					} else {
						$doc->addField($field.'_s_multi', $item->value); 
					}
					
					break;
					
				default:
					$doc->addField($field.'_s_multi', $item->value);
					break;
			}
		}
		
		return $doc;
	}
	
	/**
	 * Gets a list of bitstreams for the parent item.
	 * 
	 * @param stdClass $parent The parent Solr item.
	 * @return array An array of bitstream objects.
	 */
	private function _getBitstreams($parent)
	{
		$bundles = array();
		$bitstreams = array();

		$params = $this->params;
		
		if ($params->get('use_jspace_connection_params')) {		
			if (!JComponentHelper::isEnabled("com_jspace")) {
				JLog::add(JText::_('PLG_JSOLRCRAWLER_JSPACE_COM_JSPACE_NOT_FOUND'), JLog::ERROR, 'jsolrcrawler');
				return;	
			}

			$params = JComponentHelper::getParams('com_jspace');		
		}
		
		$url = JFactory::getURI($params->get("rest_url").'/items/'.$parent->id.'/bundles.json?type=ORIGINAL');		
		$url->setVar("user", $params->get("user"));
		$url->setVar("pass", $params->get("pass"));

		$client = new JRestClient($url->toString(), 'get');
		$client->execute();

		if (JArrayHelper::getValue($client->getResponseInfo(), "http_code") == 200) {
        	$bundles = json_decode($client->getResponseBody());

		} else {
			JLog::add($client->getResponseInfo(). " " . $client->getResponseBody(), JLog::ERROR, 'jsolrcrawler');			
		}

		$i = 0;
		
		foreach ($bundles as $bundle) {
			foreach ($bundle->bitstreams as $bitstream) {
				$document = $this->_extract($params->get('base_url').'/bitstream/handle/'.$parent->handle.'/'.rawurlencode($bitstream->name));
				$bitstreams[$i] = $bitstream;
				$bitstreams[$i]->body = $document->body;
				$bitstreams[$i]->metadata = $document->metadata;
				
				$i++;
			}
		}
		
		return $bitstreams;
	}
	
	private function _getBitstreamDocument($record)
	{
		$doc = new JSolrApacheSolrDocument();
		
		$lang = $this->getLanguage($record, false);

		$doc->addField('id', $record->id);
		$doc->addField('extension', $this->get('extension'));
		$doc->addField('view', 'bitstream');
		$doc->addField('lang', $this->getLanguage($record));
		$doc->addField('key', $this->get('extension').'.bitstream.'.$record->id);
		
		$doc->addField('title', $record->name);
		$doc->addField('title_'.$lang, $record->name);
		
		if (strip_tags($record->body)) {
			$doc->addField("body_$lang", strip_tags($record->body));
		}
		
		foreach ($record->metadata->toArray() as $key=>$value) {
			$metakey = $this->_cleanBitstreamMetadataKey($key);

			if (is_float($value)) {
				$doc->addField($metakey.'_f_multi', $value);
			} elseif (is_int($value) || is_long($value)) {
				$doc->addField($metakey.'_i_multi', $value);
			} else {
				$doc->addField($metakey.'_s_multi', $value);	
			}
		}
		
		return $doc;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JSolrIndexCrawler::onIndex()
	 */
	public function onIndex()
	{
		$items = $this->getItems();

		$ids = array();
		$documents = array();

		$i = 0;
		foreach ($items as $item) {
			// Initialize the item's parameters.
			if (isset($item->params)) {
				$registry = new JRegistry();
				$registry->loadString($item->params);
				$item->params = JComponentHelper::getParams($this->get('extension'), true);
				$item->params->merge($registry);
			}

			$documents[$i] = $this->getDocument($item);
			$documents[$i]->addField('id', $item->id);
			$documents[$i]->addField('extension', $this->get('extension'));
			$documents[$i]->addField('view', $this->get('view'));
			$documents[$i]->addField('lang', $this->getLanguage($item));
			
			$key = $this->buildKey($documents[$i]);
			
			$documents[$i]->addField('key', $key);

			$ids[$i] = $key;

			// index bitstream metadata and content against record to 
			// enhance searching. These values are for enhanced search 
			// only and shouldn't be used when retrieving information about 
			// an individual bitstream.
			$bitstreams = $this->_getBitstreams($item);		
	
			$j=$i;
			$j++;

			foreach ($bitstreams as $bitstream) {
				$documents[$i]->addField('bitstream_title_'.$this->getLanguage($item, false), $bitstream->name);
				$documents[$i]->addField('bitstream_body_'.$this->getLanguage($item, false), strip_tags($bitstream->body));

				foreach ($bitstream->metadata->toArray() as $key=>$value) {
					$metakey = $this->_cleanBitstreamMetadataKey($key);
	
					if (is_float($value)) {
						$documents[$i]->addField('bitstream_'.$metakey.'_f_multi', $value);
					} elseif (is_int($value) || is_long($value)) {
						$documents[$i]->addField('bitstream_'.$metakey.'_i_multi', $value);
					} else {
						$documents[$i]->addField('bitstream_'.$metakey.'_s_multi', $value);	
					}
				}
				
				$documents[$j] = $this->_getBitstreamDocument($bitstream);

				if ($documents[$i]->getField('created')) {
					$documents[$j]->addField("created", JArrayHelper::getValue(JArrayHelper::getValue($documents[$i]->getField('created'), 'value'), 0));
				}
				
				if ($documents[$i]->getField('modified')) {
					$documents[$j]->addField("modified", JArrayHelper::getValue(JArrayHelper::getValue($documents[$i]->getField('modified'), 'value'), 0));
				}
				
				$documents[$j]->addField("parent_id", $item->id);
				
				$key = 
					JArrayHelper::getValue(
						JArrayHelper::getValue(
							$documents[$j]->getField('key'), 
							'value'), 
						0);
						
				$ids[$j] = $key;
				
				$j++;
			}
			
			$i=$j;
		}

		try {
			$params = JComponentHelper::getParams("com_jsolrindex", true);
			
			if (!$params) {
				return;
			}

			$url = $params->get('host');
			
			if ($params->get('username') && $params->get('password')) {
				$url = $params->get('username') . ":" . $params->get('password') . "@" . $url;
			}

			$solr = new JSolrApacheSolrService($url, $params->get('port'), $params->get('path'));

			if (count($ids)) {
				$solr->deleteByQuery($this->getDeleteQueryById($ids));
			} else {
				$solr->deleteByQuery('extension:'.$this->get('extension'));
			}
			
			$solr->addDocuments($documents);
			
			$solr->commit();
		} catch (Exception $e) {
			$log = JLog::getInstance();
			$log->addEntry(array("c-ip"=>"", "comment"=>$e->getMessage()));
			
			throw $e;
		}
	}
	
	protected function buildQuery()
	{
		return "";
	}
	
	/**
	 * Extracts a file's contents and metadata.
	 * 
	 * To access the returned result's contents and metadata, use the 
	 * properties body and metadata.
	 * 
	 * @example
	 * $result = $this->_extract($path);
	 * $body = $result->body;
	 * $metadata = $result->metadata;
	 * 
	 * @param string $path
	 * @return stdClass An object containing the file's body and metadata.
	 */
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

			case "solr":
				$url = $params->get('host');
				
				if ($params->get('username') && $params->get('password')) {
					$url = $params->get('username') . ":" . $params->get('password') . "@" . $url;
				}
		
				$solr = new JSolrApacheSolrService($url, $params->get('port'), $params->get('path'));
								
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
	 * Clean metadata key so that it is index friendly.
	 * @param string $key The key to clean.
	 * @return string The cleaned metadata key.
	 */
	private function _cleanBitstreamMetadataKey($key)
	{
  		$metakey = strtolower($key);
		$metakey = preg_replace("/[^a-z0-9\s\-]/i", "", $metakey);
		$metakey = preg_replace("/[\s\-]/", "_", $metakey);

		return $metakey;
	}
}