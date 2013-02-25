<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * A plugin for searching news feed items.
 *
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr Search news feeds plugin for Joomla!.

   The JSolr Search news feeds plugin for Joomla! is free software: you can 
   redistribute it and/or modify it under the terms of the GNU General Public 
   License as published by the Free Software Foundation, either version 3 of 
   the License, or (at your option) any later version.

   The JSolr Search news feeds plugin for Joomla! is distributed in the hope 
   that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr Search news feeds plugin for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

jimport('joomla.error.log');
jimport('jsolr.search.search');

require_once(JPATH_ROOT.DS."components".DS."com_newsfeeds".DS."helpers".DS."route.php");

class plgJSolrSearchNewsfeeds extends JSolrSearchSearch 
{
	protected $extension = 'com_newsfeeds';
	
	public function __construct(&$subject, $config = array()) 
	{
		$this->set('highlighting', array("title", "body", "link", "category"));
		
		parent::__construct($subject, $config);
	}
	
	/**
	* Format a com_content document and return a generic result item.
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
		$link = "link_$lang";
		$category = "category_$lang";

		if ($document->extension == $this->get('extension')) {
			if (isset($hl->$id->$title)) {
        		$hlTitle = JArrayHelper::getValue($hl->$id->$title, 0);
			} else {				
				$hlTitle = $document->$title;
			}

			if (isset($hl->$id->$link)) {
        		$hlLink = JArrayHelper::getValue($hl->$id->$link, 0);
			} else {
				$hlLink = $document->$link;
			}
			
			if (isset($hl->$id->$category)) {
        		$hlCategory = JArrayHelper::getValue($hl->$id->$category, 0);
			} else {
				$hlCategory = $document->$category;
			}

			$document->title = $hlTitle;
			$document->href = NewsfeedsHelperRoute::getNewsfeedRoute($document->id, $document->parent_id);
			$document->snippet = $this->_getHlContent($document, $hl, $hlFragSize, $lang);
			$document->category = $hlCategory;
			$document->link = $hlLink;
			
			return $document;
		}
		
		return null;
	}
	
	private function _getHlContent($document, $highlighting, $fragSize, $lang)
	{
		$id = $document->key;
		$hl = array();

		$body = "body_$lang";

		if (isset($highlighting->$id->$body)) {
			foreach ($highlighting->$id->$body as $item) {
				$hl[] = $item;
			}
		}

		return implode("...", $hl);
	}
}