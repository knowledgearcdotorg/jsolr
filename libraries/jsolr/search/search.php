<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2011-2013 Wijiti Pty Ltd. All rights reserved.
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
   along with the JSolrSearch component for Joomla!.  If not, see 
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
jimport('joomla.utilities.arrayhelper');

abstract class JSolrSearchSearch extends JPlugin 
{	
	protected $highlighting = array();
	
	protected $operators = array();
	
	public function __construct(&$subject, $config = array()) 
	{	
		parent::__construct($subject, $config);
		
		$this->loadLanguage();
	}
	
	/**
	 * Lists fields and the boosts to associate with each.
	 * 
	 * Override this method to add more query field values.
	 */
	public function onJSolrSearchQFAdd()
	{	
		$qf = array();

		foreach ($this->get('params')->toArray() as $key=>$value) {
				if (strpos($key, "boost_") === 0) {
					$qfKey = str_replace("boost_", "", $key);
					$qf[$qfKey] = floatval($value);
				}
		}

		return $qf;
	}

	/**
	 * Gets a list of operator mappings for this search plugin.
	 * 
	 * Each operator takes the form array[facet_name] = [search_name] where 
	 * [facet_name] is the field to browse on and [search_name] is the 
	 * corresponding field to search on when navigating from browse to search.  
	 * 
	 * The [search_name] is used for stripping the correct operators off of 
	 * the query.
	 */
	final public function onJSolrSearchOperatorsGet()
	{
		return $this->operators;
	}

	/**
	 * Lists fields that have highlighting applied on the found text. 
	 */
	final public function onJSolrSearchHLAdd()
	{
		return $this->highlighting;
	}
	
	final public function onJSolrSearchExtensionGet()
	{	
		$extension = new JObject();
		
		$extension->set('name', $this->get('extension'));
		$extension->set('title', JText::_("PLG_JSOLRSEARCH_".strtoupper($this->get('extension'))));
	
		return $extension;
	}
	
	/**
	 * Retrieve the individual result template for this plugin.
	 * 
	 * @param string $option The option used to identify the associated 
	 * template.
	 * 
	 * @return string The path to the individual result template for this 
	 * plugin.
	 */
	public function onFindResultTemplatePath($option)
	{
		$pluginsPath = JPATH_PLUGINS."/jsolrsearch/";		
		
		$path = false;

		// if the o query string has not been set, exit immediately.
		if (!$option) {
			return $path;	
		}

		$path = JPath::find($pluginsPath.$this->getTemplateDirectoryName()."/views/results", $option.".php");

		if (!$path) {
			$path = JPath::find($pluginsPath.$this->getTemplateDirectoryName()."/views/result", "default.php");
		}
		
		return $path;
	}
	
	/**
	 * Retrieve the custom results template for this plugin.
	 * 
	 * @param string $o The currently selected option(s).
	 * 
	 * @return string The path to the custom results template for this plugin. 
	 */
	public function onFindResultsTemplatePath($o)
	{
		$pluginsPath = JPATH_PLUGINS."/jsolrsearch/";		
		
		$path = false;

		// if the o query string has not been set, exit immediately.
		if (!$o) {
			return $path;	
		}
		
		$options = explode(",", $o);
	
		while (!$path && $option = current($options)) {
			if (array_key_exists($option, $this->onJSolrSearchExtensionGet())) {
				$path = JPath::find($pluginsPath.$this->getTemplateDirectoryName()."/views/results", "default.php");
			}
			
			next($options);
		}
		
		return $path;
	}
	
	/**
	 * Maps a Solr document to a generic result object.
	 * 
	 * @param JSolrApacheSolrDocument $document A Solr document.
	 * @param stdClass $hl Highlighted fields.
	 * @param int $hlFragSize The size of the highlighted fragment.
	 * @param string $lang The language the result should be returned in.
	 * 
	 * @return stdClass A generic result object.
	 */
	public function onJSolrSearchResultPrepare($document, $hl, $hlFragSize, $lang)
	{
		return $document;
	}
	
	/**
	 * @deprecated
	 */
	protected function getTemplateDirectoryName()
	{
		return str_ireplace("jsolr", "", $this->get("_name"));
	}
}