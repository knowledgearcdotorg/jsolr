<?php
/**
 * A controller for managing Solr indexing and searching.
 *
 * @package     JSolr.Index
 * @subpackage  Controller
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class JSolrIndexController extends JControllerLegacy
{
    protected $default_view = 'configuration';

    public function test()
    {
        $model = $this->getModel($this->default_view);

        if ($success = $model->test()) {
            $msg = JText::_("COM_JSOLRINDEX_".strtoupper(JRequest::getWord("view", "configuration"))."_PING_SUCCESS");
        } else {
            $msg = JText::_($model->getError());
        }

        $search = array("\n", "\r", "\u", "\t", "\f", "\b", "/", '"');
        $replace = array("\\n", "\\r", "\\u", "\\t", "\\f", "\\b", "\/", "\"");
        $msg = str_replace($search, $replace, $msg);

        echo json_encode(array("success"=>$success, "message"=>$msg));
    }

    public function testTika()
    {
        $model = $this->getModel($this->default_view);

        if ($success = $model->testTika()) {
            $msg = JText::_("COM_JSOLRINDEX_".strtoupper(JRequest::getWord("view", "configuration"))."_PING_SUCCESS");
        } else {
            $msg = JText::_($model->getError());
        }

        $search = array("\n", "\r", "\u", "\t", "\f", "\b", "/", '"');
        $replace = array("\\n", "\\r", "\\u", "\\t", "\\f", "\\b", "\/", "\"");
        $msg = str_replace($search, $replace, $msg);

        echo json_encode(array("success"=>$success, "message"=>$msg));
    }

    public function index()
    {
        $model = $this->getModel($this->default_view);

        $start = new JDate('now');

        if ($success = $model->index()) {
            $msg = JText::_("Index successful");
        } else {
            error_log('failed to index');
            $msg = JText::_($model->getError());
        }

        $end = new JDate('now');

        $time = $start->diff($end);

        $msg = $msg." (execution time: ".$time->format("%H:%M:%S").")";

        echo json_encode(array("success"=>$success, "message"=>$msg));
    }

    public function purge()
    {
        $model = $this->getModel($this->default_view);

        if ($success = $model->purge()) {
            $msg = JText::_("Index purged successfully");
        } else {
            $msg = JText::_($model->getError());
        }

        echo json_encode(array("success"=>$success, "message"=>$msg));
    }

    function display($cachable = false, $urlparams = false)
    {
        parent::display($cachable, $urlparams);

        return $this;
    }
}
