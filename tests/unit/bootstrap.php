<?php
/**
* Loads Joomla framework for unit testing.
*
*/
// Maximise error reporting.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define expected Joomla constants.
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

// retrieve joomla destination from properties file.
$properties = parse_ini_file(dirname(dirname(dirname(__FILE__))).'/build.properties');

// Load Joomla framework
define('JPATH_BASE', $properties['dest']);
require_once JPATH_BASE.'/includes/defines.php';
require_once JPATH_BASE.'/includes/framework.php';

jimport('joomla.filesystem.path');
jimport('joomla.log.log');
jimport('joomla.environment.request');
jimport('joomla.session.session');

$_SERVER['HTTP_HOST'] = 'http://localhost';
$_SERVER['REQUEST_URI'] = '/index.php';
//$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$app = JFactory::getApplication('site');
$app->set('build.properties', $properties);

if (!defined('JSPACEPATH_TESTS')) {
    define('JSPACEPATH_TESTS', dirname(__FILE__));
}

// Set error handling.
JError::setErrorHandling(E_NOTICE, 'ignore');
JError::setErrorHandling(E_WARNING, 'ignore');
JError::setErrorHandling(E_ERROR, 'ignore');

require_once JPATH_PLATFORM.'/loader.php';
JLoader::registerNamespace('JSolr', dirname(dirname(dirname(__FILE__))).'/libraries');