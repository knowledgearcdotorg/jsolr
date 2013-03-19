#!/usr/bin/php
<?php
/**
 * @package JSolr
 * @subpackage Index
 * @copyright Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 */

// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER)) die();

/**
 * This is a CRON script which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/jcrawl.php
 */

// Set flag that this is a parent file.
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php'))
{
        require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
        define('JPATH_BASE', dirname(dirname(__FILE__)));
        require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Force library to be in JError legacy mode
JError::$legacy = true;

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

// Import library dependencies.
jimport('joomla.application.application');
jimport('joomla.utilities.utility');
jimport('joomla.language.language');
jimport('joomla.utilities.string');
jimport('joomla.factory');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');
jimport('joomla.error.log');
jimport('joomla.user.user');

jimport('jsolr.apache.solr.service');
 
/**
 * Simple command line interface application class.
 *
 * @package Wijiti
 * @subpackage CLI
 */
class JSolrCrawlerCli extends JApplicationCli
{ 
    public function doExecute()
    {
    	if ($this->input->get('h') || $this->input->get('help')) {
    		$this->help();
    		return;
    	}

    	$dispatcher =& JDispatcher::getInstance();
    	
		JPluginHelper::importPlugin("jsolrcrawler", null, true, $dispatcher);

		try {
			$array = $dispatcher->trigger('onIndex');
		} catch (Exception $e) {
			if ($this->input->get('q', null) || $this->input->get('quiet', null)) {
				$this->out($e->getMessage());
			}
		}
    }
 
    /**
     * Method to build and print the help screen text to stdout.
     *
     * @return void
     * @since 1.0
     */
    protected function help()
    {
        // Initialize variables.
        $help = array();
 
        // Build the help screen information.
        $help[] = 'JSolr Index Crawler: The utility for indexing Joomla information.';
        $help[] = '';
        $help[] = 'Usage:';
        $help[] = '/path/to/php /path/to/joomla/cli/jsolr_crawler.php [options]';
        $help[] = '--help, -h\tPrint this help';
        $help[] = '--quiet, -q\tSuppress all output, including errors';
 
        // Print out the help information.
        $this->out(implode("\n", $help));
 
    }
}
 
// Set error handling.
JError::setErrorHandling(E_ALL, 'echo');
 
JApplicationCli::getInstance('JSolrCrawlerCli')->execute();