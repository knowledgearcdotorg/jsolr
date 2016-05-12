<?php
/**
 * @package    JSolr
 * @copyright  Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class JSolrHelper
{
    public static $extension = 'com_jsolr';

    /**
     * Configure the Linkbar.
     *
     * @param string $vName The name of the active view.
     *
     * @return void
     */
    public static function addSubmenu($vName)
    {

    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @param   int      The community ID.
     *
     * @return  JObject
     */
    public static function getActions()
    {
        $user = JFactory::getUser();

        $result = new JObject();

        $assetName = 'COM_JSOLR';

        $actions = array(
            'core.admin',
            'core.manage',
            'core.create',
            'core.edit',
            'core.edit.own',
            'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action,    $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function log($msg, $type = JLog::ERROR)
    {
        $app = \Joomla\Utilities\ArrayHelper::getValue($GLOBALS, 'application');

        $verbose = (bool)($app->input->get('v') || $app->input->get('verbose'));
        $quiet = (bool)($app->input->get('q') || $app->input->get('quiet'));

        if (get_class($app) == "JSolrCli") {
            if ($type == JLog::ERROR) {
                if (!$quiet) {
                    $app->out($msg);
                }
            } else {
                if ($verbose) {
                    $app->out($msg);
                }
            }
        } else {
            JLog::add($msg, $type, 'jsolr');
        }
    }
}
