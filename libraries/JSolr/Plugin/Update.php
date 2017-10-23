<?php
/**
 * An abstract class which provides index updates based upon the date the
 * index was last run.
 *
 * Ideal for indexing 3rd party content such as records retrieved using a REST
 * API.
 *
 * @copyright   Copyright (C) 2012-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Plugin;

abstract class Update extends \JSolr\Plugin
{
    protected $indexed;

    protected $now;

    /**
     * Fires the index update event.
     *
     * The index update event is used to index items that have been created or
     * modified since the index date. It should be used for content that are
     * being indexed from an external source. For content that implements
     * Joomla! content events, use onContentAfterSave, onContentAfterDelete
     * and onContentStateChanged instead.
     */
    public function onJSolrIndexUpdate()
    {
        $this->out(array("task:index:update crawler:".$this->get('context'),"[starting]"), \JLog::DEBUG);

        if ($indexed = $this->params->get('indexed')) {
            $this->indexed = \JFactory::getDate($indexed)->format('Y-m-d\TH:i:s\Z', false);
            $this->out(array("indexing from ".$this->indexed), \JLog::DEBUG);
        } else {
            $this->out(array("running update for the first time. indexing everything..."), \JLog::DEBUG);
        }

        $this->index();

        $this->out(array("task:index:update crawler:".$this->get('context'),"[completed]"), \JLog::DEBUG);
    }

    public function index()
    {
        $this->now = \JFactory::getDate()->format('Y-m-d\TH:i:s\Z', false);

        parent::index();

        $this->updateIndexed();
    }

    /**
     * Update the plugin's indexed param.
     */
    protected function updateIndexed()
    {
        $table = \JTable::getInstance('extension');

        $name = 'plg_'.$this->_type.'_'.$this->_name;

        $id = $table->find(array('name'=>$name, 'type'=>'plugin'));
        $table->load($id);

        $params = new \Joomla\Registry\Registry;
        $params->loadString($table->params, 'json');

        $params->set('indexed', $this->now);
        $table->bind(array('params'=>$params->toString()));

        // check and store
        if (!$table->check()) {
            new \Exception($table->getError());
        }
        if (!$table->store()) {
            new \Exception($table->getError());
        }
    }
}
