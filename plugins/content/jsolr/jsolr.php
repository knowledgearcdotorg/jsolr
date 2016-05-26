<?php
/**
 * @package     JSolr.Plugin
 * @subpackage  Content
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * A class for handling content manipulation via the com_content event model.
 *
 * @package     JSolr.Plugins
 * @subpackage  Content
 */
class PlgContentJSolr extends JPlugin
{
    protected $autoloadLanguage = true;

    public function onContentAfterSave($context, $article, $isNew)
    {
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('jsolr');

        $results = $dispatcher->trigger('onJSolrIndexItemSave', array($context, $article, $isNew));
    }

    public function onContentAfterDelete($context, $article)
    {
        $dispatcher = JDispatcher::getInstance();

        JPluginHelper::importPlugin('jsolr');

        $results = $dispatcher->trigger('onJSolrIndexItemDelete', array($context, $article));
    }

    public function onContentChangeState($context, $pks, $value)
    {

    }
}
