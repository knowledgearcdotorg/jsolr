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
    	$_SERVER['HTTP_HOST'] = 'domain.com';
		JFactory::getApplication('site');

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
    	$options['application'] = get_class($this);
    	$options['rebuild'] = ($this->input->get('r') || $this->input->get('rebuild')) ? true : false;
    	$options['verbose'] = ($this->input->get('v') || $this->input->get('verbose')) ? true : false;
    	$options['clean'] = ($this->input->get('c') || $this->input->get('clean')) ? true : false;

    	// @deprecated the m and modified options are deprecated and will be 
    	// removed from future versions. 
    	if ($this->input->getString('u') || $this->input->getString('updated')) {
    		$lastModified = $this->input->getString('u', $this->input->getString('update'));

    		$d = JDate::createFromFormat("Y-m-d\TH:i:sP", $lastModified, new DateTimeZone(JFactory::getConfig()->get('offset')));
    		
    		$valid = false;
    		
    		if ($d) {    			
    			if ($d->getTimezone()) {    				
	    			$format = "Y-m-d\TH:i:s".(($d->getTimezone()->getName() == 'Z') ? '\Z' : 'P');

	    			if ($d->format($format) == $lastModified) {	    				
	    				$valid = true;
	    			}
    			}
    		}
    		
    		if ($valid) {
    			$options['lastModified'] = $lastModified;
    		} else {
	    		$client = JSolrIndexFactory::getService();
	    		
	    		if ($client->ping()) {
	    			$response = $client->luke();
	    	
	    			$options['lastModified'] = $response->index->lastModified;
	    		}
    		}
    		
    	} elseif ($this->input->get('m') || $this->input->get('modified')) {
    		$lastModified = $this->input->get('m', $this->input->get('modified'));
    		
    		$client = JSolrIndexFactory::getService();
    		
    		if ($client->ping()) {
    			$response = $client->luke();
    			 
    			$options['lastModified'] = $response->index->lastModified;
    		}    		
    	}
    	
    	$start = new JDate('now');
    	
    	$this->out("crawl start ".$start->format("c"));

    	$dispatcher = JDispatcher::getInstance();

    	JPluginHelper::importPlugin("jsolrcrawler", null, true, $dispatcher);
    	
		try {			
    		$array = $dispatcher->trigger('onIndex', array($options));
		} catch (Exception $e) {
    		if ($this->input->get('q', false) || $this->input->get('quiet', false)) {
    			$this->out($e->getMessage());
    		}    		
    	}
    	
    	$end = new JDate('now');

    	$this->out("crawl end ".$end->format("c"));    	 
    	
    	$time = $start->diff($end);
    	
    	$this->out("execution time: ".$time->format("%H:%I:%S"));  	 
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
    	echo <<<EOT
Usage: jsolr_crawler [options]
   jsolr_crawler [vq] [u|update] <last-index-date>
    	
Provides tools for managing a Joomla-centric Solr index.

  -c, --clean         Clean out deleted items from the index.
  -h, --help          Display this help and exit.
  -m, --modified      Deprecated. Use -u or --update instead.
  -o, --optimize      Run an optimization on the index.
  -p, --purge         Purge the contents of the index.
  -q, --quiet         Suppress all output.
  -r, --rebuild       Rebuild the index, deleting then re-creating all 
                      documents.                      
  -u, --update        Index only those items which have been created or 
                      modified since the last index.
                      Specify an ISO8601-compatible date to override the last 
                      index date.
  -v, --verbose       Display verbose information about the current action.
    	
EOT;
    }
    
    public function out($text = '', $nl = true)
    {
    	if (!($this->input->get('q', false) || $this->input->get('quiet', false))) {
    		parent::out($text, $nl);
    	}
    	
    	return $this;
    }
}
 
JApplicationCli::getInstance('JSolrCrawlerCli')->execute();