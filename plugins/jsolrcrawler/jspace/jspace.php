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

require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrindex".DS."helpers".DS."plugin.php");

class plgJSolrCrawlerJSpace extends JSolrCrawlerPlugin
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
			if (!JComponentHelper::isEnabled("com_jspace")) {
				JLog::add(JText::_('PLG_JSOLRCRAWLER_JSPACE_COM_JSPACE_NOT_FOUND'), JLog::ERROR, 'jsolrcrawler');
				return;	
			}

			$params = JComponentHelper::getParams('com_jspace');		
		} else {
			$params = $this->params;
		}
		
		$url = JFactory::getURI($params->get("rest_url").'/items.json');
		$url->setVar("user", $params->get("user"));
		$url->setVar("pass", $params->get("pass"));
			
		$client = new JRestClient($url->toString(), 'get');
		$client->execute();
		
		if (JArrayHelper::getValue($client->getResponseInfo(), "http_code") == 200) {
        	$response = json_decode($client->getResponseBody());

		} else {
			JLog::add($client->getResponseInfo(). " " . $client->getResponseBody(), JLog::ERROR, 'jsolrcrawler');			
		}

		return $response->items;
	}
	
	/**
	 * Prepares an article for indexing.
	 */
	protected function getDocument(&$record)
	{	
		$doc = new Apache_Solr_Document();
		
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
						$doc->addField('author_sort', $item->value);
						$doc->addField('author_'.$lang, $item->value);
					}
					
					$doc->addField($field.'_s_multi', $item->value);
					
					break;
					
				case 'subject':
					if (!$item->qualifier) {
						$doc->addField($field.'_'.$lang, $item->value);
					}
					
					$doc->addField($field.'_s_multi', $item->value);
					
					break;
					
				case 'description':
					if (!$item->qualifier) {
						$doc->addField($field.'_'.$lang, $item->value);
					} else {
						$doc->addField($field.'_s_multi', $item->value); 
					}
					
				default:
					$doc->addField($field.'_s_multi', $item->value);
					break;
			}
		}
		
		return $doc;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JSolrCrawlerPlugin::onIndex()
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
			$documents[$i]->addField('key', $this->buildKey($documents[$i]));
					
			$ids[$i] = JArrayHelper::getValue($documents[$i]->getField('key'), 0);
			
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

			$solr = new Apache_Solr_Service($url, $params->get('port'), $params->get('path'));

			$solr->deleteByQuery($this->getDeleteQueryById($ids));
			
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
}