<?php
/**
 * Installation scripts.
 * 
 * @package		JSolr
 * @subpackage	Index
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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

class com_JSolrIndexInstallerScript
{	
	public function install($parent)
	{
		$installer = new JInstaller();
		$installer->setOverwrite(true);
		
		$pkg_path = JPATH_ADMINISTRATOR.'/components/com_jsolrindex/extensions/';
		if (JFolder::create($pkg_path)) {
			if ($handle = opendir($pkg_path)) {
				while ($pkg = readdir($handle)) {
					if ($pkg != "." && $pkg != "..") {
						$package = JInstallerHelper::unpack($pkg_path.$pkg);
			
						if ($installer->install($package['dir'])) {
							$msgcolor = "#E0FFE0";
							$msgtext  = "$pkg successfully installed.";
						} else {
							$msgcolor = "#FFD0D0";
							$msgtext  = "ERROR: Could not install $pkg. Please install manually.";
						}
						?>
						
						<table bgcolor="<?php echo $msgcolor; ?>" width ="100%">
							<tr style="height:30px">
								<td width="50px"><img src="/administrator/images/tick.png" height="20px" width="20px"></td>
								<td><font size="2"><b><?php echo $msgtext; ?></b></font></td>
							</tr>
						</table>
						
						<?php
						JInstallerHelper::cleanupInstall($pkg_path.$pkg, $package['dir']);			
					}
				}
			}
		}
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
	
	public function postflight($type, $adapter)
	{
		$src = $adapter->getParent()->getPath('extension_administrator').'/cli/jsolr_crawler.php';
	
		$cli = JPATH_ROOT.'/cli/jsolr_crawler.php';
	
		if (JFile::exists($src)) {
			if (JFile::move($src, $cli)) {
				JFolder::delete($adapter->getParent()->getPath('extension_administrator').'/cli');
			}
		}
	}
}