<?php
/**
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Index;

use \JComponentHelper as JComponentHelper;
use \JLog as JLog;
use \JFactory as JFactory;
use \JArrayHelper as JArrayHelper;

/**
 * An abstract class which all other crawler classes should derive from.
 */
abstract class Crawler extends \JPlugin
{
    const STDOUT_SEPARATOR_WIDTH = 90;

    protected static $chunk;

    /**
     * The context of the document being indexed.
     *
     * @var string
     */
    protected $context;

    /**
     * True if verbose messaging should be output, false otherwise.
     *
     * @var bool
     */
    protected $verbose = false;

    /**
     * The class name of the calling object.
     * @var string
     */
    protected $caller;

    /**
     * An array of additional params that the crawler may require to
     * complete its tasks.
     * @var array
     */
    protected $indexingParams = array();

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();

        self::$chunk = 1000;

        // load the jsolrindex component's params into plugin params for
        // easy access.
        $params = JComponentHelper::getParams('com_jsolrindex', true);

        $this->params->loadArray(array('component'=>$params->toArray()));

        Jlog::addLogger(array());
    }

    /**
     * Prepares an article for indexing.
     */
    protected abstract function getDocument(&$record);

    /**
     * Get's the language, either from the item or from the Joomla environment.
     *
     * @param JObject $item The item being indexed.
     * @param bool $includeRegion True if the region should be included, false
     * otherwise. E.g. If true, en-AU would be returned, if false, just en
     * would be returned.
     *
     *  @return string The language code.
     */
    protected function getLanguage($item, $includeRegion = true)
    {
        if (isset($item->language) && $item->language != '*') {
            $lang = $item->language;
        } else {
            $lang = JFactory::getLanguage()->getDefault();
        }

        if ($includeRegion) {
            return $lang;
        } else {
            $parts = explode('-', $lang);

            // just return the xx part of the xx-XX language.
            return JArrayHelper::getValue($parts, 0);
        }
    }

    /**
     *
     *
     * @return JDatabaseQuery A database query.
     */
    abstract protected function buildQuery();

    protected function getItems($start, $limit)
    {
        $database = JFactory::getDBO();
        $database->setQuery($this->buildQuery(), $start, $limit);

        return $database->loadObjectList();
    }

    protected function getItemCount()
    {
        $database = JFactory::getDBO();
        $database->setQuery($this->buildQuery());
        $database->getQuery()->clear('select')->clear('group')->select('COUNT(*)');

        return (int)$database->loadResult();
    }

    /**
     * Builds the item's index key.
     *
     * Takes the form extension.view.id, E.g. com_content.article.1.
     *
     * The key can be customized by overriding this method but it is not
     * recommended.
     *
     * @param \JSolr\Apache\Solr\Document $document The document to use to build the
     * key.
     *
     * @return string The item's key.
     */
    protected function buildKey($document)
    {
        $type = $document->getField('context');
        $type = JArrayHelper::getValue($type, 'value');
        $type = JArrayHelper::getValue($type, 0);
        $id = $document->getField('id');
        $id = JArrayHelper::getValue($id, 'value');
        $id = JArrayHelper::getValue($id, 0);
        return $type.'.'.$id;
    }

    /**
     * Fires the cleaning event.
     *
     * Derived classes should override the clean() method when implementing
     * a custom clean task.
     *
     * @params string caller The calling class.
     * @params bool verbose True if verbose messaging should be enabled, false
     * otherwise. The default is false.
     * @params array indexingParams A list of options to control various aspects of
     * the clean task.
     */
    public function onClean($caller, $verbose = false, $indexingParams = array())
    {
        $this->set('caller', $caller);
        $this->set('verbose', $verbose);
        $this->set('indexingParams', $indexingParams);

        $this->out(array("task:clean extension:".$this->get('context'),"[starting]"));

        $this->clean();

        $this->out(array("task:clean extension:".$this->get('extension'),"[completed]"));
    }

    /**
     * Fires the indexing event.
     *
     * Derived classes should override the index() method when implementing
     * a custom index task.
     *
     * @params string caller The calling class.
     * @params bool verbose True if verbose messaging should be enabled, false
     * otherwise. The default is false.
     * @params array indexingParams A list of options to control various aspects of
     * the clean task.
     */
    public function onIndex($caller, $verbose = false, $indexingParams = array())
    {
        $this->set('caller', $caller);
        $this->set('verbose', $verbose);
        $this->set('indexingParams', $indexingParams);

        $this->out(array("task:index crawler:".$this->get('context'),"[starting]"));

        $this->index();

        $this->out(array("task:index crawler:".$this->get('context'),"[completed]"));
    }

    /**
     * Fires the purging event.
     *
     * Derived classes should override the purge() method when implementing
     * a custom purge task.
     *
     * @params string caller The calling class.
     * @params bool verbose True if verbose messaging should be enabled, false
     * otherwise. The default is false.
     * @params array indexingParams A list of options to control various aspects of
     * the clean task.
     */
    public function onPurge($caller, $verbose = false, $indexingParams = array())
    {
        $this->set('caller', $caller);
        $this->set('verbose', $verbose);
        $this->set('indexingParams', $indexingParams);

        $this->out(array('task;purge type;'.$this->get('context'),'[starting]'));

        $this->purge();

        $this->out(array('task;purge type;'.$this->get('context'),'[completed]'));
    }

    /**
     *
     * @param string $context The context of the item being saved.
     * @param mixed $item The item being deleted (must have an id property).
     * @param bool $isNew True if the item is new, false otherwise.
     */
    public function onJSolrIndexAfterSave($context, $item, $isNew)
    {
        if ($context == $this->get('context')) {
            $query = $this->buildQuery()->where('a.id='.$item->id);

            $database = JFactory::getDBO();
            $database->setQuery($query);

            $document = $this->prepare($database->loadObject());

            try {
                $solr = \JSolr\Index\Factory::getService();

                $solr->addDocument($document, false, true, true, $this->params->get('component.commitWithin', '1000'));
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'jsolrcrawler');
            }
        }
    }

    /**
     * Triggers an event to delete an indexed item.
     *
     * @param string $context The context of the item being deleted.
     * @param mixed $item The item being deleted (must have an id property).
     */
    public function onJSolrIndexAfterDelete($context, $item)
    {
        try {
            if ($context == $this->get('context')) {
                $this->deleteItem($this->get('context').'.'.$item->id);
            }
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'jsolrcrawler');
        }
    }

    /**
     * A convenience event for adding a record to the index.
     *
     * Use this event when the plugin is known but the context is not.
     *
     * @param int $id The id of the record being added.
     */
    public function onItemAdd($id)
    {
        $item = new stdClass();
        $item->id = $id;

        $this->onJSolrIndexAfterSave($this->get('context'), $item, true);
    }

    /**
     * A convenience event for deleting an indexed item.
     *
     * Use this event when the plugin is known but the context is not.
     *
     * @param int $id The id of the item being deleted.
     */
    public function onItemDelete($id)
    {
        $item = new stdClass();
        $item->id = $id;

        $this->onJSolrIndexAfterDelete($this->get('context'), $item);
    }

    /**
     * Cleans deleted items from the index.
     *
     * Derived classes should override this method when implementing a custom
     * clean operation.
     */
    protected function clean()
    {

    }

    /**
     * Adds items to/edits existing items in the index.
     *
     * Derived classes should override this method when implementing a custom
     * index operation.
     */
    protected function index()
    {
        $itemTotal = $this->getItemCount();
        $start = 0;
        $limit = 1000;
        $total = 0;

        while ($start < $itemTotal)
        {
            $items = $this->getItems($start, $limit);

            $solr = \JSolr\Index\Factory::getService();

            if (is_array($items)) {
                $documents = array();
                $i = 0;

                foreach ($items as $item) {
                    $total++;
                    $documents[$i] = $this->prepare($item);

                    $this->out('document '.$this->buildKey($documents[$i]).' ready for indexing');

                    $i++;

                    // index when either the number of items retrieved matches
                    // the total number of items being indexed or when the
                    // index chunk size has been reached.
                    if ($total == count($items) || $i >= self::$chunk) {
                        $response = $solr->addDocuments($documents, false, true, true, $this->params->get('component.commitsWithin', '10000'));

                        $this->out(array($i.' documents successfully indexed', '[status:'.$response->getHttpStatus().']'));

                        $documents = array();
                        $i = 0;
                    }
                }
            }

            $start+=$limit;
        }

        $this->out("items indexed: $total");
    }

    /**
     * Permanently removes all items in the index which are managed by the
     * associated plugin..
     *
     * Derived classes should override this method when implementing a custom
     * purge operation.
     */
    protected function purge()
    {
        $solr = \JSolr\Index\Factory::getService();
        $solr->deleteByQuery("context:".$this->get('context').'*');
        $solr->commit();
    }

    /**
     * Deletes a single item from the index.
     *
     * @param string $key The unique key of the item to delete.
     */
    protected function deleteItem($key)
    {
        $solr = \JSolr\Index\Factory::getService();
        $solr->deleteById($key);
        $solr->commit();
    }

    /**
     * Prepare the item for indexing.
     *
     * @param stdClass $item
     * @return \JSolr\Apache\Solr\Document
     */
    protected function prepare($item)
    {
        // Initialize the item's parameters.
        if (isset($item->params)) {
            $registry = new \Joomla\Registry\Registry();
            $registry->loadString($item->params);
        }

        if (isset($item->metadata)) {
            $registry = new \Joomla\Registry\Registry();
            $registry->loadString($item->metadata);
            $item->metadata = $registry;
        }

        $document = $this->getDocument($item);
        $document->addField('id', $item->id);
        $document->addField('context', $this->get('context'));
        $document->addField('lang', $item->language);

        $key = $this->buildKey($document);

        $document->addField('key', $key);

        return $document;
    }

    /**
     * Command line formmatted output.
     *
     * @param mixed $text String or array.
     * To provide a description with a message, E.g.
     *
     * indexing                [started]
     *
     * pass a 2 dimensional array; array('indexing', '[started]');
     *
     * @param bool $nl True if a new line character should be appended, false
     * otherwise. The default is true.
     * @return \JSolr\Index\Crawler Returns $this for chaining output.
     */
    protected function out($text = '', $nl = true)
    {
        if ($this->get('caller') == 'JSolrCrawlerCli') {
            if ($this->get('verbose')) {
                if (is_array($text)) {
                    if (count($text) == 2) {
                        $length = self::STDOUT_SEPARATOR_WIDTH - (strlen($text[0]) + strlen($text[1]));
                        $text = implode(str_repeat(' ', ($length > 0) ? $length : 1), $text);
                    } else if (count($text) > 2) {
                        $text = implode(' ', $text);
                    } else {
                        $text = implode('', $text);
                    }
                }

                fwrite(STDOUT, $text . ($nl ? "\n" : null));
            }
        }

        return $this;
    }

    /**
     * Gets a formatted facet based on the JSolrIndex configuration.
     *
     * @param string $facet
     *
     * @return string A formatted facet based on the JSolrIndex configuration.
     */
    protected function getFacet($facet)
    {
        switch (intval($this->params->get('component.casesensitivity'))) {
            case 1:
                return JString::strtolower($facet);
                break;

            case 2:
                return \JSolr\Helper::toCaseInsensitiveFacet($facet);
                break;

            default:
                return $facet;
                break;
        }
    }
}
