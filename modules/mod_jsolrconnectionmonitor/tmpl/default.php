<?php
/**
 * @package		JSolr
 * @copyright	Copyright (C) 2014 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolr Connection Monitor module for Joomla!.

   The JSolr Connection Monitor module for Joomla! is free software: you can 
   redistribute it and/or modify it under the terms of the GNU General Public 
   License as published by the Free Software Foundation, either version 3 of 
   the License, or (at your option) any later version.

   The JSolr Connection Monitor module for Joomla! is distributed in the hope 
   that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolr filter module for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */
defined('_JEXEC') or die;
?>
<div class="row-striped">
	<div class="row-fluid">
		<div class="span6">		
			<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_STATUS');?></strong>
		</div>
		
		<div class="span6">
		<?php 
		if (JArrayHelper::getValue($index, 'status')) :
			echo JText::_("MOD_JSOLRCONNECTIONMONITOR_CONNECTED");
		else :		
			echo JText::_("MOD_JSOLRCONNECTIONMONITOR_NOT_CONNECTED");
		endif;
		?>
		</div>
	</div>
	
	<?php foreach (array('host', 'port', 'path') as $setting) : ?>
	<div class="row-fluid">
		<div class="span6">		
			<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_'.JString::strtoupper($setting));?></strong>
		</div>
		
		<div class="span6">
			<?php echo JArrayHelper::getValue($index, $setting); ?>
		</div>
	</div>
	<?php endforeach; ?>

	<?php if ($details = JArrayHelper::getValue($index, 'details')) : ?>
	<div class="row-fluid">
		<div class="span6">
			<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_NUMDOCS'); ?></strong>
		</div>
		
		<div class="span6">
			<?php echo $details->numDocs; ?>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span6">
			<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_LASTINDEXED'); ?></strong>
		</div>
		
		<div class="span6">
			<i class="icon-calendar"></i> <?php echo JHtml::_('date', $details->lastModified, JText::_('DATE_FORMAT_LC2')); ?>
		</div>
	</div>
	<?php endif; ?>
	
	<?php if ($libs = JArrayHelper::getValue($index, 'libraries')) : ?>
		<?php if (!JArrayHelper::getValue($libs, 'curl')) : ?>
		<div class="row-fluid">
			<div class="span6">
				<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_CURL_NOT_INSTALLED'); ?></strong>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if (!JArrayHelper::getValue($libs, 'jsolr')) : ?>
		<div class="row-fluid">
			<div class="span6">
				<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_JSOLR_NOT_INSTALLED'); ?></strong>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>
</div>