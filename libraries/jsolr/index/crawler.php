<?php
/**
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012 - 2013 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr library for Joomla!.

   The JSolr library for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr library for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrIndex component for Joomla!.  If not, see 
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

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.plugin.plugin');

jimport('jsolr.helper');
jimport('jsolr.index.factory');
jimport('jsolr.apache.solr.service');
jimport('jsolr.apache.solr.document');

abstract class JSolrIndexCrawler extends JPlugin 
{
	protected static $chunk;
	
    /**
     * The extension of the indexed item.
     * 
     * E.g. com_content
     *
     * @var string
     */
	protected $extension;
	
    /**
     * The view of the indexed item.
     * 
     * E.g. article
     *
     * @var string
     */
	protected $view;
	
	protected $indexOptions = array();

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		
		self::$chunk = 1000;
		
		Jlog::addLogger(array('text_file'=>'jsolr.php'), JLog::ALL, 'jsolr');
	}
	
	/**
	* Prepares an article for indexing.
	*/
	protected abstract function getDocument(&$record);

	/**
	 * Get's the language, either from the item or from the Joomla environment.
	 * 
	 * @param JObject $item The item being indexed.
	 * @param bool $includeRegion True if the region should be included, false 
	 * otherwise. E.g. If true, en-AU would be returned, if false, just en 
	 * would be returned.
	 * 
	 *  @return string The language code.
	 */
	protected function getLanguage(&$item, $includeRegion = true)
	{
		if (isset($item->language) && $item->language != '*') {			
			$lang = $item->language;
		} else {
			if (!($lang = JLanguageHelper::detectLanguage())) {
				$lang = JFactory::getLanguage()->getDefault();
			}			
		}

		if ($includeRegion) {
			return $lang;
		} else {
			$parts = explode('-', $lang);
			
			// just return the xx part of the xx-XX language.
			return JArrayHelper::getValue($parts, 0);
		}
	}
	
	/**
	 * 
	 * 
	 * @return JDatabaseQuery A database query.
	 */
	abstract protected function buildQuery();
	
	protected function getItems()
	{
		$database = JFactory::getDBO();
		$database->setQuery($this->buildQuery());

		return $database->loadObjectList();
	}
	
	/**
	 * Builds the item's index key.
	 * 
	 * Takes the form extension.view.id, E.g. com_content.article.1.
	 * 
	 * The key can be customized by overriding this method but it is not 
	 * recommended.
	 * 
	 * @param JSolrApacheSolrDocument $document The document to use to build the 
	 * key.
	 * 
	 * @return string The item's key.
	 */
	protected function buildKey($document)
	{
		$extension = $document->getField('extension');
		$extension = JArrayHelper::getValue($extension, 'value');
		$extension = JArrayHelper::getValue($extension, 0);
		$view = $document->getField('view');
		$view = JArrayHelper::getValue($view, 'value');
		$view = JArrayHelper::getValue($view, 0);
		$id = $document->getField('id');
		$id = JArrayHelper::getValue($id, 'value');
		$id = JArrayHelper::getValue($id, 0);
		return $extension.'.'.$view.'.'.$id;
	}
	
	/**
	 * Builds Solr documents and indexes them to the Solr server.
	 * 
	 * @throws Exception
	 */
	public function onIndex($options = array())
	{
		$this->set('indexOptions', $options);

		try {
			if (JArrayHelper::getValue($this->get('indexOptions'), "rebuild", false, 'bool')) {
				$this->rebuild();	
			} elseif (JArrayHelper::getValue($this->get('indexOptions'), "clean", false, 'bool')) {
				$this->clean();
			} else {
				$this->index();
			}
		} catch (Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'jsolrsearch');
				
			$this->out('index failed. '.$e->getMessage());
			$this->out('index failed. '.$e->getTraceAsString());
		}
	}
	
	/**
	 * Rebuilds the current extension's indexed items, deleting them first, 
	 * then indexing them.
	 */
	protected function rebuild()
	{
		$solr = JSolrIndexFactory::getService();
		
		$this->out('deleting all '.$this->get('extension').' items');
		
		$solr->deleteByQuery('extension:'.$this->get('extension'));
		
		$solr->commit();
		
		$this->index();
	}
	
	/**
	 * Cleans deleted items from the index.
	 */
	protected function clean()
	{
		
	}
	
	/**
	 * Adds items to the index.
	 */
	protected function index()
	{
		$total = 0;

		$items = $this->getItems();
		
		$solr = JSolrIndexFactory::getService();
		
		if (is_array($items)) {				
			$documents = array();
			$i = 0;
			
			foreach ($items as $item) {
				$documents[$i] = $this->prepare($item);

				$this->out('document '.$this->buildKey($documents[$i]).' ready for indexing');
				
				$total++;
				$i++;

				// index when either the number of items retrieved matches 
				// the total number of items being indexed or when the 
				// index chunk size has been reached. 
				if ($i == count($items) || $i % self::$chunk == 0) {						
					$response = $solr->addDocuments($documents, false, true, true, 10000);
											
					$this->out($i.'documents indexed [status:'.$response->getHttpStatus().']');
					
					$documents = array();
					$i = 0;
				}					
			}			
		}
					
		$this->out($this->get('extension').' crawler completed.')
			 ->out("items indexed: $total");
	}
	
	/**
	 * Prepare the item for indexing.
	 * 
	 * @param stdClass $item
	 * @return JSolrApacheSolrDocument
	 */
	protected function prepare($item)
	{
		// Initialize the item's parameters.
		if (isset($item->params)) {
			$registry = new JRegistry();
			$registry->loadString($item->params);
			$item->params = JComponentHelper::getParams($this->get('extension'), true);
			$item->params->merge($registry);
		}
		
		if (isset($item->metadata)) {
			$registry = new JRegistry();
			$registry->loadString($item->metadata);
			$item->metadata = $registry;
		}
	
		$document = $this->getDocument($item);
		$document->addField('id', $item->id);
		$document->addField('extension', $this->get('extension'));
		$document->addField('view', $this->get('view'));
		$document->addField('lang', $this->getLanguage($item));
		
		$key = $this->buildKey($document);
		
		$document->addField('key', $key);

		return $document;
	}
	
	protected function out($text = '', $nl = true)
	{		
		if (JArrayHelper::getValue($this->get('indexOptions'), "application", null, 'string') == 'JSolrCrawlerCli') {
			if (JArrayHelper::getValue($this->get('indexOptions'), "verbose", false, 'bool')) {
				fwrite(STDOUT, $text . ($nl ? "\n" : null));
			}
		}
	
		return $this;
	}
	
	private function _getContentTypes($param)
	{
		$params = JComponentHelper::getParams('com_jsolrindex', true);
		
		$types = $params->get($param);
		
		return array_map('trim', explode(',', trim($types)));		
	}
	
	protected function getAllowedContentTypes()
	{
		return $this->_getContentTypes('content_types_allowed');
	}
	
	protected function getIndexContentContentTypes()
	{
		return $this->_getContentTypes('content_types_index_content');
	}
	
	protected function isAllowedContentType($contentType)
	{
		$allowed = false;
		
		$types = $this->getAllowedContentTypes();

		while ((($type = current($types)) !== false) && !$allowed) {
			if (preg_match("#".$type."#i", $contentType)) {
				$allowed = true;
			}
			
			next($types);
		}
		
		return $allowed;
			
	}
	
	protected function isContentIndexable($contentType)
	{
		$allowed = false;
	
		$types = $this->getIndexContentContentTypes();
	
		while ((($type = current($types)) !== false) && !$allowed) {
			if (preg_match("#".$type."#i", $contentType)) {
				$allowed = true;
			}
				
			next($types);
		}
	
		return $allowed;
			
	}
	
	/**
	 * Gets a formatted facet based on the JSolrIndex configuration.
	 * 
	 * @param string $facet
	 * 
	 * @return string A formatted facet based on the JSolrIndex configuration.
	 */
	protected function getFacet($facet)
	{
		switch (intval(JComponentHelper::getParams('com_jsolrindex')->get('casesensitivity'))) {
			case 1:
				return JString::strtolower($facet);
				break;
	
			case 2:
				return JSolrHelper::toCaseInsensitiveFacet($facet);
				break;
					
			default:
				return $facet;
				break;
		}
	}
}