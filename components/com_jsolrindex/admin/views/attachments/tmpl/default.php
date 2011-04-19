<?php 
/**
 * A form view for adding/editing JSolrIndex configuration.
 * 
 * @author		$LastChangedBy: spauldingsmails $
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
	testURL : \"".$application->getSiteURL()."administrator/index.php?option=com_jsolrindex&task=test&view=attachments&format=raw\"
});
");

JToolBarHelper::title(JText::_('JSolr Index Attachments'), 'config.png');

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
								<legend>Attachment Settings</legend>
	
								<table cellspacing="1" class="admintable">
									<tbody>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip"><?php echo JText::_("Include the following file types for indexing"); ?></span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("include_types"); ?>" size="50" id="include_types" name="include_types" class="text_area"/>
											</td>	
											<td>
											<div><?php echo JText::_("A comma-separated list of attachment file types to index."); ?></div>											
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip"><?php echo JText::_("Extract using"); ?></span>
											</td>
											<td>
											<?php 
											$options = array();
											$options[] = JHTML::_("select.option", "local", JText::_("Local Tika"));
											$options[] = JHTML::_("select.option", "remote", JText::_("Remote Tika"));
											$options[] = JHTML::_("select.option", "solr", JText::_("Solr Server"));
											
											echo JHTML::_("select.radiolist", $options, "extractor", "", "value", "text", $this->getModel()->getParam("extractor"), "extractor");
											?>
											</td>
											<td>
												<div><?php echo JText::_("Using a local Tika application will improve indexing performance."); ?></div>
											</td>
										</tr>
									</tbody>																	
								</table>
							</fieldset>
							
							<fieldset id="localTika" class="adminform<?php if ($this->getModel()->getParam("extractor") != "local") echo " hide"; ?>">
								<legend><?php echo JText::_("Local Tika Management"); ?></legend>
	
								<table cellspacing="1" class="admintable">
									<tbody>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip"><?php echo JText::_("Tika Application Path"); ?></span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("tika_app_path"); ?>" size="50" id="tika_app_path" name="tika_app_path" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td>
												<button id="testButton1" class="test-button"><?php echo JText::_("Test application path"); ?></button>
											</td>
											<td colspan="2" style="vertical-align: middle;">
												<div id="testButton1Message" class="test-message"></div>
											</td>
										</tr>
									</tbody>
								</table>
							</fieldset>
							
							<fieldset id="remoteTika" class="adminform<?php if ($this->getModel()->getParam("extractor") != "remote") echo " hide"; ?>">
								<legend><?php echo JText::_("Remote Tika Management"); ?></legend>
	
								<table cellspacing="1" class="admintable">
									<tbody>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Host name</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("tika_host"); ?>" size="50" id="tika_host" name="tika_host" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Port</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("tika_port"); ?>" size="50" id="tika_port" name="tika_port" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Path</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("tika_path"); ?>" size="50" id="tika_path" name="tika_path" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Username</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("tika_username"); ?>" size="50" id="tika_username" name="tika_username" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td class="key">
												<span class="editlinktip hasTip">Password</span>
											</td>
											<td>
												<input type="text" value="<?php echo $this->getModel()->getParam("tika_password"); ?>" size="50" id="tika_password" name="tika_password" class="text_area"/>
											</td>
										</tr>
										<tr>
											<td>
												<button id="testButton2" class="test-button"><?php echo JText::_("Test connection"); ?></button>
											</td>
											<td style="vertical-align: middle;">
												<div id="testButton2Message" class="test-message"></div>
											</td>
										</tr>
									</tbody>
								</table>
							</fieldset>
							
							<fieldset id="solrServer" class="adminform<?php if ($this->getModel()->getParam("extractor") != "solr") echo " hide"; ?>">
								<legend><?php echo JText::_("Solr Server Management"); ?></legend>
	
								<table cellspacing="1" class="admintable">
									<tbody>
										<tr>
											<td style="vertical-align: middle;">
											<?php echo JText::_("COM_JSOLRINDEX_SOLR_SERVER_MANAGEMENT"); ?>
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
	<input type="hidden" value="attachments" name="view"/>
</form>