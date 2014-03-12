<?php
/**
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2013 KnowledgeARC Ltd. All rights reserved.
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

jimport('jsolr.factory');

class JSolrIndexFactory extends JSolrFactory 
{
	protected static $component = 'com_jsolrindex';
	
	/**
	 * Gets a file extractor for the file or url provided.
	 * 
	 * @param string $fileOrUrl A file path or url.
	 * 
	 * @return JSolrIndexFilesystemExtractor A sub class of the 
	 * JSolrIndexFilesystemExtractor, based on the JSolr Index component's 
	 * configuration.
	 */
	public static function getExtractor($fileOrUrl)
	{
		$params = JComponentHelper::getParams('com_jsolrindex', true);
		
		$params->loadArray(array('component'=>$params->toArray()));			
		
		$type = JString::ucfirst($params->get('component.extractor'));
		
		jimport('jsolr.index.filesystem.'.$params->get('component.extractor'));
		$class = "JSolrIndexFilesystemExtractor".$type;
		
		return new $class($fileOrUrl);
	}
}