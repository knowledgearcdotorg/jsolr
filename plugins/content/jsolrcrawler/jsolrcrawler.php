<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Vote plugin.
 *
 * @package		JSolr.Plugins
 * @subpackage	Content
 */
class plgContentJSolrCrawler extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      public
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config)
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
}