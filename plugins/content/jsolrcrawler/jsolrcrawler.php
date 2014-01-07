<?php
/**
 * @package		JSolr.Plugins
 * @subpackage	Content
 * @copyright	Copyright (C) 2013 Wijiti Pty Ltd. All rights reserved.
 * @license		This file is part of the JSolr Content Crawler plugin for Joomla!.

   The JSolr Content Crawler plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr Content Crawler plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr Content Index plugin for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * A class for handling content manipulation via the com_content event model. 
 *
 * @package		JSolr.Plugins
 * @subpackage	Content
 */
class plgContentJSolrCrawler extends JPlugin
{
	/**
	 * Initializes an instance of this class.
	 *
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
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
}