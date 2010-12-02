<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * Search the contents of articles using a Solr server.
 *
 * @version     $LastChangedBy$
 * @package     JSolr
 * @subpackage  Search
 * @copyright   Copyright (C) 2010 inwardXpat Pty Ltd
 */

require_once JPATH_LIBRARIES."/joomla/database/table/content.php";
require_once(JPATH_ROOT.DS."components".DS."com_content".DS."helpers".DS."route.php");

class plgSearchJSolrContent extends JPlugin 
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

	function __construct(&$subject)
	{
		parent::__construct($subject);

		// load plugin parameters
		$this->_plugin = & JPluginHelper::getPlugin('index', 'jsolrcontent');
		$this->_params = new JParameter($this->_plugin->params);
		
		require_once(JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolr".DS."configuration.php");

		$configuration = new JSolrConfig();
		
		$options = array(
    		'hostname' => $configuration->host,
    		'login'    => $configuration->username,
    		'password' => $configuration->password,
    		'port'     => $configuration->port,
			'path'	   => $configuration->path
		);
		
		$this->_client = new SolrClient($options);	
	}

	/**
	* Search method
	*
	* The sql must return the following fields that are used in a common display
	* routine: href, title, section, created, text, browsernav
	* @param string Target search string
	* @param string matching option, exact|any|all
	* @param string ordering option, newest|oldest|popular|alpha|category
	*/
	function onSearch($text, $phrase = '', $ordering = '', $areas = null) 
	{
		$list = array();
		
		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onSearchAreas()))) {
				return array();
			}
		}
		
		$area = JArrayHelper::getValue($areas, 0);
		
		if ($text) {
			$queryStr = $text;
			
	        switch ($phrase) {
	                case 'exact':
	                	$queryStr = "\"".$queryStr."\"";
	                	break;
	
	                case 'all':
	                	$parts = explode(" ", $queryStr);
	                	
	                	for ($i = 0; $i < count($parts); $i++) {
	                		if ($i > 0) {
	                			$parts[$i] = "+".$parts[$i];
	                		}  
	                	}
	                	
	                	$queryStr = implode(" ", $parts);
	                	break;
	                	
	                default:
	                	// do nothing
	                	break;
	        }
	        
			$query = new SolrQuery();
			
			$query->setQuery($queryStr);
			
			$query->setHighlight(true);
			
			$query->addField('*')->addField('score');
			
			$query->addHighlightField("title");
			$query->addHighlightField("content");

			$query->setHighlightFragsize(200, "content");

			$query_response = $this->_client->query($query);

			$response = $query_response->getResponse();

			if(count($response->response->docs)) {
				$i = 0;
				
				foreach ($response->response->docs as $document) {
					$parts = explode($document->id, ".");
					$id = JArrayHelper::getValue($parts, 0, 0);
					
					$highlighting = JArrayHelper::getValue($response->highlighting, $document->id);
					
        			
					if ($highlighting->offsetExists("content")) {
						$hlContent = JArrayHelper::getValue($highlighting->content, 0);

        				$parts = explode($document->content, $hlContent);

				        if (count($parts) > 1) {
                			$hlContent = "..." . $hlContent . "...";
        				}

        				if (count($parts) == 1) {
                			if (strpos($document->content[0], $hlContent) === 0) {
                				$hlContent = "..." . $hlContent;
                			} else {
                        		$hlContent = $hlContent . "...";
                			}
        				}
					} else {
						$hlContent = substr($document->content, 0, $query->getHighlightFragsize());
					}

					if ($highlighting->offsetExists("title")) {
        				$hlTitle = JArrayHelper::getValue($highlighting->title, 0);
					} else {
						$hlTitle = $document->title;
					}
					
					$list[$i]->title = strip_tags($hlTitle);
					
					$list[$i]->href = ContentHelperRoute::getArticleRoute($id);
					
					$list[$i]->text = $hlContent;
					$list[$i]->created = $document->created;
					$list[$i]->section = JArrayHelper::getValue($document->section, 0) . "/" . JArrayHelper::getValue($document->category, 0);
					$list[$i]->browsernav = 2;
					
					$i++;
				}
			}		
		}
		
		return $list;
	}
	
	function onSearchAreas()
	{
		static $areas = array(
			'article' => 'Articles'
		);
	
		return $areas;		
	}
}