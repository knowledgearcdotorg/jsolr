<?php
/**
 * A script for intercepting calls to this component and handling them appropriately.
 *
 * @package    JSolr
 * @copyright  Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

$path = dirname(__FILE__) . '/helpers/jsolr.php';

JLoader::register('JSolrHelper', $path);

require_once JPATH_ROOT.'/libraries/JSolr/vendor/autoload.php';

if (class_exists('JControllerLegacy')) {
    $JControllerName = 'JControllerLegacy';
} else {
    $JControllerName = 'JController';
}

$controller	= $JControllerName::getInstance('jsolr');

$controller->execute(JRequest::getCmd('task'));

$controller->redirect();
