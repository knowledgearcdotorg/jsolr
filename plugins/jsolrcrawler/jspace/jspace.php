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

jimport('jspace.factory');
jimport('jsolr.index.crawler');

class plgJSolrCrawlerJSpace extends JSolrIndexCrawler
{
	protected $extension = 'com_jspace';
	
	protected $view = 'item';

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		
		static::$chunk = 50;		
		
	}
	
	/**
	 * @return JSpaceRepositoryConnector
	 */
	private function _getConnector()
	{
		$options = null;
		
		if ($this->get('params')->get('use_jspace_connection_params')) {
			if (!JComponentHelper::isEnabled("com_jspace", true)) {
				JLog::add(JText::_('PLG_JSOLRCRAWLER_JSPACE_COM_JSPACE_NOT_FOUND'), JLog::ERROR, 'jsolrcrawler');
				return;
			}
			
			$options = array();
			$options['driver'] = $this->params->get('driver');
			$options['url'] = $this->params->get($options['driver'].'_rest_url');
			$options['username'] = $this->params->get($options['driver'].'_username');
			$options['password'] = $this->params->get($options['driver'].'_password');
		} else {
			$options = array();
			$options['driver'] = 'dspace';
			$options['url'] = $this->params->get('rest_url');
			$options['username'] = $this->params->get('username');
			$options['password'] = $this->params->get('password');
		}

		return JSpaceFactory::getConnector($options);
	}
	
	private function _getCrosswalk()
	{
		return JSpaceFactory::getCrosswalk('dublincore');
	}
	
	/**
	 * Gets all DSpace items using the JSpace component and DSpace REST API.
	 * 
	 * @return array A list of DSpace items.
	 */
	protected function getItems()
	{
		$items = array();
		
		try {
			$items = array();
			
			$connector = $this->_getConnector();
						
			$vars = array();
			$vars['q'] = '*:*';
			$vars['fl'] = 'search.resourceid';
			$vars['fq'] = 'search.resourcetype:2';
			$vars['rows'] = '2147483647';

			if ($lastModified = JArrayHelper::getValue($this->get('indexOptions'), 'lastModified', null, 'string')) {
				$lastModified = JFactory::getDate($lastModified)->format('Y-m-d\TH:i:s\Z', false);

				$vars['q'] = urlencode("SolrIndexer.lastIndexed:[$lastModified TO NOW]");
			}

			$response = json_decode($connector->get(JSpaceFactory::getEndpoint('/discover.json', $vars)));

			if (isset($response->response->docs)) {
				$items = $response->response->docs;
			}
		} catch (Exception $e) {
        	JLog::add($e->getMessage(), JLog::ERROR);
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
					if ($item->qualifier == 'author' || $item->qualifier == 'other') {
						if ($item->qualifier == 'author') {
							$doc->addField('author', $item->value);
						}
						
						$doc->addField($field.'_'.$lang, $item->value);
						$doc->addField($field."_fc", $item->value); // for faceting
						$doc->addField($field."_ac", $item->value); // for auto complete
						$doc->addField($field."_sort", $item->value); // for auto complete
					}
					
					$doc->addField($field.'_sm', $item->value);
					$doc->addField($field.'_txt', $item->value); // for lower-case searching
					
					break;
					
				case 'subject':
					if (!$item->qualifier) {
						$doc->addField($field.'_'.$lang, $item->value);
						$doc->addField($field."_fc", $item->value); // for faceting
						$doc->addField($field."_ac", $item->value); // for auto complete
					}
					
					$doc->addField($field.'_sm', $item->value);
					
					break;
				
				case 'type':
					if (!$item->qualifier) {
						$doc->addField($field.'_'.$lang, $item->value);
						$doc->addField($field."_fc", $item->value); // for faceting
						$doc->addField($field."_ac", $item->value); // for auto complete
					} else {
						$doc->addField($field.'_sm', $item->value); 
					}
					break;
					
				case 'description':
					if (!$item->qualifier) {
						$doc->addField('body_'.$lang, $item->value);
						$doc->addField($field.'_'.$lang, $item->value);
					} else {
						$doc->addField($field.'_sm', $item->value); 
					}
					
					break;
					
				case 'publisher':
					if (!$item->qualifier) {
						$doc->addField($field.'_'.$lang, $item->value);
						$doc->addField($field."_fc", $item->value); // for faceting
						$doc->addField($field."_ac", $item->value); // for auto complete
					} else {
						$doc->addField($field.'_sm', $item->value);
					}
					break;
					
					
				default:
					$doc->addField($field.'_sm', $item->value);
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

		$connector = $this->_getConnector();
		
		$endpoint = JSpaceFactory::getEndpoint('/items/'.$parent->id.'/bundles.json', array('type'=>'ORIGINAL'));		
		
		$bundles = json_decode($connector->get($endpoint)); 

		$i = 0;
		
		$path = JArrayHelper::getValue($connector->getOptions(), 'url', null, 'string');

		foreach ($bundles as $bundle) {
			foreach ($bundle->bitstreams as $bitstream) {
				$document = $this->_extract($path.'/bitstreams/'.$bitstream->id.'/download');
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
				$doc->addField($metakey.'_tfm', $value);
			} elseif (is_int($value)) {
				// handle solr int/long differentiation.
				if ((int)$value > 2147483647 || (int)$value < -2147483648) {
					$doc->addField($metakey.'_tlm', $value);
				} else {
					$doc->addField($metakey.'_tim', $value);
				}
			} else {
				$doc->addField($metakey.'_sm', $value);	
			}
		}
		
		return $doc;
	}

	protected function clean()
	{
		$items = $this->getItems();
	
		$service = JSolrIndexFactory::getService();
	
		jimport('jsolr.search.factory');
	
		$query = JSolrSearchFactory::getQuery('*:*')
		->useQueryParser("edismax")
		->filters(array('extension:com_jspace', 'view:item'))
		->retrieveFields('id')
		->rows(0);
	
		$response = $query->search();

		if (isset($response->response->numFound)) {
			$query->rows($response->response->numFound);
		}
	
		$response = $query->search();
	
		if (isset($response->response->docs)) {
			$docs = $response->response->docs;
	
			$delete = array();
			$prefix = $this->get('extension').'.'.$this->get('view').'.';

			foreach ($docs as $doc) {
				$needle = new stdClass();
				$needle->{'search.resourceid'} = $doc->id;

				if (array_search($needle, $items) === false) {		
					$delete[] = $prefix.$doc->id;
				}
			}

			if (count($delete)) {
				foreach ($delete as $key) {
					$this->out('cleaning item '.$key.' and its bitstreams');
					
					$query = 'extension:'.$this->get('extension').
						' AND view:bitstream'.
						' AND parent_id:'.str_replace($prefix, '', $key);
					$service->deleteByQuery($query);
				}				
				
				$service->deleteByMultipleIds($delete);
				
				$response = $service->commit();
			}
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JSolrIndexCrawler::index()
	 */
	protected function index()
	{		
		if (!jimport('joomla.factory')) {
			JLog::add(JText::_('PLG_JSOLRCRAWLER_JSPACE_COM_JSPACE_NOT_FOUND'), JLog::ERROR, 'jsolrcrawler');
			
			return array();
		}

		$total = 0;
		$totalBitstreams = 0;
		
		$items = $this->getItems();
		
		$solr = JSolrIndexFactory::getService();

		$documents = array();
		
		$connecter = $this->_getConnector();
		
		$i = 0;

		foreach ($items as $temp) {
			$item = json_decode($connecter->get(JSpaceFactory::getEndpoint('/items/'.$temp->{'search.resourceid'}.'.json')));
			
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
			
			$this->out('item '.$key.' ready for indexing');

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
						$documents[$i]->addField('bitstream_'.$metakey.'_tfm', $value);
					} elseif (is_int($value)) {
						// handle solr int/long differentiation.
						if ((int)$value > 2147483647 || (int)$value < -2147483648) {
							$documents[$i]->addField('bitstream_'.$metakey.'_tlm', $value);
						} else {
							$documents[$i]->addField('bitstream_'.$metakey.'_tim', $value);
						}							
					} else {
						$documents[$i]->addField('bitstream_'.$metakey.'_sm', $value);	
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
				
				$this->out('bitstream '.$key.' ready for indexing');
				
				$totalBitstreams++;
				$j++;
			}
			
			$total++;
			$i=$j;
			
			// index when either the number of items retrieved matches
			// the total number of items being indexed or when the
			// index chunk size has been reached.
			if ($total == count($items) || $i > static::$chunk) {
				$response = $solr->addDocuments($documents, false, true, true, 10000);
				
				$this->out($i.' documents indexed [status:'.$response->getHttpStatus().']');
				
				$documents = array();
				$i = 0;
			}
		}

		$this->out($this->get('extension')." crawler completed.")
			 ->out("items indexed: $total")
			 ->out("bitsteams indexed: $totalBitstreams");
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
				passthru("java -jar ".$params->get('local_tika_app_path')." ".$path." 2> /dev/null");
				$result = ob_get_contents();
				ob_end_clean();				
				$document->body = $result;

				ob_start();
				passthru("java -jar ".$params->get('local_tika_app_path')." -j ".$path." 2> /dev/null");
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