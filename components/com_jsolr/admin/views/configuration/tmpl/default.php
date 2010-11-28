<?php 
/**
 * A form view for adding/editing JSolr configuration.
 * 
 * @author		$LastChangedBy: spauldingsmails $
 * @package		JSolr
 * @copyright	Copyright (C) 2009 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr component for Joomla!.

   The JSolr component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolr component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$application = JFactory::getApplication("administrator");

$document = JFactory::getDocument();

$document->addScriptDeclaration(
"
var adminOptions = new Object({
	testURL : \"".$application->getSiteURL()."administrator/index.php?option=com_jsolr&task=test&format=raw\"
});
");

$document->addScript($application->getSiteURL() . "media/com_jsolr/js/jsolr.js");

JToolBarHelper::title(JText::_('JSolr Configuration'), 'config.png');

JToolBarHelper::save();
JToolBarHelper::cancel();
?>

<form autocomplete="off" name="adminForm" method="post" action="index.php">
	<div id="config-document">
		<div id="page-site" style="display: block;">
			<table class="noshow">
				<tbody>
					<tr>
						<td width="65%">
							<fieldset class="adminform">
								<legend>Component Settings</legend>
	
								<table cellspacing="1" class="admintable">
									<tbody>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Host name</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("host"); ?>" size="50" id="host" name="host" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Port</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("port"); ?>" size="50" id="port" name="port" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Path</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("path"); ?>" size="50" id="path" name="path" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Username</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("username"); ?>" size="50" id="username" name="username" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Password</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("password"); ?>" size="50" id="password" name="password" class="text_area"/>
											</td>
										</tr>
									</tbody>																	
								</table>
							</fieldset>
							
							<fieldset class="adminform">
								<legend>Index Management</legend>
	
								<table cellspacing="1" class="admintable">
									<tbody>
										<tr>
											<td>
												<button id="testButton"><?php echo JText::_("Test connection"); ?></button>
											</td>
											<td>
												<div id="testMessage" style="vertical-align: middle;"></div>
											</td>
										</tr>									
									</tbody>
								</table>
							</fieldset>							
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="clr"></div>
	
	<input type="hidden" value="com_jsolr" name="option"/>
	<input type="hidden" value="" name="task"/>
	<input type="hidden" value="configuration" name="view"/>
</form>