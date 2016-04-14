<?php
/**
 * A script for intercepting calls to this component and handling them appropriately.
 *
 * @package     JSolr.Search
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

JLoader::register('JSolrSearchHelper', dirname(__FILE__) . '/helpers/jsolrsearch.php');

JLoader::registerNamespace('JSolr', JPATH_PLATFORM);

$JControllerName = class_exists('JControllerLegacy') ? 'JControllerLegacy' : 'JController';

$controller = $JControllerName::getInstance('jsolrsearch');

$controller->execute(JFactory::getApplication()->input->get('task'));

$controller->redirect();