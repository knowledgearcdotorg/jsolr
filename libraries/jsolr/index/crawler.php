<?php
/**
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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

jimport('jsolr.apache.solr.service');
jimport('jsolr.apache.solr.document');

abstract class JSolrIndexCrawler extends JPlugin 
{
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

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		Jlog::addLogger(array('text_file'=>'jsolr.php'), JLog::ALL, 'jsolr');
	}
	
	/**
	* Prepares an article for indexing.
	*/
	protected abstract function getDocument(&$record);
	
	protected function getDeleteQueryById($ids)
	{
		$i = 0;
		
		$query = "";
		
		if (count($ids)) {
			$query.="-key:(";
		
			foreach ($ids as $id) {
				if ($i > 0) {
					$query .= " OR ";	
				}
				
				$query .= $id;
				
				$i++;	
			}
			
			$query .= ")";
		}
		
		return $query;
	}

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
			$lang = JLanguageHelper::detectLanguage();
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
		$extension = JArrayHelper::getValue($document->getField('extension'), 'value');
		$extension = JArrayHelper::getValue($extension, 0);
		$view = JArrayHelper::getValue($document->getField('view'), 'value');
		$view = JArrayHelper::getValue($view, 0);
		$id = JArrayHelper::getValue($document->getField('id'), 'value');
		$id = JArrayHelper::getValue($id, 0);
		return $extension.'.'.$view.'.'.$id;
	}
	
	/**
	 * Builds Solr documents and indexes them to the Solr server.
	 * 
	 * @throws Exception
	 */
	public function onIndex()
	{
		$items = $this->getItems();

		$ids = array();
		$documents = array();

		$i = 0;
		foreach ($items as $item) {
			$documents[$i] = $this->prepare($item);

			$key = JArrayHelper::getValue($documents[$i]->getField('key'), 'value');
			$ids[$i] = JArrayHelper::getValue($key, 0);
			
			$i++;
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
				$solr->deleteByQuery('extension:'.$this->get('extension').' AND view:'.$this->get('view'));
			}
			
			$solr->addDocuments($documents, false, true, true);
			
			$solr->commit();
		} catch (Exception $e) {
			$log = JLog::getInstance();
			$log->addEntry(array("c-ip"=>"", "comment"=>$e->getMessage()));
			
			throw $e;
		}
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
}