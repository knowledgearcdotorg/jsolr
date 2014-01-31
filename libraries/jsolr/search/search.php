<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2011-2013 KnowledgeARC Ltd. All rights reserved.
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
* @author Hayden Young <haydenyoung@knowledgearc.com>
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
	
	public abstract function onJSolrSearchURIGet($document);
	
	/**
	 * Lists fields and the boosts to associate with each.
	 * 
	 * Override this method to add more query field values.
	 */
	public function onJSolrSearchQFAdd($language)
	{	
		$qf = array();

		$boosts = explode(' ', $this->get('params')->get('boost', null));

		foreach ($boosts as $boost) {
			if ($boost)			
				$qf[] = $this->localize($boost, $language);
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
	final public function onJSolrSearchOperatorsGet($language = null)
	{
		return $this->operators;
	}

	/**
	 * Lists fields that have highlighting applied on the found text. 
	 */
	final public function onJSolrSearchHLAdd($language = null)
	{
		$hl = array();
		
		foreach ($this->highlighting as $higlighting) {
			if ($higlighting)
				$hl[] = $this->localize($higlighting, $language);
		}
		
		return $hl;
	}
	
	final public function onJSolrSearchExtensionGet()
	{	
		$extension = new JObject();
		
		$extension->set('name', $this->get('extension'));
		$extension->set('title', JText::_("PLG_JSOLRSEARCH_".strtoupper($this->get('extension'))));
	
		return $extension;
	}
	
	protected function localize($field, $language)
	{
		$code = $language;
		
		if (!$code) {
			$code = JFactory::getLanguage()->getTag();
		}

		$parts = explode('-', $code);
		$code = JArrayHelper::getValue($parts, 0);

		return str_replace("*", $code, $field);
	}
}