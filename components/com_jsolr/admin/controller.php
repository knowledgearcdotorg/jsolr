<?php
/**
 * A controller for managing Solr indexing and search.
 *
 * @package     JSolr
 * @subpackage  Controller
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class JSolrController extends JControllerLegacy
{
    protected $default_view = 'configuration';

    public function test()
    {
        $model = $this->getModel($this->default_view);

        if ($success = $model->test()) {
            $msg = JText::_(
                strtoupper(
                    'com_'.$this->get('name').'_'.
                    JRequest::getWord("view", "configuration")).
                    "_PING_SUCCESS");
        } else {
            $msg = JText::_($model->getError());
        }

        $search = array("\n", "\r", "\u", "\t", "\f", "\b", "/", '"');

        $replace = array("\\n", "\\r", "\\u", "\\t", "\\f", "\\b", "\/", "\"");

        $msg = str_replace($search, $replace, $msg);

        echo json_encode(array("success"=>$success, "message"=>$msg));
    }

    function display($cachable = false, $urlparams = false)
    {
        parent::display($cachable, $urlparams);

        return $this;
    }
}
