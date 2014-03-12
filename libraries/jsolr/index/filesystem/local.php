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
jimport('jsolr.index.filesystem.extractor');

/**
 * A class for extracting metadata, content and other information from a file.
 */
class JSolrIndexFilesystemExtractorLocal extends JSolrIndexFilesystemExtractor
{	
	public function getContentType()
	{
		if (!$this->get('contentType')) {	
			$result = $this->_extract('-d');

			// sometimes the charset is appended to the file type.
			$this->set('contentType', JArrayHelper::getValue(array_map('trim', explode(';', trim($result))), 0));
		}
		
		return $this->get('contentType');
	}
	
	/**
	 * Gets an array of languages associated with this document.
	 * 
	 * In many cases only a two-letter iso language (iso639) is attached to the 
	 * document. However, JSolr supports both language (iso639) and region 
	 * (iso3166), E.g. en-AU.
	 * This method will attempt to match all the language codes with their 
	 * language+region counterparts so it is possible that more than one code 
	 * will be returned for the document.
	 * 
	 * @return array An array of languages associated with the document.
	 */
	public function getLanguage()
	{
		if (!$this->get('language')) {
			$result = $this->_extract('-l');

			$results = explode("\n", str_replace("\r", "\n", $result));
			
			$array = array();
			
			foreach ($results as $value) {
				if ($value) {
					if (JString::strlen($value) == 5) { // assume iso with region
						$array[] = str_replace('_', '-', $value);
					} elseif (JString::strlen($value) == 2) { // assume iso without region
						$found = false;
						$languages = JLanguageHelper::getLanguages();
						
						while (($language = current($languages)) && !$found) {
							$parts = explode('-', $language->lang_code);
							if ($value == JArrayHelper::getValue($parts, 0)) {
								if (array_search($language->lang_code, $array) === false) {
									$array[] = $language->lang_code;
								}
								
								$found = true;
							}
							
							next($languages);
						}
						
						reset($languages);
					}
				}				
			}
			
			// if no languages could be detected, use the system lang.
			if (!count($array)) {
				$array[] = JLanguage::getInstance()->getTag();
			}

			$this->set('language', $array);
		}
		
		return $this->get('language');
	}
	
	public function getContent()
	{
		if (!$this->get('content')) {
			$result = $this->_extract('');
			
			$this->set('content', $result);
		}
		
		return $this->get('content');
	}
	
	public function getMetadata()
	{
		if (!$this->get('metadata')) {
			$result = $this->_extract('-j');

			$metadata = new JRegistry();
			$metadata->loadString($result);
			
			$this->set('metadata', $metadata);
		}
		
		return $this->get('metadata');
	}
	
	private function _extract($flags)
	{
		ob_start();
		passthru("java -jar ".$this->get('params')->get('component.local_tika_app_path')." ".$flags." ".$this->get('pathOrUrl')." 2> /dev/null");
		$result = ob_get_contents();
		ob_end_clean();
		
		return $result;
	}
}