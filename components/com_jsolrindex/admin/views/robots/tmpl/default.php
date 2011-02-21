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
	testURL : \"".$application->getSiteURL()."administrator/index.php?option=com_jsolrindex&task=test&format=raw\"
});
");

$document->addScript($application->getSiteURL() . "media/com_jsolrindex/js/jsolrindex.js");

JToolBarHelper::title(JText::_('JSolr Index Configuration'), 'config.png');

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
												<span class="editlinktip hasTip">Robots.txt</span>
											</td>
											<td>
												<textarea id="robots" name="robots" class="text_area" cols="80" rows="20"><?php echo $this->get("Contents"); ?></textarea>
											</td>
											<td>											
												<p>Specify ignore rules for crawlers.</p>
												<p>An ignore rule takes the form;
												<br/>&lt;plugin-name&gt;;&lt;rule&gt;=list of rules</p>
												<p>Refer to each crawler plugin's documentation for more information about configuration rules.</p>
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
	<input type="hidden" value="robots" name="view"/>
</form>