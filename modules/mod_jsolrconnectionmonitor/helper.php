<?php
defined('_JEXEC') or die;

jimport('jsolr.factory');

abstract class ModConnectionMonitorHelper
{
	public static function getIndex($params)
	{
		$index = array();
		
		$client = self::_getService($params);
		$config = self::_getConfig($params);
		
		$index['status'] = self::isConnected($params);
		$index['host'] = $config->get('host', null);
		$index['port'] = $config->get('port', null);
		$index['path'] = $config->get('path', null);
		$index['libraries']['curl'] = self::isCurlInstalled();
		$index['libraries']['jsolr'] = self::isJSolrLibraryInstalled();
		
		if (JArrayHelper::getValue($index, 'status') && 
			JArrayHelper::getValue(JArrayHelper::getValue($index, 'libraries'), 'curl') &&
			JArrayHelper::getValue(JArrayHelper::getValue($index, 'libraries'), 'jsolr')) {	
			try {
				$response = $client->luke();

				$index['details'] = $response->index;					
			} catch (Exception $e) {
				// do nothing
			}
		}
		
		return $index;
	}

	public static function isConnected($params)
	{
		$client = self::_getService($params);
		
		$response = $client->ping(10);
		
		if ($response === false) {
			return false;
		}
		
		return true;
	}
	
	private static function _getService($params)
	{
		$class = 'JSolrIndexFactory';

		if ($params->get('service') == 1) {
			$class = 'JSolrSearchFactory';
		}

		return $class::getService();
	}
	
	private static function _getConfig($params)
	{
		$class = 'JSolrIndexFactory';
		
		if ($params->get('service') == 1) {
			$class = 'JSolrSearchFactory';
		}
		
		return $class::getConfig();		
	}
	
	public static function isCurlInstalled()
	{
		return function_exists('curl_version');
	}
	
	public static function isJSolrLibraryInstalled()
	{
		return class_exists('JSolrFactory');
	}
}
