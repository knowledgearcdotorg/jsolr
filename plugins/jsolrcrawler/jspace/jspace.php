<?php
/**
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012-2014 KnowledgeARC Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@knowledgearc.com> 
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
	
	protected $collections = array();
	
	private $connector = null;

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		
		static::$chunk = 50;		
		
		// set some JSpace Crawler specific rules.
		$this->set('bundleExclusions', explode(',', $this->get('params')->get('exclude_bundles_from_index', "")));
		$this->set('contentExclusions', explode(',', $this->get('params')->get('exclude_bundle_content_from_index', "")));
	}
	
	/**
	 * @return JSpaceRepositoryConnector
	 */
	private function _getConnector()
	{
		if (!$this->connector) {
			$options = null;
	
			if ($this->get('params')->get('use_jspace_connection_params', 1)) {
				if (!JComponentHelper::isEnabled("com_jspace", true)) {
					JLog::add(JText::_('PLG_JSOLRCRAWLER_JSPACE_COM_JSPACE_NOT_FOUND'), JLog::ERROR, 'jsolrcrawler');
					return null;
				}
				
				$params = JComponentHelper::getParams('com_jspace');
	
				$options = array();
				$options['driver'] = $params->get('driver', 'DSpace');
				$options['url'] = $params->get($options['driver'].'_rest_url');
				$options['username'] = $params->get($options['driver'].'_username');
				$options['password'] = $params->get($options['driver'].'_password');
				
				$this->out('settings: component');	
			} else {
				$options = array();
				$options['driver'] = 'DSpace';
				$options['url'] = $this->params->get('rest_url');
				$options['username'] = $this->params->get('username');
				$options['password'] = $this->params->get('password');
				
				$this->out('settings: plugin');
			}
			
			$this->out('driver: '.$options['driver']);
			$this->out('url: '.$options['url']);
			$this->out('username: '.$options['username']);
			$this->out('password: '.str_repeat("*", strlen($options['password'])));
	
			$this->connector = JSpaceFactory::getConnector($options);
		}
		
		return $this->connector;
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
			$vars['fl'] = 'search.resourceid,read';
			$vars['fq'] = 'search.resourcetype:2';
			$vars['rows'] = '2147483647';

			if ($this->get('params')->get('private_access', "") == "") {
				$vars['fq'] .= ' AND read:g0';
			} else {
				// only get items with read set.
				$vars['fq'] .= ' AND read:[* TO *]';
			}
			
			$vars['fq'] = urlencode($vars['fq']);

			if ($lastModified = JArrayHelper::getValue($this->get('indexingParams'), 'lastModified', null, 'string')) {
				$lastModified = JFactory::getDate($lastModified)->format('Y-m-d\TH:i:s\Z', false);

				$vars['q'] = urlencode("SolrIndexer.lastIndexed:[$lastModified TO NOW]");
			}

			$response = json_decode($connector->get(JSpaceFactory::getEndpoint('/discover.json', $vars)));

			if (isset($response->response->docs)) {
				$items = $response->response->docs;
			}
		} catch (Exception $e) {
        	JLog::add($e->getMessage(), JLog::ERROR, 'jsolrcrawler');
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
		
		if ($record->name) {
			$doc->addField('title', $record->name);
			$doc->addField('title_'.$lang, $record->name);
			$doc->addField("title_sort", $record->name); // for sorting by title					
		}
		
		if ($record->access) {
			$doc->addField('access', $record->access);
		}
		
		$collection = $this->_getCollection($record->collection->id);
		
		$doc->addField("parent_id", $collection->id);
		$doc->addField("collection_s", $collection->name);
		$doc->addField("collection_fc", $this->getFacet($collection->name));

		foreach ($record->metadata as $item) {
			$field = $item->schema.'.'.$item->element;

			if ($item->qualifier) {
				$field .= '.'.$item->qualifier;
			}
			
			if (array_search($field, $this->get('params')->get('facets')) !== false) {
				$doc->addField($field."_fc", $this->getFacet($item->value)); // for faceting
			}
						
			if (array_search($field, $this->get('params')->get('sorts')) !== false) {
				if (!is_array($item->value)) {
					$doc->addField($field.'_sort', $item->value); // for sorting
				} else {
					JLog::add('Trying to index multivalue field '.$field.' value to a sort field is not supported.', JLog::WARNING, 'jsolrcrawler');
				}
			}
			
			if ($item->qualifier == 'author') {
				$doc->addField('author', $item->value);
			}

			// Handle dates carefully then just save out all other field 
			// values to generic multi-valued indexing fields.
			if ($item->element == 'date') {
				// @todo Dates are confusing in DSpace as they are never
				// guaranteed to be generated. There may need to be a 
				// better method devised for handling them.
				
				$datePattern = "/[0-9]{4}-[0-9]{2}-[0-9]{2}[Tt][0-9]{2}:[0-9]{2}:[0-9]{2}[Zz]/";
				$suffix = 's';
				$value = $item->value;
				
				// if the date is a valid iso date then index it as such.
				if (preg_match($datePattern, $item->value) > 0) {
					$suffix = 'dt';
					$date = JFactory::getDate($item->value);
					$value = $date->format('Y-m-d\TH:i:s\Z', false);

					if ($item->qualifier == 'created' || $item->qualifier == 'modified') {
						$doc->addField('created', $value);
						$doc->addField('modified', $value);
					}
					
					if (!is_array($value)) {
						$doc->addField($field.'_sort', $value); // for sorting
					} else {
						JLog::add('Date field '.$field.' contains multiple values and so cannot be indexed for sorting.', JLog::WARNING, 'jsolrcrawler');
					}
				}
				
				$doc->addField($field.'_'.$suffix, $value);
			} else {		
				$doc->addField($field.'_'.$lang, $item->value); // language-specific indexing.
				$doc->addField($field.'_sm', $item->value); // for (almost) exact matching.
				$doc->addField($field.'_txt', $item->value); // for lower-case searching
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
		
		$endpoint = JSpaceFactory::getEndpoint('/items/'.$parent->id.'/bundles.json', null, false);		
		
		$bundles = json_decode($connector->get($endpoint)); 

		$i = 0;
		
		$path = JArrayHelper::getValue($connector->getOptions(), 'url', null, 'string');		
		
		foreach ($bundles as $bundle) {
			
			if (in_array($bundle->name, $this->get('bundleExclusions')) === false) {
				
				foreach ($bundle->bitstreams as $bitstream) {
					$exclude = in_array($bundle->name, $this->get('contentExclusions'));

					$document = $this->extract($path.'/bitstreams/'.$bitstream->id.'/download', $exclude);
					
					if ($document) {
						$bitstreams[$i] = $bitstream;
						
						if (isset($document->body)) {
							$bitstreams[$i]->body = $document->body;
						}
						
						$bitstreams[$i]->metadata = $document->metadata;
						$bitstreams[$i]->type = $bundle->name;
						
						$i++;
					}
				}
			}
		}
		
		return $bitstreams;
	}
	
	/**
	 * Gets a populated instance of the JSolrApacheSolrDocument class containing 
	 * indexable information about a single bitstream.
	 * 
	 * @param stdClass $record The bitstream information.
	 * 
	 * @return JSolrApacheSolrDocument A populated instance of the 
	 * JSolrApacheSolrDocument class containing indexable information about 
	 * the single bitstream. 
	 */
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
		
		$doc->addField('type_s', $record->type);
		
		if (isset($record->body)) {
			if (strip_tags($record->body)) {
				$doc->addField("body_$lang", strip_tags($record->body));
			}
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
	
		$results = $query->search();

		if ($results->get('numFound')) {
			$query->rows($results->get('numFound'));
		}
	
		$results = $query->search();
	
		if ($results->get('numFound')) {	
			$delete = array();
			$prefix = $this->get('extension').'.'.$this->get('view').'.';

			foreach ($results as $result) {
				$needle = new stdClass();
				$needle->{'search.resourceid'} = $result->id;

				if (array_search($needle, $items) === false) {		
					$delete[] = $prefix.$result->id;
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
			throw new Exception(JText::_('PLG_JSOLRCRAWLER_JSPACE_COM_JSPACE_NOT_FOUND'));
		}

		$total = 0;
		$totalBitstreams = 0;
		
		$items = $this->getItems();
		
		$solr = JSolrIndexFactory::getService();

		$documents = array();
		
		$connecter = $this->_getConnector();
		
		$i = 0;

		foreach ($items as $temp) {
			$total++;
			
			try {
				$item = json_decode($connecter->get(JSpaceFactory::getEndpoint('/items/'.$temp->{'search.resourceid'}.'.json', null, false)));
				
				// g0 = public
				if (array_search('g0', $temp->read) !== false) {
					$item->access = $this->get('params')->get('anonymous_access', null);						
				} else {
					$item->access = $this->get('params')->get('private_access', null);
				}
				
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

				$this->out(array('item '.$key, '[queued]'));
				
				if ($this->params->get('component.index')) {
					$bitstreams = $this->_getBitstreams($item);
					
					$j=$i;
					$j++;
		
					foreach ($bitstreams as $bitstream) {
						$totalBitstreams++;
						
						$type = strtolower($bitstream->type);
						
						$documents[$i]->addField($type.'_bitstream_id_tim', $bitstream->id);
						$documents[$i]->addField($type.'_bitstream_title_'.$this->getLanguage($item, false), $bitstream->name);
						
						if (isset($bitstream->body)) {
							$documents[$i]->addField($type.'_bitstream_body_'.$this->getLanguage($item, false), strip_tags($bitstream->body));
						}
						
						foreach ($bitstream->metadata->toArray() as $key=>$value) {
							$metakey = $this->_cleanBitstreamMetadataKey($key);
			
							if (is_float($value)) {
								$documents[$i]->addField($type.'_bitstream_'.$metakey.'_tfm', $value);
							} elseif (is_int($value)) {
								// handle solr int/long differentiation.
								if ((int)$value > 2147483647 || (int)$value < -2147483648) {
									$documents[$i]->addField($type.'_bitstream_'.$metakey.'_tlm', $value);
								} else {
									$documents[$i]->addField($type.'_bitstream_'.$metakey.'_tim', $value);
								}
							} else {
								$documents[$i]->addField($type.'_bitstream_'.$metakey.'_sm', $value);	
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
						
						$this->out(array('bitstream '.$key, '[queued]'));
						
						$j++;
					}
										
					$i=$j;
				}			
			} catch (Exception $e) {
				if ($e->getCode() == 403) {
					$this
						->out(array('Could not index item '.$temp->{'search.resourceid'},'[skipping]'))
						->out("\tReason:".$e->getMessage());
					// continue from this kind of error.
				}				
			}
			
			// index when either the number of items retrieved matches
			// the total number of items being indexed or when the
			// index chunk size has been reached.
			if ($total == count($items) || $i >= static::$chunk) {
				$response = $solr->addDocuments($documents, false, true, true, $this->params->get('component.commitsWithin', '10000'));
				
				$this->out(array($i.' documents successfully indexed', '[status:'.$response->getHttpStatus().']'));
				
				$documents = array();
				$i = 0;
			}
		}

		$this->out("items indexed: $total")
			 ->out("bitsteams indexed: $totalBitstreams");
	}
	
	/**
	 * A convenience event for adding a record to the index.
	 *
	 * Use this event when the plugin is known but the context is not.
	 *
	 * @param int $id The id of the record being added.
	 */
	public function onItemAdd($id)
	{
		$item = new JObject();
		$item->dspaceId = $id;
	
		$this->onJSolrIndexAfterSave('com_jspace.submission', $item, true);
	}
	
	protected function buildQuery()
	{
		return "";
	}
	
	public function onJSolrIndexAfterSave($context, $item, $isNew)
	{
		if ($context == 'com_jspace.submission') {
			try {	
				$endpoint = JSpaceFactory::getEndpoint('/items/'.$item->get('dspaceId').'.json', null, false);

				$documents = $this->prepare(json_decode($this->_getConnector()->get($endpoint)));
	
				$solr = JSolrIndexFactory::getService();
	
				$solr->addDocuments($documents, false, true, true, $this->params->get('component.commitWithin', '1000'));
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'jsolrcrawler');
			}
		}
	}
	
	/**
	 * Prepare the item for indexing.
	 *
	 * @param stdClass $item
	 * @return array An array of JSolrApacheSolrDocument objects to be indexed.
	 * 
	 * @todo Need to merge this and the index logic as it is being replicated.
	 */
	protected function prepare($item)
	{
		$documents = array();
		
		$i = 0;
		
		// g0 = public
		$item->access = $this->get('params')->get('anonymous_access', 1);
		
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
		
		$this->out(array('item '.$key, '[queued]'));
		
		if ($this->get('params')->get('component.index', false)) {
			$bitstreams = $this->_getBitstreams($item);
			
			$j=$i;
			$j++;
			
			foreach ($bitstreams as $bitstream) {
				$type = strtolower($bitstream->type);
			
				$documents[$i]->addField($type.'_bitstream_id_i_multi', $bitstream->id);
				$documents[$i]->addField($type.'_bitstream_title_'.$this->getLanguage($item, false), $bitstream->name);
				
				if (isset($bitstream->body)) {
					$documents[$i]->addField($type.'_bitstream_body_'.$this->getLanguage($item, false), strip_tags($bitstream->body));
				}
				
				foreach ($bitstream->metadata->toArray() as $key=>$value) {
					$metakey = $this->_cleanBitstreamMetadataKey($key);
			
					if (is_float($value)) {
						$documents[$i]->addField($type.'_bitstream_'.$metakey.'_tfm', $value);
					} elseif (is_int($value)) {
						// handle solr int/long differentiation.
						if ((int)$value > 2147483647 || (int)$value < -2147483648) {
							$documents[$i]->addField($type.'_bitstream_'.$metakey.'_tlm', $value);
						} else {
							$documents[$i]->addField($type.'_bitstream_'.$metakey.'_tim', $value);
						}
					} else {
						$documents[$i]->addField($type.'_bitstream_'.$metakey.'_sm', $value);
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
	
				$this->out(array('bitstream '.$key, '[queued]'));
			
				$j++;
			}
		}
			
		$i=$j;
		
		return $documents;
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
	
	private function _getCollection($id)
	{
		$collection = null;
	
		if (array_key_exists($id, $this->get('collections'))) {
			$collection = JArrayHelper::getValue($this->get('collections'), $id);
		} else {				
			try {
				$collection = json_decode($this->_getConnector()->get(JSpaceFactory::getEndpoint('/collections/'.$id.'.json')));
				$this->collections[$collection->id] = $collection;
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'jsolrcrawler');
				throw $e;
			}
		}
	
		return $collection;
	}
	
	public function onListMetadataFields()
	{
		$metadata = array();
		
		try {
			$metadata = json_decode($this->_getConnector()->get(JSpaceFactory::getEndpoint('/items/metadatafields.json')));
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'jsolrcrawler');			
			throw $e;
		}
		
		return $metadata;
	}
}