<?php
/**
 * Installation scripts.
 * 
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012-2014 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrIndex component for Joomla!.

   The JSolrIndex component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrIndex component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrIndex component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.helper');
jimport('joomla.filesystem.folder');

class Com_JSolrIndexInstallerScript
{	
	private $dependencies = array(
		'libraries'=>array(
			'lib_jsolr'=>array(
				'published'=>true
			)	
		),
		'modules'=>array(
			'mod_jsolrconnectionmonitor'=>array(
				'published'=>true,
				'client_id'=>'1',
				'position'=>'jsolrindex',
				'title'=>'MOD_JSOLRCONNECTIONMONITOR_DEFAULT_INDEX_TITLE',
				'params'=>'{"service":"index"}',
				'installed_position'=>'jsolrindex' // use for replacing modules by position.
			)
		),
		'plugins'=>array(
		//	'plg_system_mysystemplugin'=>array(
		//		'published'=>true
		//	)
		)
	);
	
	public function install($parent)
	{		
		$this->_installExtensions($parent);
	}
	
	public function update($parent) 
	{
		$this->_installExtensions($parent);
	}
	
	public function uninstall($parent)
	{
		$src = JPATH_ROOT."/cli/jsolr_crawler.php";
		
		if (JFile::exists($src)){
			if (JFile::delete($src)) {
				echo "<p>Crawler uninstalled from ".$src." successfully.</p>";
			} else {
				echo "<p>Could not uninstall crawler from ".$src.". You will need to manually remove it.</p>";
			}
		}
	}
	
	public function postflight($type, $parent)
	{
		$src = $parent->getParent()->getPath('extension_administrator').'/cli/jsolr_crawler.php';
	
		$cli = JPATH_ROOT.'/cli/jsolr_crawler.php';
	
		if (JFile::exists($src)) {
			if (JFile::move($src, $cli)) {
				JFolder::delete($parent->getParent()->getPath('extension_administrator').'/cli');
			}
		}
	}
	
	/**
	 * Installs all dependencies.
	 * 
	 * @param JAdapterInstance $parent
	 */
	private function _installExtensions($parent)
	{
		$installer = new JInstaller();
		$installer->setOverwrite(true);

		$src = $parent->getParent()->getPath('source');

		foreach ($this->dependencies as $type=>$extension) {
			foreach ($extension as $name=>$params) {
				$packageZip = $src.'/'.$type.'/'.$name.'.zip';

				if ($package = JInstallerHelper::unpack($packageZip)) {
					$doInstall = true;
					
					$path = $this->_getInstalledManifest($type, $name);					
					$oldManifest = null;
					
					if (JFile::exists($path)) {
						$oldManifest = $installer->parseXMLInstallFile($path);
					}
					
					$dir = JArrayHelper::getValue($package, 'dir');
					
					$path = $this->_getExtractedManifest($dir, $type, $name);
					$newManifest = $installer->parseXMLInstallFile($path);
					
					if ($oldManifest) {
						$oldVersion = JArrayHelper::getValue($oldManifest, 'version');
						$newVersion = JArrayHelper::getValue($newManifest, 'version');
						
						if (version_compare($oldVersion, $newVersion, 'ge')) {
							$doInstall = false;
						}
					}
					
					if ($doInstall) {
						if ($installer->install($dir)) {
							
							// post installation configuration.
							if ($type == 'modules') {
								$this->_configureModule($name, $params);
							}
							
							$msgtext  = "$name successfully installed.";
						} else {
							$msgtext  = "ERROR: Could not install $name. Please install manually.";
						}
					} else {
						$msgtext = $name." is up-to-date";
					}
					?>
					<table width="100%">
						<tr style="height:30px">
							<td width="50px"><img src="/administrator/images/tick.png" height="20px" width="20px"></td>
							<td><font size="2"><b><?php echo $msgtext; ?></b></font></td>
						</tr>
					</table>
					<?php
					JInstallerHelper::cleanupInstall($packageZip, $dir);
				}
			}
		}
	}
	
	private function _getInstalledManifest($type, $name)
	{
		$path = JPATH_ROOT;

		switch ($type) {
			case 'libraries':
				$path.='/administrator/manifests/libraries/'.str_replace("lib_", "", $name);
				break;
				
			case 'modules':
				$path.='/modules/'.$name.'/'.$name;
				break;
				
			case 'plugins':
				if (count($parts = explode('_', $name, 2)) == 3) { 
					$path.='/plugins/'.JArrayHelper::getValue($parts, 1).'/'.JArrayHelper::getValue($parts, 2);
				}
				
		}
		
		return $path.'.xml';
	}
	
	private function _getExtractedManifest($path, $type, $name)
	{
		switch ($type) {
			case 'libraries':
				$path.='/'.str_replace("lib_", "", $name);
				break;
	
			case 'modules':
				$path.='/'.$name;
				break;
	
			case 'plugins':
				if (count($parts = explode('_', $name, 2)) == 3) {
					$path.='/'.JArrayHelper::getValue($parts, 1).'/'.JArrayHelper::getValue($parts, 2);
				}
	
		}
	
		return $path.'.xml';
	}
	
	private function _configureModule($name, $params)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__modules')
			->where("module = '$name'");
		
		if ($clientId = JArrayHelper::getValue($params, 'client_id')) {
			$query->where("client_id = $clientId");
		}
		
		
		if ($installedPosition = JArrayHelper::getValue($params, 'installed_position')) {
			$query->where("position = '$installedPosition'");
		}
		
		$count = $db->setQuery($query)->loadResult();
		
		if (!$count) {
			$language = JFactory::getLanguage();
			$language->load($name, JPATH_ADMINISTRATOR, null, true);
			
			// Set up module per config preferences.
			$position = JArrayHelper::getValue($params, 'position');
			$published = JArrayHelper::getValue($params, 'published');	
			
			$query = $db->getQuery(true)
				->update($db->qn('#__modules'))
				->set($db->qn('position').' = '.$db->q($position))				
				->where($db->qn('module').' = '.$db->q($name));
			
			if ($title = JArrayHelper::getValue($params, 'title')) {
				$query->set($db->qn('title').' = '.$db->q(JText::_($title)));
			}
			
			if($published) {
				$query->set($db->qn('published').' = '.$db->q('1'));
			}
			
			$db->setQuery($query);
			$db->execute();
			
			// Make accessible on every page.
			$query = $db->getQuery(true)
				->select('id')
				->from($db->qn('#__modules'))
				->where($db->qn('module').' = '.$db->q($name));
			
			$db->setQuery($query);
			$moduleId = $db->loadResult();
			
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__modules_menu'))
				->where($db->qn('moduleid').' = '.$db->q($moduleId));
			
			$db->setQuery($query);
			$assignments = $db->loadObjectList();
			
			if(!$assignments) {
				$o = (object)array('moduleid'=>$moduleId, 'menuid'=>0);
				$db->insertObject('#__modules_menu', $o);
			}
		}
	}
}