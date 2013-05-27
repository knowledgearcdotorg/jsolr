<?php
/**
 * @package		JSolr.Plugins
 * @subpackage	Index
 * @copyright	Copyright (C) 2012, 2013 Wijiti Pty Ltd. All rights reserved.
 * @license		This file is part of the JSolr Content Index plugin for Joomla!.

   The JSolr Content Index plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr Content Index plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr Content Index plugin for Joomla!.  If not, see 
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

jimport('joomla.factory');
jimport('joomla.database.table');

jimport('jsolr.index.crawler');
jimport('jsolr.helper');

class plgJSolrCrawlerJReviews extends JSolrIndexCrawler
{	
	protected $extension = 'com_jreviews';
	
	protected $view = 'listing';
	
	private $jrFields = array();
	
	private $jrFieldNames = array();
	
	/**
	* Prepares a listing for indexing.
	*/
	protected function getDocument(&$record)
	{	
		$doc = new JSolrApacheSolrDocument();
		
		$created = JFactory::getDate($record->created);
		$modified = JFactory::getDate($record->modified);
		
		$lang = $this->getLanguage($record, false);

		$doc->addField('created', $created->format('Y-m-d\TH:i:s\Z', false));
		$doc->addField('modified', $modified->format('Y-m-d\TH:i:s\Z', false));
		$doc->addField("title", $record->title);	
		$doc->addField("title_$lang", $record->title);

		$doc->addField("title_ac", $record->title); // for auto complete

		$record->summary = JSolrHelper::prepareContent($record->summary, $record->params);
		$record->body = JSolrHelper::prepareContent($record->body, $record->params);

		$doc->addField("body_$lang", strip_tags($record->summary));	
		$doc->addField("body_$lang", strip_tags($record->body));
		
		foreach (explode(',', $record->metakey) as $metakey) {
			$doc->addField("metakeywords_$lang", trim($metakey));
		}
		
		$doc->addField("metadescription_$lang", $record->metadesc);
		$doc->addField("author", $record->author);
		
		$doc->addField("author_fc", $record->author); // for faceting
		$doc->addField("author_ac", $record->author); // for auto complete
		
		foreach (JSolrHelper::getTags($record, array("<h1>")) as $item) {
			$doc->addField("tags_h1_$lang", $item);
		}

		foreach (JSolrHelper::getTags($record, array("<h2>", "<h3>")) as $item) {
			$doc->addField("tags_h2_h3_$lang", $item);
		}
		
		foreach (JSolrHelper::getTags($record, array("<h4>", "<h5>", "<h6>")) as $item) {
			$doc->addField("tags_h4_h5_h6_$lang", $item);
		}
		
		$doc->addField("hits_i", (int)$record->hits);
		
		if ($record->catid) {
			$doc->addField("parent_id", $record->catid);
			$doc->addField("category_$lang", $record->category);
			$doc->addField("category_fc", $record->category); // for faceting
		}
		
		if (isset($record->images)) {
			$parts = explode("|||", $record->images);
			
			if ($image = JArrayHelper::getValue($parts, 0)) {
				$doc->addField("image_s", $image);
			}
		}

		if (isset($record->user_rating)) {
			$doc->addField("user_rating_tf", $record->user_rating);
		}

		if (isset($record->user_rating_count)) {
			$doc->addField("user_rating_count_i", $record->user_rating_count);
		}

		if (isset($record->user_criteria_rating)) {
			$doc->addField("user_criteria_rating_tf", $record->user_criteria_rating);
		}
		
		if (isset($record->user_criteria_rating_count)) {
			$doc->addField("user_criteria_rating_count_i", $record->user_criteria_rating_count);
		}
		
		if (isset($record->review_count)) {
			$doc->addField("review_count_i", $record->review_count);
		}
		
		if (isset($record->editor_rating)) {
			$doc->addField("editor_rating_tf", $record->editor_rating);
		}
		
		if (isset($record->editor_rating_count)) {
			$doc->addField("editor_rating_count_i", $record->editor_rating_count);
		}
		
		if (isset($record->editor_criteria_rating)) {
			$doc->addField("editor_criteria_rating_tf", $record->editor_criteria_rating);
		}
		
		if (isset($record->editor_criteria_rating_count)) {
			$doc->addField("editor_criteria_rating_count_s", $record->editor_criteria_rating_count);
		}
		
		if (isset($record->editor_review_count)) {
			$doc->addField("editor_review_count_s", $record->editor_review_count);
		}

		if (isset($record->favorites)) {
			$doc->addField("favorites_i", $record->favorites);
		}
		
		// Obtain the configured fields to index.		
		if (array_search('jsolr_all', $this->params->def('index_fields')) === false) {
			$indexes = $this->params->def('index_fields');
		} else {
			$indexes = $this->_getJRFields();
		}
		
		foreach ($indexes as $index) {
			$key = JString::strtolower(JStringNormalise::toVariable($index));
			
			switch ($this->_getJRField($index)->type) {
				case 'checkboxes':
				case 'selectmultiple':
				case 'radiobuttons':
					foreach (explode('*', $record->$index) as $value) {
						if (!empty($value))
							$doc->addField($key.'_s_multi', $value);
					}
									
					break;

				case 'integer':
					$doc->addField($key.'_i', (int)$record->$index);
					
					break;
					
				default:
					$doc->addField($key.'_s', $record->$index); // for faceting
					break;
			}
		}

		// Obtain the configured fields to index.		
		if (array_search('jsolr_all', $this->params->def('facet_fields')) === false) {
			$facets = $this->params->def('facet_fields');
		} else {
			$facets = $this->_getJRFields();
		}
		
		// Obtain the configured fields to facet.
		foreach ($facets as $facet) {
			$key = JString::strtolower(JStringNormalise::toVariable($facet));

			switch ($this->_getJRField($facet)->type) {
				case 'checkboxes':
				case 'selectmultiple':
				case 'radiobuttons':
					foreach (explode('*', rtrim(ltrim($record->$facet,'*'),'*')) as $value) {
						if (!empty($value))
							$doc->addField($key.'_fc', $value);
					}
					
					break;
					
				default:
					if (!empty($record->$facet)) {
						$doc->addField($key.'_fc', $record->$facet); // for faceting
					}
					
					break;
			}
		}

		return $doc;
	}
	
	private function _getJRFields()
	{
		if (!$this->jrFieldNames) {
			// Create a new query object.
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			$user	= JFactory::getUser();
	
			// Select extra fields from jreviews content.
			$query
				->select('jf.name')
				->from('#__jreviews_fields AS jf')
				->where("location='content'");
				
			$db->setQuery($query);
		
			$this->jrFieldNames = $db->loadColumn();
		}
		
		return $this->jrFieldNames;
	}
	
	/**
	 * A convenince method for reducing the amount of requests the db. 
	 * 
	 * @param string $field
	 */
	private function _getJRField($field)
	{
		if (!JArrayHelper::getValue($this->jrFields, $field, null)) {
			// Create a new query object.
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			$user	= JFactory::getUser();
	
			// Select extra fields from jreviews content.
			$query
				->select('jf.*')
				->from('#__jreviews_fields AS jf')
				->where("jf.name='".$field."'");
				
			$db->setQuery($query);
				
			$this->jrFields[$field] = $db->loadObject();
		}
		
		return JArrayHelper::getValue($this->jrFields, $field, null); 
	}
	
	// @todo This method is adapted from the com_finder preparecontent method 
	// but it doesn't really do anything (loadmodule and loadposition still 
	// appear in the content even though they should be parsed out).
	// Currently, it is assumed that this method handles other content manipulation 
	// such as BBCode (used by certain 3rd party plugins to add complex javascript, 
	// css and html to an article.
	// Instead, this method should do more to clear out the markup including module 
	// loading and other 3rd party content manipulation plugins.
	public static function prepareContent($text, $params = null)
    {
		static $loaded;
		
		// Get the dispatcher.
		$dispatcher = JDispatcher::getInstance();

		// Load the content plugins if necessary.
		if (empty($loaded)) {
			JPluginHelper::importPlugin('content');
			$loaded = true;
		}

		// Instantiate the parameter object if necessary.
		if (!($params instanceof JRegistry)) {
			$registry = new JRegistry;
			$registry->loadString($params);
			$params = $registry;
		}

		// Create a mock content object.
		$content = JTable::getInstance('Content');
		$content->text = $text;

		// Fire the onContentPrepare event with the com_finder context to avoid 
		// errors with loadmodule/loadposition plugins.
		$dispatcher->trigger('onContentPrepare', array('com_finder.indexer', &$content, &$params, 0));
 
		return $content->text;
	}
	
	/**
	 * Updates the index after an item is saved.
	 * 
	 * @param string $context The context of the item (com_jreviews.listing).
	 * @param mixed $item The item to index.
	 */
	public function onJSolrIndexAfterSave($context, $item)
	{
		if ($context == 'com_jreviews.listing') {
			$listing = JArrayHelper::getValue($item->data, 'Listing');
			$fields = JArrayHelper::getValue($item->data, 'Field');

			// flatten the array.
			$array = array_merge($listing, JArrayHelper::getValue($fields, 'Listing'));
			
			foreach ($array as $key=>$value) {
				if (JString::strpos('jr_', $value) !== 0) {
					if (is_array($value)) {
						$array[$key] = '*'.implode('*', $value).'*';			
					}
				}
			}
			
			if ($id = JArrayHelper::getValue($item->data, 'insertid', 0)) {		
				// Load the category title.
				$category = JTable::getInstance('Category');
				$category->load(JArrayHelper::getValue($array, 'catid'));
				
				$array['category'] = $category->get('title');
				
				// Load the user name.
				$array['author'] = JFactory::getUser(JArrayHelper::getValue($array, 'created_by'))->get('name');
				
				// rename some keys.
				$array['summary'] = JArrayHelper::getValue($array, 'introtext');
				unset($array['introtext']);
				
				$array['body'] = JArrayHelper::getValue($array, 'fulltext');
				unset($array['fulltext']);
				
				// Add some mandatory field values if they don't already exist.
				$array['modified'] = JArrayHelper::getValue(
					$array, 
					'modified', 
					JArrayHelper::getValue($array, 'created')
				);
				
				$array['hits'] = JArrayHelper::getValue($array, 'hits', 0);
				
				$array['params'] = JArrayHelper::getValue($array, 'attribs', null);
				
				$array['id'] = $id;
				
				$object = JArrayHelper::toObject($array);
			} else {
				// JReviews does not pass through the entire record (it only 
				// updates a subset of fields) and the database table cannot 
				// be relied upon for the most up-to-date record. 
				// Therefore we need to load the entire record then update 
				// fields available in the passed object. 
				$query = $this->buildQuery()->where('a.id='.JArrayHelper::getValue($listing, 'id', 0));

				$database = JFactory::getDBO();
				$database->setQuery($query);

				$object = $database->loadObject();

				foreach ($array as $key=>$value) {
					if (property_exists($object, $key)) {
						$object->$key = $value;
					}
				}
			}
	
			try {
				// Throw an error if the record has no id.
				if ($object->id == 0) {
					throw new Exception('Could not find item id. Cannot index.');
				}
				
				$document = $this->prepare($object);

				$solr = JSolrIndexFactory::getService();

				$commitWithin = JSolrIndexFactory::getConfig()->get('commitWithin', 0);
				
				$solr->addDocument($document, false, true, true, $commitWithin);	

				// if no commitWithin time is set and autocommit is not 
				// configured, need to commit manually. 
				if (!($commitWithin || JSolrIndexFactory::getConfig()->get('autocommit', 0))) {
					$solr->commit();
				}
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'jsolr');
			}
		}
	}
	
	/**
	 * Deletes the item from the index after it has been successfully deleted 
	 * from the JReviews database.
	 * 
	 * @param string $context The context of the item (com_jreviews.listing).
	 * @param mixed $item The item to index.
	 */
	public function onJSolrIndexAfterDelete($context, $item)
	{
		if ($context == 'com_jreviews.listing') {
			$listing = JArrayHelper::getValue($item->data, 'Listing');
			$id = JArrayHelper::getValue($listing, 'id');
			
			try {
				if (!$id) {
					throw new Exception('No id exists for this item.');
				}
				
				$solr = JSolrIndexFactory::getService();
				$solr->deleteById($this->get('extension').'.'.$this->get('view').'.'.$id);
				$solr->commit();
			} catch (Exception $e) {
				JLog::add($e->getMessage(), JLog::ERROR, 'jsolr');
			}
		}
	}

	protected function buildQuery()
	{
		// Create a new query object.
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();

		// Select extra fields from jreviews content.
		$query->select('jc.contentid, jc.*');
		$query->from('#__jreviews_content AS jc');

		// Select the required fields from content.
		$query->select('a.id, a.title, a.alias, a.introtext AS summary, a.fulltext AS body');
		$query->select('a.state, a.catid, a.created, a.created_by, a.hits');
		$query->select('a.created_by_alias, a.modified, a.modified_by, a.attribs AS params');
		$query->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.version, a.ordering');
		$query->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date, a.images');
		
		$query->join('LEFT', '#__content AS a ON jc.contentid=a.id');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the categories.
		$query->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author.
		$query->select('ua.name AS author');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		
		$query->select('user_rating', 'user_rating_count', 'user_criteria_rating', 'user_criteria_rating_count', 'review_count', 'editor_rating', 'editor_rating_count', 'editor_criteria_rating', 'editor_criteria_rating_count', 'editor_review_count');
		$query->join('LEFT', '#__jreviews_listing_totals AS lt ON lt.listing_id = jc.contentid');

		$query->select('COUNT(f.favorite_id) AS favorites');
		$query->join('LEFT', '#__jreviews_favorites AS f ON f.content_id = jc.contentid');
		$query->group('jc.contentid');
		
		$conditions = array();

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$conditions[] = 'a.access IN ('.$groups.')';
		}

		$categories = $this->params->get('categories');

		if (is_array($categories)) {
			if (JArrayHelper::getValue($categories, 0) != 0) {
				JArrayHelper::toInteger($categories);
				$categories = implode(',', $categories);
				$conditions[] = 'a.catid IN ('.$categories.')';
			}
		}

		if ($lastModified = JArrayHelper::getValue($this->get('indexOptions'), 'lastModified', null, 'string')) {
			$lastModified = JFactory::getDate($lastModified);
		
			$conditions[] = "(a.created > '".$lastModified."' OR a.modified > '".$lastModified."')";
		}
		
		if (count($conditions)) {
			$query->where($conditions);
		}

		return $query;
	}
}