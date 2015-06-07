<?php
/**
 * A script for intercepting calls to this component and handling them appropriately.
 *
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.

   The JSolrSearch component for Joomla! is free software: you can redistribute it
   and/or modify it under the terms of the GNU General Public License as
   published by the Free Software Foundation, either version 3 of the License,
   or (at your option) any later version.

   The JSolrSearch component for Joomla! is distributed in the hope that it will be
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch component for Joomla!.  If not, see
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<hayden@knowledgearc.com>
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

JLoader::register('JSolrSearchHelper', dirname(__FILE__) . '/helpers/jsolrsearch.php');
JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

$JControllerName = class_exists('JControllerLegacy') ? 'JControllerLegacy' : 'JController';

$controller	= $JControllerName::getInstance('jsolrsearch');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();