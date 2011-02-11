<?php
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'config.php' );

jimport("joomla.plugin.helper");
jimport("joomla.event.dispatcher");
jimport("joomla.environment.uri");
jimport("joomla.database.table");

class JSolrVirtuemartTest extends PHPUnit_Framework_TestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp() 
	{
		require_once JPATH_BASE.DS.'configuration.php';
		
		$registry =& JFactory::getConfig();

		$config = new JConfig();
		$registry->loadObject($config);
		
		$_SERVER['HTTP_HOST'] = 'localhost';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown() 
	{

	}
	
	public function testOnPrepareCurrency()
	{
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		$array = $dispatcher->trigger("onPrepareCurrency", array("en-US"));

		$this->assertEquals("USD", JArrayHelper::getValue($array, 0), 'Currencies are not equal.');
	}
	
	public function testOnPrepareCurrencyGeneric()
	{
		JPluginHelper::importPlugin("jsolrsearch");
		$dispatcher =& JDispatcher::getInstance();

		$array = $dispatcher->trigger("onPrepareCurrency", array("en-NZ"));

		$this->assertEquals("USD", JArrayHelper::getValue($array, 0), 'Currencies are not equal.');
	}
}