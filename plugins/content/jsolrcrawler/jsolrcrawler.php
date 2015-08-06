<?php
/**
 * @package     JSolr.Plugin
 * @subpackage  Content
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * A class for handling content manipulation via the com_content event model.
 *
 * @package     JSolr.Plugins
 * @subpackage  Content
 */
class plgContentJSolrCrawler extends JPlugin
{
    /**
     * Initializes an instance of this class.
     *
     * @param  object  $subject The object to observe
     * @param  array   $config  An array that holds the plugin configuration
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function onContentAfterSave($context, $article, $isNew)
    {
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('jsolrcrawler');

        $results = $dispatcher->trigger('onJSolrIndexAfterSave', array($context, $article, $isNew));
    }

    public function onContentAfterDelete($context, $article)
    {
        $dispatcher = JDispatcher::getInstance();

        JPluginHelper::importPlugin('jsolrcrawler');

        $results = $dispatcher->trigger('onJSolrIndexAfterDelete', array($context, $article));
    }
}
