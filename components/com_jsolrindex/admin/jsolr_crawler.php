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

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php')) {
        require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', dirname(dirname(__FILE__)));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
	require_once JPATH_LIBRARIES . '/import.legacy.php';	
else
	require_once JPATH_LIBRARIES . '/import.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';


if (version_compare(JVERSION, "3.0", "l")) {
	// Force library to be in JError legacy mode
	JError::$legacy = true;
	
	// Import necessary classes not handled by the autoloaders
	jimport('joomla.application.menu');
	jimport('joomla.environment.uri');
	jimport('joomla.event.dispatcher');
	jimport('joomla.utilities.utility');
	jimport('joomla.utilities.arrayhelper');
	
}

// System configuration.
$config = new JConfig;

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

// Load Library language
$lang = JFactory::getLanguage();

// Try the finder_cli file in the current language (without allowing the loading of the file in the default language)
$lang->load('jsolrcrawler_cli', JPATH_SITE, null, false, false)
// Fallback to the finder_cli file in the default language
|| $lang->load('jsolrcrawler_cli', JPATH_SITE, null, true);

jimport('joomla.application.component.helper');
jimport('jsolr.index.factory');
 
/**
 * Simple command line interface application class.
 *
 * @package JSolr.Index.CLI
 */
class JSolrCrawlerCli extends JApplicationCli
{
    public function doExecute()
    {
    	if ($this->input->get('h') || $this->input->get('help')) {
    		$this->help();
    		return;
    	}
    	
    	// fool the system into thinking we are running as JSite with JSolr as the active component
		JFactory::getApplication('site');
		$_SERVER['HTTP_HOST'] = 'domain.com';

		// Disable caching.
		$config = JFactory::getConfig();
		$config->set('caching', 0);
		$config->set('cache_handler', 'file');
		
		try {			
			if ($this->input->get('p') || $this->input->get('purge')) {
				$this->purge();
				return;
			}			
			
			if ($this->input->get('o') || $this->input->get('optimize')) {
				$this->optimize();
				return;
			}

			$this->index();
						
		} catch (Exception $e) {
			if ($this->input->get('q', null) || $this->input->get('quiet', null)) {
				$this->out($e->getMessage());
			}
		}
    }
    
    protected function optimize()
    {
    	$client = JSolrIndexFactory::getService();
    	
    	if ($client->ping()) {
    		$client->optimize();
    	}
    }
    
    protected function index()
    {    	
    	$options = array();
    	$options['quiet'] = ($this->input->get('q') || $this->input->get('quiet')) ? true : false;
    	$options['rebuild'] = ($this->input->get('r') || $this->input->get('rebuild')) ? true : false;
    	
    	if ($this->input->get('c') || $this->input->get('changed')) {
    		$client = JSolrIndexFactory::getService();
    		
    		if ($client->ping()) {
    			$response = $client->luke();
    	
    			$options['lastModified'] = $response->index->lastModified;
    		}
    	}

    	$this->out("start crawl...");
    	
    	$start = new JDate('now');
    	
    	$dispatcher = JDispatcher::getInstance();

    	JPluginHelper::importPlugin("jsolrcrawler", null, true, $dispatcher);
    	
		try {
    		$array = $dispatcher->trigger('onIndex', array($options));
		} catch (Exception $e) {
    		if ($this->input->get('q', false) || $this->input->get('quiet', false)) {
    			$this->out($e->getMessage());
    		}    		
    	}

    	$this->out("end crawl...");
    	
    	$end = new JDate('now');
    	 
    	$time = $start->diff($end);
    	
    	$this->out("execution time: ".$time->format("%H:%M:%S"));  	 
    }
    
    protected function purge()
    {
		$client = JSolrIndexFactory::getService();
		
		if ($client->ping()) {
			$client->deleteByQuery("*:*");
			$client->commit();
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
        $help[] = "JSolr Index Crawler: The utility for indexing Joomla information.";
        $help[] = "\r\n";
        $help[] = "Usage:";
        $help[] = "/path/to/php /path/to/joomla/cli/jsolr_crawler.php -[c|h|r|p|q]";
        $help[] = "\r\n";
        $help[] = "-c, --created\tIndex only those items which have been created or modified since the last index.";
        $help[] = "-h, --help\tPrint this help";
        $help[] = "-p, --purge\tPurge the contents of the index.";
        $help[] = "-q, --quiet\tSuppress all output, including errors";
        $help[] = "-r, --rebuild\tRebuild the index, deleting then re-creating all documents.";
 
        // Print out the help information.
        $this->out(implode("\n", $help));
 
    }
    
    protected function out($text = '', $nl = true)
    {
    	if ($this->input->get('q', false) || $this->input->get('quiet', false)) {
    		parent::out($text, $nl);
    	}
    	
    	return $this;
    }
}
 
JApplicationCli::getInstance('JSolrCrawlerCli')->execute();