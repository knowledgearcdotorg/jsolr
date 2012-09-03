<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
* A plugin for searching articles.
 *
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr Search JSpace plugin for Joomla!.

   The JSolr Search JSpace plugin for Joomla! is free software: you can 
   redistribute it and/or modify it under the terms of the GNU General Public 
   License as published by the Free Software Foundation, either version 3 of 
   the License, or (at your option) any later version.

   The JSolr Search JSpace plugin for Joomla! is distributed in the hope 
   that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr Search JSpace plugin for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

jimport('joomla.error.log');

require_once JPATH_ROOT.DS."administrator".DS."components".DS."com_jsolrsearch".DS."helpers".DS."plugin.php";

class plgJSolrSearchJSpace extends JSolrSearchPlugin 
{
	protected $extension = 'com_jspace';

	public function __construct(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		
		$this->set('highlighting', array("title", "body", "author"));
		$this->set('operators', array('author'));
	}

	/**
	 * Add custom filters to the main query.
	 * 
	 * @param JObject $state An instance of JObject. Holds query variables of 
	 * the class which triggered this plugin event.
	 * @param string $language The current language.
	 */
	public function onJSolrSearchFQAdd($state, $language)
	{
		$array = array('-view:bitstream');

		if ($value = JArrayHelper::getValue($state->get('query.q.operators'), 'author')) {
			$array[] = 'author:'.$value;	
		}
		
		return $array;
	}

	/**
	* Format a com_jspace document and return a generic result item.
	* 
	* @param mixed $document
	* @param mixed $hl
	* @param int $hlFragSize
	* @param string $lang
	*/
	public function onJSolrSearchResultPrepare($document, $hl, $hlFragSize, $lang) 
	{
		$id = $document->key;
		$title = "title_$lang";
		$category = "category_$lang";
		
		if ($document->extension == $this->get('extension')) {
			if (isset($hl->$id->$title)) {
        		$hlTitle = JArrayHelper::getValue($hl->$id->$title, 0);
			} else {
				$hlTitle = $document->$title;
			}

			$document->title = $hlTitle;
			$document->href = ""; //JSpaceHelperRoute::getItemRoute($document->id, $document->parent_id);
			$document->snippet = $this->_getHlContent($document, $hl, $hlFragSize, $lang);
			
			return $document;
		}
		
		return null;
	}
	
	private function _getHlContent($document, $highlighting, $fragSize, $lang)
	{
		$id = $document->key;
		$hlContent = array();

		$content = "body_$lang";

		if (isset($highlighting->$id->$content)) {
			foreach ($highlighting->$id->$content as $item) {
				$hlContent[] = $item;	
			}
		}
		
		return implode("...", $hlContent);
	}
}