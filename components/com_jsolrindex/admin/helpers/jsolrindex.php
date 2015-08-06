<?php
/**
 * A helper that provides assistance with permissions adn submenus for JSolrIndex.
 *
 * @package    JSolr.Index
 * @copyright  Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

class JSolrIndexHelper
{
    public static $extension = 'com_jsolrindex';

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
     * @param    int        The community ID.
     *
     * @return    JObject
     * @since    1.6
     */
    public static function getActions()
    {
        $user    = JFactory::getUser();
        $result    = new JObject();

        $assetName = 'COM_JSOLRINDEX';

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
}