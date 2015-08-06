<?php
/**
 * A script for intercepting calls to this component and handling them appropriately.
 *
 * @package     JSolr.Index
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

JLoader::register('JSolrIndexHelper', dirname(__FILE__) . '/helpers/jsolrindex.php');

JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

$JControllerName = class_exists('JControllerLegacy') ? 'JControllerLegacy' : 'JController';

$controller	= $JControllerName::getInstance('jsolrindex');

$controller->execute(JRequest::getCmd('task'));

$controller->redirect();
