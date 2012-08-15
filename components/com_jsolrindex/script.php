<?php
/**
 * Installation scripts.
 * 
 * @package	Wijiti
 * @subpackage	JSolr
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
		$src = "administrator".DS."components".DS."com_jsolrindex".DS."jsolr_crawler.php";
		$dest = "cli".DS."jsolr_crawler.php";
		
		if (JFile::move($src, $dest, JPATH_ROOT)) {
			echo "<p>Crawler installed in ".JPATH_ROOT.DS."cli successfully. Use the crawler file to run an indexing cron job across your Joomla! site.</p>";
		} else {
			echo "<p>Crawler failed to install in ".JPATH_ROOT.DS."cli. You will need to copy it manually from ".JPATH_COMPONENT_ADMINISTRATOR.".</p>";
		}

		$installer = new JInstaller();
		$installer->setOverwrite(true);
		
		$pkg_path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jsolrindex'.DS.'extensions'.DS;
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
		$src = JPATH_ROOT.DS."cli".DS."jsolr_crawler.php";
		
		if (JFile::delete($src)) {
			echo "<p>Crawler uninstalled from ".$src." successfully.</p>";
		} else {
			echo "<p>Could not uninstall crawler from ".$src.". You will need to manually remove it.</p>";
		}
	}
}