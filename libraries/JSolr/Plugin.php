<?php
/**
 * An abstract class which all other classes should drive from.
 *
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr;

use \JComponentHelper as JComponentHelper;
use \JLog as JLog;
use \JFactory as JFactory;
use \Joomla\Utilities\ArrayHelper;

use Solarium\Plugin\BufferedAdd\Event\Events;
use Solarium\Plugin\BufferedAdd\Event\PostCommit as PostCommitEvent;

/**
 * An abstract class which all other classes should derive from.
 */
abstract class Plugin extends \JPlugin
{
    const STDOUT_SEPARATOR_WIDTH = 80;

    const GLOBAL_QUERY_FIELD = "_text_";

    protected $autoloadLanguage = true;

    /**
     * The context of the document being indexed.
     *
     * @var string
     */
    protected $context;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        // load the jsolr component's params into plugin params for
        // easy access.
        $params = JComponentHelper::getParams('com_jsolr', true);

        $this->params->loadArray(array('component'=>$params->toArray()));

        Jlog::addLogger(array());
    }

    /**
     * Get's the language, either from the source language or from the Joomla
     * environment.
     *
     * @param  string  $language       The source language. Must include locale.
     * @param  bool    $includeRegion  True if the region should be included,
     * false otherwise. E.g. If true, en-AU would be returned, if false, just
     * en would be returned.
     *
     *  @return string The language code.
     */
    protected function getLanguage($language, $includeRegion = true)
    {
        if (\JLanguage::exists($language)) {
            $lang = $language;
        } else {
            $lang = JFactory::getLanguage()->getDefault();
        }

        if ($includeRegion) {
            return $lang;
        } else {
            $parts = explode('-', $lang);

            // just return the xx part of the xx-XX language.
            return ArrayHelper::getValue($parts, 0);
        }
    }

    /**
     * Builds the item's index key.
     *
     * Takes the form extension.view.id, E.g. com_content.article.1.
     *
     * The key can be customized by overriding this method but it is not
     * recommended.
     *
     * @param   int  $id  The document to use to build the key.
     *
     * @return  string  The item's id.
     */
    protected function buildId($id)
    {
        return $this->get('context').'.'.$id;
    }

    /**
     * Fires the indexing event.
     *
     * Derived classes should override the index() method when implementing
     * a custom index task.
     */
    public function onJSolrIndex()
    {
        $this->out(array("task:index crawler:".$this->get('context'),"[starting]"), \JLog::DEBUG);

        try {
            $this->index();
        } catch (\Exception $e) {
            // catch catastrophic errors that can't be recovered from.
            $this->out(array("HALTING:".$this->get('context')."\n".(string)$e), \JLog::CRITICAL);
        }

        $this->out(array("task:index crawler:".$this->get('context'),"[completed]"), \JLog::DEBUG);
    }

    /**
     * Fires the purging event.
     *
     * Derived classes should override the purge() method when implementing
     * a custom purge task.
     */
    public function onJSolrPurge()
    {
        $this->purge();
    }

    /**
     * Adds items to/edits existing items in the index.
     *
     * Derived classes should override this method when implementing a custom
     * index operation.
     */
    protected function index()
    {
        $start = 0;
        $limit = $this->params->get('component.commitLimit', 1000);
        $total = $this->getTotal();

        $client = \JSolr\Index\Factory::getClient();

        $buffer = $client->getPlugin('bufferedadd');
        $buffer->setBufferSize($limit);

        // also register event hooks to display what is happening
        $client->getEventDispatcher()->addListener(
            Events::POST_COMMIT,
            function (\Symfony\Component\EventDispatcher\Event $event) {
                $total = count($event->getResult());
                $this->out("items indexed: $total", \JLog::DEBUG);
            }
        );

        $client->getEventDispatcher()->addListener(
            Events::ADD_DOCUMENT,
            function (\Symfony\Component\EventDispatcher\Event $event) {
                $this->out('document '.$event->getDocument()->id.' ready for indexing', \JLog::DEBUG);
            }
        );

        $client->getEventDispatcher()->addListener(
            Events::PRE_FLUSH,
            function (\Symfony\Component\EventDispatcher\Event $event) {
                $this->out('flushing buffer ('.count($event->getBuffer()).' documents)', \JLog::DEBUG);
            }
        );

        while ($start < $total) {
            $items = $this->getItems($start, $limit);

            $documents = array();

            try {
                foreach ($items as $item) {
                    $array = $this->prepare($item);

                    if (!empty($array)) {
                        $array = $this->prepareGlobalQueryField($array);
                        $documents[] = $buffer->createDocument($array);
                    } else {
                        $this->out('document is empty, ignoring...', \JLog::WARNING);
                    }

                    $start++;
                }
            } catch (Exception $e) {
                $this->out($e->getMessage(), \JLog::ERROR);
            }

            $documents = array();
        }

        $buffer->commit();
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
        $client = \JSolr\Index\Factory::getClient();
        $update = $client->createUpdate();

        $update->addDeleteQuery("context:".$this->get('context'));
        $update->addCommit();

        $result = $client->update($update);
    }

    /**
     * Gets the total number of items to index.
     *
     * @return  int  The total number of items to index.
     */
    protected abstract function getTotal();

    /**
     * Gets the items to be indexed.
     *
     * @param   int    $start
     * @param   int    $limit
     *
     * @return  array  The items to index.
     */
    protected abstract function getItems($start = 0, $limit = 10);

    /**
     * Prepare the item for indexing.
     *
     * @param StdClass $item
     * @return \JSolr\Apache\Solr\Document
     */
    protected abstract function prepare($item);

    /**
     * Command line formmatted output.
     *
     * @param  mixed  $text String or array.
     * To provide a description with a message, E.g.
     *
     * indexing                [started]
     *
     * pass a 2 dimensional array; array('indexing', '[started]');
     *
     * @param  bool   $level The log level. Use the JLog constants; \JLog::DEBUG, \JLog::ERROR, etc.
     */
    protected function out($text, $level)
    {
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

        \JSolrHelper::log($text, $level);
    }

    /**
     * Gets a formatted facet based on the JSolrIndex configuration.
     *
     * @param   string  $facet
     *
     * @return  string  A formatted facet based on the JSolrIndex configuration.
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

    /**
     *
     * @param string    $context  The context of the item being saved.
     * @param StdClass  $item     The item being deleted (must have an id property).
     * @param bool      $isNew    True if the item is new, false otherwise.
     */
    public function onJSolrAfterSave($context, $item, $isNew)
    {
        if ($context == $this->get('context')) {
            $commitWithin = $this->params->get('component.commitWithin', '1000');

            $client = \JSolr\Index\Factory::getClient();
            $update = $client->createUpdate();

            // some Joomla items have published instead of state.
            if (!isset($item->state)) {
                $item->state = $item->published;
            }

            if ((int)$item->state == 1) {
                $array = $this->prepare($item);

                $catid = ArrayHelper::getValue($array, 'parent_id_i');

                if ($this->isCategoryIndexable($catid)) {
                    $document = $update->createDocument($array);
                    $update->addDocument($document, null, $commitWithin);
                }
            } else {
                $dispatcher = \JDispatcher::getInstance();
                \JPluginHelper::importPlugin('jsolr');

                $results = $dispatcher->trigger('onJSolrAfterDelete', array($context, $item));
            }

            $update->addCommit();

            $result = $client->update($update);
        }
    }

    /**
     * Triggers an event to delete an indexed item.
     *
     * @param  string  $context  The context of the item being deleted.
     * @param  mixed   $item     The item being deleted (must have an id property).
     */
    public function onJSolrAfterDelete($context, $item)
    {
        if ($context == $this->get('context')) {
            $client = \JSolr\Index\Factory::getClient();
            $update = $client->createUpdate();

            $update->addDeleteQuery("id:".$this->get('context').'.'.$item->id);
            $update->addCommit();

            $result = $client->update($update);
        }
    }

    /**
     * Triggers an event manage an indexed item when there is a state change.
     *
     * @param  string  $context  The context of the changed items.
     * @param  array   $pks      An array of ids of the chnaged items.
     * @param  int     $value    The new state.
     */
    public function onJSolrChangeState($context, $pks, $value)
    {
        if ($context == $this->get('context')) {
            $dispatcher = \JDispatcher::getInstance();
            \JPluginHelper::importPlugin('jsolr');

            foreach ($pks as $pk) {
                // dummy item for passing to respective event.
                $item = new \StdClass();
                $item->id = $pk;
                $item->state = $value;

                $results = $dispatcher->trigger('onJSolrAfterSave', array($context, $item, false));
            }
        }
    }

    /**
     * Check whether a category has been configured for indexing.
     *
     * If no categories have been set, all items will be indexed.
     *
     * @param   int   $catid  The category to check.
     *
     * @return  bool  True if the category is found, false otherwise.
     */
    protected function isCategoryIndexable($catid)
    {
        $catids = $this->params->get('categories', array());

        if (($pos = array_search(1, $catids)) !== false) {
            unset($catids[$pos]);
        }

        if (count($catids)) {
            return in_array($catid, $catids, true);
        }

        return true;
    }

    /**
     * Populate the global query field _text_.
     *
     * Override this method in a child plugin if different/additional fields
     * need to be specified.
     *
     * @param   array  $array  An array of values to be indexed.
     *
     * @return  array  $array  The updated array with _text_ populated.
     */

    protected function prepareGlobalQueryField($array)
    {
        // define a catch all field for general search querying.
        $array[static::GLOBAL_QUERY_FIELD] = [];
        $array[static::GLOBAL_QUERY_FIELD][] = $array['name'];
        $array[static::GLOBAL_QUERY_FIELD] = array_merge($array[static::GLOBAL_QUERY_FIELD], $array['author']);
        $array[static::GLOBAL_QUERY_FIELD][] = $array["category_s"];

        return $array;
    }
}
