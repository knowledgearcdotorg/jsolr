<?php
/**
 * @package     JSolr.Plugin
 * @subpackage  Index
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die();

JLoader::import('joomla.log.log');

JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

class PlgJSolrDSpace extends \JSolr\Index\Crawler
{
    protected $context = 'dspace';

    protected $itemContext = 'archive.item';

    protected $assetContext = 'archive.asset';

    protected $collections = array();

    private $connector = null;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        static::$chunk = 50;

        // set some JSpace Crawler specific rules.
        $this->set('bundleExclusions', explode(',', $this->get('params')->get('exclude_bundles_from_index', "")));

        $this->set('contentExclusions', explode(',', $this->get('params')->get('exclude_bundle_content_from_index', "")));
    }

    /**
     * Gets all DSpace items using the JSpace component and DSpace REST API.
     *
     * @return array A list of DSpace items.
     */
    protected function getItems($start = 0, $limit = 20, $query = '*:*')
    {
        $items = array();

        try {
            $items = array();

            $vars = array();

            $vars['q'] = $query;

            $vars['fl'] = 'search.resourceid,search.uniqueid,read';

            $vars['fq'] = 'search.resourcetype:2';

            $vars['rows'] = '2147483647';

            if ($this->get('params')->get('private_access', "") == "") {
                $vars['fq'] .= ' AND read:g0';
            } else {
                // only get items with read set.
                $vars['fq'] .= ' AND read:[* TO *]';
            }

            $vars['fq'] = urlencode($vars['fq']);

            if ($lastModified = JArrayHelper::getValue($this->get('indexingParams'), 'lastModified', null, 'string')) {
                $lastModified = JFactory::getDate($lastModified)->format('Y-m-d\TH:i:s\Z', false);

                $vars['q'] = urlencode("SolrIndexer.lastIndexed:[$lastModified TO NOW]");
            }

            $url = new JUri($this->params->get('rest_url').'/discover.json');

            $url->setQuery($vars);

            $http = JHttpFactory::getHttp();

            $response = $http->get((string)$url);

            if ((int)$response->code !== 200) {
                throw new Exception($response->body, $response->code);
            }

            $response = json_decode($response->body);

            if (isset($response->response->docs)) {
                $items = $response->response->docs;
            }
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'jsolr');
        }

        return $items;
    }

    /**
     * Prepares an article for indexing.
     */
    protected function getDocument(&$record)
    {
        $doc = new \JSolr\Apache\Solr\Document();

        $lang = $this->getLanguage($record, false);

        $doc->addField('handle_s', $record->handle);

        if ($record->name) {
            $doc->addField('title', $record->name);

            $doc->addField("title_sort", $record->name); // for sorting by title
        }

        if ($record->access) {
            $doc->addField('access', $record->access);
        }

        $collection = $this->getCollection($record->collection->id);

        $doc->addField("parent_id", $collection->id);

        $doc->addField("collection_s", $collection->name);

        $doc->addField("collection_fc", $this->getFacet($collection->name));

        $doc->addField("collection_sort", $collection->name);

        $record = $this->getMultilingualDocument($record);

        foreach ($record->metadata as $item) {
            $field = $item->schema.'.'.$item->element;

            if ($item->qualifier) {
                $field .= '.'.$item->qualifier;
            }

            if (array_search($field, $this->get('params')->get('facets', array())) !== false) {
                $doc->addField($field."_fc", $this->getFacet($item->value)); // for faceting
            }

            // Store title based on metadata field language.
            if ($item->element == 'title') {
                $titleLang = $lang;

                if (isset($item->lang)) {
                    $titleLang = $item->lang;
                }

                $doc->addField('title_'.$titleLang, $item->value);
            }

            if ($item->qualifier == 'author') {
                $doc->addField('author', $item->value);
            }

            if ($item->element == 'description') {
                $body = strip_tags($item->value);

                if ($item->qualifier == 'abstract') {
                    $doc->addField("body_$lang", $body);
                } else if (!$item->qualifier && !$doc->getField('body')) {
                    $doc->addField("body_$lang", $body);
                }
            }

            // Any iso language not matching the default will be pushed to lang_alt.
            if ($item->element == 'language' && $item->qualifier == 'iso') {
                if ($item->value != $lang) {
                    $doc->addField('lang_alt', str_replace('_', '-', $item->value));
                }
            }

            // Handle dates carefully then just save out all other field
            // values to generic multi-valued indexing fields.
            if ($item->element == 'date') {
                $doc->addField($field.'_txt', $item->value); // store raw value.

                $value = $item->value;

                // if only a year is given, add a month so it converts correctly.
                if (preg_match("/^\d{4}$/", $item->value) > 0) {
                    $value .= "-01";
                }

                $date = JFactory::getDate($value);

                $value = $date->format('Y-m-d\TH:i:s\Z', false);

                $year = $date->format('Y', false);

                if (array_search($item->qualifier, array('created', 'modified'))) {
                    $name = $item->qualifier;
                } else {
                    $name = $field.'_tdtm';
                }

                $doc->addField($name, $value); // store as an iso date.

                $doc->addField($field.'_year_tim', $year); // store year only.

                if (!$doc->getField($field.'_year_sort')) {
                    $doc->addField($field.'_year_sort', $value); // for sorting
                } else {
                    JLog::add(
                        JText::sprintf(
                            "PLG_JSOLR_DSPACE_WARNING_MULTIDATE_SORT",
                            $record->id,
                            $field),
                        JLog::WARNING,
                        'jsolr');
                }

                if (!$doc->getField($field.'_sort')) {
                    $doc->addField($field.'_sort', $value); // for sorting
                } else {
                    JLog::add(
                        JText::sprintf(
                            "PLG_JSOLR_DSPACE_WARNING_MULTIDATE_SORT",
                            $record->id,
                            $field),
                        JLog::WARNING,
                        'jsolr');
                }
            } else {
                if (isset($item->lang)) {
                    $doc->addField($field.'_'.$item->lang, $item->value); // language-specific indexing.
                }
            }

            if (JString::strlen($item->value) < 32776) {
                $doc->addField($field.'_sm', $item->value); // for (almost) exact matching.
            }
        }

        return $doc;
    }

    /**
     * @todo A bruteforce/messy way to get multilingual information out of DSpace
     * and into JSolr.
     */
    private function getMultilingualDocument($record)
    {
        $languages = array();

        // DSpace handles languages and translations horribly.
        foreach (JLanguageHelper::getLanguages() as $language) {
            $code = $language->lang_code;

            // we need to handle en_US differently in DSpace.
            if (JString::strtolower($code) == 'en-us') {
                $code = 'en_US';
            }

            $languages[] = JString::strtolower(JArrayHelper::getValue(explode("-", $code), 0));
        }

        $fields = array();

        foreach ($languages as $language) {
            foreach ($record->metadata as $item) {
                $field = $item->schema.'.'.$item->element;

                if ($item->qualifier) {
                    $field .= '.'.$item->qualifier;
                }

                $field .= '.'.$language;

                $fields[] = $field;
            }
        }

        $vars = array();

        $vars['q'] = '*:*';

        $vars['fq'] = "(search.resourceid:".$record->id."%20AND%20search.resourcetype:2)";

        $vars['fl'] = implode(',', $fields);

        $url = new JUri($this->params->get('rest_url').'/discover.json');

        $url->setQuery($vars);

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ((int)$response->code !== 200) {
            throw new Exception($response->body, $response->code);
        }

        $response = json_decode($response->body);

        if (isset($response->response->docs)) {
            $document = JArrayHelper::getValue($response->response->docs, 0);

            $document = JArrayHelper::fromObject($document);

            foreach ($fields as $field) {
                if (array_key_exists($field, $document)) {
                    $parts = explode(".", $field);

                    $popped = $parts;

                    array_pop($popped);

                    $popped = implode(".", $popped);

                    foreach (JArrayHelper::getValue($document, $field) as $value) {
                        $found = false;

                        while (($item = current($record->metadata)) && !$found) {
                            $raw = $item->schema.'.'.$item->element;

                            if ($item->qualifier) {
                                $raw .= '.'.$item->qualifier;
                            }

                            if ($popped == $raw) {
                                if ($item->value == $value) {
                                    $key = key($record->metadata);

                                    $record->metadata[$key]->lang = JArrayHelper::getValue($parts, count($parts)-1);

                                    $found = true;
                                }
                            }

                            next($record->metadata);
                        }

                        reset($record->metadata);
                    }
                }
            }
        }

        return $record;
    }

    /**
     * Gets a list of bitstreams for the parent item.
     *
     * @param stdClass $parent The parent Solr item.
     * @return array An array of bitstream objects.
     */
    private function getBitstreams($parent)
    {
        $bundles = array();

        $bitstreams = array();

        $url = new JUri($this->params->get('rest_url').'/items/'.$parent->id.'/bundles.json');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ((int)$response->code !== 200) {
            throw new Exception($response->body, $response->code);
        }

        $bundles = json_decode($response->body);

        $i = 0;

        foreach ($bundles as $bundle) {
            if (in_array($bundle->name, $this->get('bundleExclusions')) === false) {
                foreach ($bundle->bitstreams as $bitstream) {
                    $path = $this->params->get('rest_url').'/bitstreams/'.$bitstream->id.'/download';

                    try {
                        $extractor = \JSolr\Index\Factory::getExtractor($path);

                        if ($extractor->isAllowedContentType()) {
                            $this->out(array($path, "[extracting]"));

                            $indexContent = (
                                    (in_array($bundle->name, $this->get('contentExclusions')) === false) &&
                                    $extractor->isContentIndexable());

                            $bitstreams[$i] = $bitstream;

                            if ($indexContent) {
                                $bitstreams[$i]->body = $extractor->getContent();
                            }

                            $bitstreams[$i]->metadata = $extractor->getMetadata();

                            $bitstreams[$i]->lang = $extractor->getLanguage();

                            $bitstreams[$i]->type = $bundle->name;

                            $this->out(array($path, "[extracted]"));

                            $i++;
                        }
                    } catch (Exception $e) {
                        if ($e->getMessage()) {
                            $this->out($e->getMessage());
                        } else {
                            $code = $e->getCode();
                            $this->out(JText::_("PLG_JSOLR_DSPACE_ERROR_".$code));
                            $this->out(
                                array(
                                    $path,
                                    '[status:'.$code.']'));
                        }
                    }
                }
            }
        }

        return $bitstreams;
    }

    /**
     * Gets a populated instance of the \JSolr\Apache\Solr\Document class containing
     * indexable information about a single bitstream.
     *
     * @param stdClass $record The bitstream information.
     *
     * @return \JSolr\Apache\Solr\Document A populated instance of the
     * \JSolr\Apache\Solr\Document class containing indexable information about
     * the single bitstream.
     */
    private function getBitstreamDocument($record)
    {
        $doc = new \JSolr\Apache\Solr\Document();

        // Make the first language available the bitstream's language.
        $lang = explode('-', JArrayHelper::getValue($record->lang, 0));

        $lang = JArrayHelper::getvalue($lang, 0);

        $doc->addField('id', $record->id);

        $doc->addField('context', $this->get('assetContext'));

        for ($i = 0; $i < count($record->lang); $i++) {
            if ($i == 0) {
                if ($this->get('params')->get('ignore_language', 1)) {
                    $doc->addField('lang', '*');
                } else {
                    $doc->addField('lang', JArrayHelper::getValue($record->lang, $i));
                }
            } else {
                $doc->addField('lang_alt', JArrayHelper::getValue($record->lang, $i));
            }
        }

        $doc->addField('key', $this->get('assetContext').'.'.$record->id);

        if (isset($record->author)) {
            $doc->addField('author', $record->author);

            $doc->addField('author_'.$lang, $record->author);
        }

        $doc->addField('title', $record->name);

        $doc->addField('title_'.$lang, $record->name);

        $doc->addField('type_s', $record->type);

        if (isset($record->body)) {
            if (strip_tags($record->body)) {
                $doc->addField("body_$lang", strip_tags($record->body));
            }
        }

        foreach ($record->metadata->toArray() as $key=>$value) {
            $metakey = $this->cleanBitstreamMetadataKey($key);

            if (is_float($value)) {
                $doc->addField($metakey.'_tfm', $value);
            } elseif (is_int($value)) {
                // handle solr int/long differentiation.
                if ((int)$value > 2147483647 || (int)$value < -2147483648) {
                    $doc->addField($metakey.'_tlm', $value);
                } else {
                    $doc->addField($metakey.'_tim', $value);
                }
            } else {
                $doc->addField($metakey.'_sm', $value);
            }
        }

        return $doc;
    }

    /**
     * (non-PHPdoc)
     * @see \JSolr\Index\Crawler::clean()
     *
     * @TODO quick and dirty clean. This would break on very large indexes.
     */
    protected function clean()
    {
        $items = $this->getItems();

        $service = \JSolr\Index\Factory::getService();

        $query = \JSolr\Search\Factory::getQuery('*:*')
            ->useQueryParser("edismax")
            ->filters(array("context:".$this->get('itemContext')))
            ->retrieveFields('id')
            ->rows(0);

        JLog::add((string)$query, JLog::DEBUG, 'jsolr');

        $results = $query->search();

        if ($results->get('numFound')) {
            $query->rows($results->get('numFound'));
        }

        JLog::add((string)$query, JLog::DEBUG, 'jsolr');

        $results = $query->search();

        if ($results->get('numFound')) {
            $delete = array();

            $prefix = $this->get('itemContext').'.';

            foreach ($results as $result) {
                $found = false;

                reset($items);

                $i = 0;

                while (($item = current($items)) && !$found) {
                    if ($result->id == $item->{'search.resourceid'}) {
                        $found = true;
                    } else {
                        $i++;
                    }

                    next($items);
                }

                if (!$found) {
                    $delete[] = $prefix.$result->id;
                }
            }

            if (count($delete)) {
                foreach ($delete as $key) {
                    $this->out('cleaning item '.$key.' and its bitstreams');

                    $query = 'context:'.$this->get('assetContext').
                        ' AND parent_id:'.str_replace($prefix, '', $key);

                    $service->deleteByQuery($query);
                }

                $service->deleteByMultipleIds($delete);

                $response = $service->commit();
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \JSolr\Index\Crawler::index()
     */
    protected function index()
    {
        $commitWithin = $this->params->get(
                            'component.commitsWithin',
                            '10000');

        $endpoint = $this->params->get('rest_url').'/items/%s.json';

        $total = 0;

        $totalBitstreams = 0;

        $items = $this->getItems();

        $solr = \JSolr\Index\Factory::getService();

        $documents = array();

        foreach ($items as $temp) {
            $total++;

            try {
                $id = $temp->{'search.resourceid'};

                $url = new JUri(JText::sprintf($endpoint, $id));

                $http = JHttpFactory::getHttp();

                $response = $http->get((string)$url);

                if ((int)$response->code !== 200) {
                    throw new Exception($response->body, $response->code);
                }

                $item = json_decode($response->body);

                $item->access = $this->getAccess($temp);

                $document = $this->prepare($item);

                // assuming that when we get a document, it will be the document + n bitstreams.
                // Not a smart calculation, needs to be reworked.
                $totalBitstreams += count($document) - 1;

                $documents = array_merge($documents, $document);
            } catch (Exception $e) {
                if ($e->getCode() == 403) {
                    $this
                        ->out(array('Could not index item '.$id, '[skipping]'))
                        ->out("\tReason:".$e->getMessage());
                    // continue from this kind of error.
                }
            }

            // index when either the number of items retrieved matches
            // the total number of items being indexed or when the
            // index chunk size has been reached.
            if ($total == count($items) || count($documents) >= static::$chunk) {
                $response = $solr->addDocuments($documents, true, $commitWithin);

                $this->out(
                    array(
                        count($documents).' documents successfully indexed',
                        '[status:'.$response->getHttpStatus().']'));

                $documents = array();
            }
        }

        $this->out("items indexed: $total")
             ->out("bitsteams indexed: $totalBitstreams");
    }

    /**
     * A convenience event for adding a record to the index.
     *
     * Use this event when the plugin is known but the context is not.
     *
     * @param int $id The id of the record being added.
     */
    public function onJSolrItemAdd($id)
    {
        $commitWithin = $this->params->get('component.commitWithin', '1000');

        $endpoint = $this->params->get('rest_url').'/items/%s.json';

        try {
            $url = new JUri(JText::sprintf($endpoint, $id));

            $http = JHttpFactory::getHttp();

            $response = $http->get((string)$url);

            if ((int)$response->code !== 200) {
                throw new Exception($response->body, $response->code);
            }

            $item = json_decode($response->body);

            // DSpace is incapable of exposing item permissions in a clean acl
            // manner. Query src Solr for this information.
            $temp = $this->getItems(0, 1, "search.resourceid:".$id);
            $temp = JArrayHelper::getValue($temp, 0);

            $item->access = $this->getAccess($temp);

            $document = $this->prepare($item);

            $solr = \JSolr\Index\Factory::getService();

            $solr->addDocuments($document, true, $commitWithin);
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'jsolr');
        }
    }

    /**
     * A convenience event for adding a record to the index.
     *
     * Use this event when the plugin is known but the context is not.
     *
     * @param int $id The id of the record being added.
     */
    public function onJSolrItemDelete($id)
    {
        $commitWithin = $this->params->get('component.commitWithin', '1000');

        try {
            $solr = \JSolr\Index\Factory::getService();

            $this->out('cleaning item '.$id.' and its bitstreams');

            $query = 'context:'.$this->get('assetContext').
                ' AND parent_id:'.$id;

            $solr->deleteByQuery($query);

            $solr->deleteById($this->get('itemContext').'.'.$id);

            $solr->commit();
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'jsolr');
            JLog::add($e->getTraceAsString(), JLog::ERROR, 'jsolr');
        }
    }

    protected function buildQuery()
    {
        return "";
    }

    private function getAccess($item)
    {
        // g0 = public
        if (array_search('g0', $item->read) !== false) {
            return $this->get('params')->get('anonymous_access', null);
        } else {
            return $this->get('params')->get('private_access', null);
        }
    }

    /**
     * Prepare the item for indexing.
     *
     * @param stdClass $item
     * @return array An array of \JSolr\Apache\Solr\Document objects to be indexed.
     *
     * @todo Need to merge this and the index logic as it is being replicated.
     */
    protected function prepare($item)
    {
        $documents = array();

        $i = 0;

        $documents[$i] = $this->getDocument($item);

        $documents[$i]->addField('id', $this->context.":".$item->id);

        $documents[$i]->addField('context', $this->get('itemContext'));

        if ($this->get('params')->get('ignore_language', 1)) {
            $documents[$i]->addField('lang', '*');
        } else {
            $documents[$i]->addField('lang', $this->getLanguage($item));
        }

        $key = $this->buildKey($documents[$i]);

        $documents[$i]->addField('key', $key);

        $this->out(array('item '.$key, '[queued]'));

        if ($this->get('params')->get('component.index', false)) {
            $bitstreams = $this->getBitstreams($item);

            $j=$i;

            $j++;

            foreach ($bitstreams as $bitstream) {
                $type = strtolower($bitstream->type);

                $documents[$i]->addField($type.'_bitstream_id_tim', $bitstream->id);

                $documents[$i]->addField($type.'_bitstream_title_'.$this->getLanguage($item, false), $bitstream->name);

                if (isset($bitstream->body)) {
                    $documents[$i]->addField($type.'_bitstream_body_'.$this->getLanguage($item, false), strip_tags($bitstream->body));
                }

                foreach ($bitstream->metadata->toArray() as $key=>$value) {
                    $metakey = $this->cleanBitstreamMetadataKey($key);

                    if (is_float($value)) {
                        $documents[$i]->addField($type.'_bitstream_'.$metakey.'_tfm', $value);
                    } elseif (is_int($value)) {
                        // handle solr int/long differentiation.
                        if ((int)$value > 2147483647 || (int)$value < -2147483648) {
                            $documents[$i]->addField($type.'_bitstream_'.$metakey.'_tlm', $value);
                        } else {
                            $documents[$i]->addField($type.'_bitstream_'.$metakey.'_tim', $value);
                        }
                    } else {
                        $documents[$i]->addField($type.'_bitstream_'.$metakey.'_sm', $value);
                    }
                }

                $documents[$j] = $this->getBitstreamDocument($bitstream);

                if ($documents[$i]->getField('created')) {
                    $documents[$j]->addField("created", JArrayHelper::getValue(JArrayHelper::getValue($documents[$i]->getField('created'), 'value'), 0));
                }

                if ($documents[$i]->getField('modified')) {
                    $documents[$j]->addField("modified", JArrayHelper::getValue(JArrayHelper::getValue($documents[$i]->getField('modified'), 'value'), 0));
                }

                $documents[$j]->addField("parent_id", $item->id);

                $key =
                JArrayHelper::getValue(
                        JArrayHelper::getValue(
                                $documents[$j]->getField('key'),
                                'value'),
                        0);

                $this->out(array('bitstream '.$key, '[queued]'));

                $j++;
            }
        }

        return $documents;
    }

    /**
     * Clean metadata key so that it is index friendly.
     * @param string $key The key to clean.
     * @return string The cleaned metadata key.
     */
    private function cleanBitstreamMetadataKey($key)
    {
        $metakey = strtolower($key);

        $metakey = preg_replace("/[^a-z0-9\s\-]/i", "", $metakey);

        $metakey = preg_replace("/[\s\-]/", "_", $metakey);

        return $metakey;
    }

    private function getCollection($id)
    {
        $collection = null;

        if (array_key_exists($id, $this->get('collections'))) {
            $collection = JArrayHelper::getValue($this->get('collections'), $id);
        } else {
            try {
                $url = new JUri($this->params->get('rest_url').'/collections/'.$id.'.json');

                $http = JHttpFactory::getHttp();

                $response = $http->get((string)$url);

                if ((int)$response->code !== 200) {
                    throw new Exception($response->body, $response->code);
                }

                $collection = json_decode($response->body);

                $this->collections[$collection->id] = $collection;
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'jsolr');

                throw $e;
            }
        }
        return $collection;
    }

    public function onListMetadataFields()
    {
        $metadata = array();

        $url = new JUri($this->params->get('rest_url').'/items/metadatafields.json');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ((int)$response->code !== 200) {
            throw new Exception($response->body, $response->code);
        }

        $metadata = json_decode($response->body);

        return $metadata;
    }

    public function getLanguage($item, $includeRegion = true)
    {
        // Grab the first iso code. This will be the record's default
        // language.
        $found = false;

        $lang = parent::getLanguage($item, $includeRegion);

        $metadata = $item->metadata;

        $languages = JLanguageHelper::getLanguages();

        while (($field = current($metadata)) && !$found) {
            $metafield = $field->schema.'.'.$field->element;

            if (isset($field->qualifier)) {
                $metafield .= '.'.$field->qualifier;
            }

            if ($metafield == 'dc.language.iso') {
                while (($language = current($languages)) && !$found) {
                    $code = $language->lang_code;

                    // we need to handle en_US differently in DSpace.
                    if (JString::strtolower($code) == 'en-us') {
                        $code = 'en_US';
                    }

                    if ($code == str_replace('_', '-', $field->value)) {
                        $lang = $code;
                        $found = true;
                    }

                    next($languages);
                }

                reset($languages);
            }

            next($metadata);
        }

        reset($metadata);

        return $lang;
    }
}
