<?php
/**
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Index;

use \JComponentHelper as JComponentHelper;
use \JLog as JLog;
use \JFactory as JFactory;
use \Joomla\Utilities\ArrayHelper;

/**
 * An abstract class which all other crawler classes should derive from.
 */
abstract class Crawler extends \JPlugin
{
    const STDOUT_SEPARATOR_WIDTH = 80;

    protected static $chunk;

    /**
     * The context of the document being indexed.
     *
     * @var string
     */
    protected $context;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();

        self::$chunk = 1000;

        // load the jsolrindex component's params into plugin params for
        // easy access.
        $params = JComponentHelper::getParams('com_jsolr', true);

        $this->params->loadArray(array('component'=>$params->toArray()));

        Jlog::addLogger(array());
    }

    /**
     * Get's the language, either from the source language or from the Joomla
     * environment.
     *
     * @param  string  $language       The item being indexed.
     * @param  bool    $includeRegion  True if the region should be included,
     * false otherwise. E.g. If true, en-AU would be returned, if false, just
     * en would be returned.
     *
     *  @return string The language code.
     */
    protected function getLanguage($language, $includeRegion = true)
    {
        if (isset($language) && $language != '*') {
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
     *
     * @params  string  $lastModified  Only index items after the last modified
     * date.
     */
    public function onJSolrIndex($lastModified = null)
    {
        $this->out(array("task:index crawler:".$this->get('context'),"[starting]"), \JLog::DEBUG);

        $this->index($lastModified);

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
    protected function index($lastModified = null)
    {
        $commitWithin = $this->params->get('component.commitsWithin', '10000');

        $start = 0;
        $total = $this->getTotal();

        $client = \JSolr\Index\Factory::getClient();
        $update = $client->createUpdate();

        while ($start < $total) {
            $items = $this->getItems($start);

            $documents = array();

            try {
                foreach ($items as $item) {
                    $documents[] = $update->createDocument($this->prepare($item));

                    $this->out('document '.ArrayHelper::getValue(end($documents), 'id').' ready for indexing', \JLog::DEBUG);

                    $start++;
                }

                $update->addDocuments($documents, null, $commitWithin);
                $update->addCommit();

                $result = $client->update($update);

                if ($result->getStatus() !== 0) {
                    throw new Exception($result->getResponse()->getStatusMessage(), $result->getResponse()->getStatusCode());
                }
            } catch (Exception $e) {
                $this->out($e->getMessage(), \JLog::ERROR);
            }

            $this->out(array(count($documents).' documents successfully indexed', '[status:'.$result->getResponse()->getStatusCode().']'), \JLog::DEBUG);

            $documents = array();
        }

        $this->out("items indexed: $total", \JLog::DEBUG);
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
     * @param stdClass $item
     * @return \JSolr\Apache\Solr\Document
     */
    protected abstract function prepare($item);

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
     * @param bool $level The log level. Use the JLog constants; \JLog::DEBUG, \JLog::ERROR, etc.
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
