<?php
/**
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2014 KnowledgeARC Ltd. All rights reserved.
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
 * @author Hayden Young <haydenyoung@knowledgearc.com>
 * 
 */
 
// no direct access
defined('_JEXEC') or die();

jimport('joomla.error.log');

/**
 * A class for extracting metadata, content and other information from a file.
 */
abstract class JSolrIndexFilesystemExtractor extends JObject
{
	private $pathOrUrl;
	
	private $params;
	
	private $language;
	
	private $contentType;
	
	private $content;
	
	private $metadata;

	public function __construct($pathOrUrl)
	{
		$this->set('pathOrUrl', $pathOrUrl);
	
		$params = JComponentHelper::getParams('com_jsolrindex', true);
	
		$this->set('params', $params);
	}
	
	abstract public function getContentType();
	
	abstract public function getLanguage();
	
	abstract public function getContent();

	abstract public function getMetadata();

	public function getAllowedContentTypes()
	{
		$types = $this->get('params')->get('component.content_types_allowed');
	
		return array_map('trim', explode(',', trim($types)));
	}
	
	public function getIndexContentContentTypes()
	{
		$types = $this->get('params')->get('component.content_types_index_content');
	
		return array_map('trim', explode(',', trim($types)));
	}
	
	public function isAllowedContentType()
	{
		$allowed = false;
		
		$contentType = $this->getContentType();

		$types = $this->getAllowedContentTypes();
	
		while ((($type = current($types)) !== false) && !$allowed) {
			if (preg_match("#".$type."#i", $contentType)) {
				$allowed = true;
			}
	
			next($types);
		}
	
		return $allowed;
			
	}
	
	public function isContentIndexable()
	{
		$allowed = false;
	
		$contentType = $this->getContentType();
		
		$types = $this->getIndexContentContentTypes();
	
		while ((($type = current($types)) !== false) && !$allowed) {
			if (preg_match("#".$type."#i", $contentType)) {
				$allowed = true;
			}
	
			next($types);
		}
	
		return $allowed;
			
	}
}