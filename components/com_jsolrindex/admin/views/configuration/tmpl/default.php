<?php 
/**
 * A form view for adding/editing JSolrIndex configuration.
 * 
 * @author		$LastChangedBy$
 * @package	Wijiti
 * @subpackage	JSolr
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
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

defined( '_JEXEC' ) or die( 'Restricted access' );

$application = JFactory::getApplication("administrator");

$document = JFactory::getDocument();

$document->addScriptDeclaration(
"
var adminOptions = new Object({
	pleaseWait : \"".JText::_("Please wait...")."\",
	testURL : \"".$application->getSiteURL()."administrator/index.php?option=com_jsolrindex&task=test&format=raw\",
	indexURL : \"".$application->getSiteURL()."administrator/index.php?option=com_jsolrindex&task=index&format=raw\",
	purgeURL : \"".$application->getSiteURL()."administrator/index.php?option=com_jsolrindex&task=purge&format=raw\"
});
");

JToolBarHelper::title(JText::_('Attachment Configuration'), 'config.png');

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
								<legend>Index Server Settings</legend>
	
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
												<button id="testButton1" class="test-button"><?php echo JText::_("Test connection"); ?></button>
											</td>
											<td style="vertical-align: middle;">
												<div id="testButton1Message" class="test-message"></div>
											</td>
										</tr>
										<tr>
											<td>
												<button id="indexButton"><?php echo JText::_("Start indexing"); ?></button>
											</td>
											<td style="vertical-align: middle;">
												<div id="indexMessage"></div>
											</td>
										</tr>
										<tr>
											<td>
												<button id="purgeButton"><?php echo JText::_("Purge index"); ?></button>
											</td>
											<td style="vertical-align: middle;">
												<div id="purgeMessage"></div>
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
	
	<input type="hidden" value="com_jsolrindex" name="option"/>
	<input type="hidden" value="" name="task"/>
	<input type="hidden" value="configuration" name="view"/>
</form>