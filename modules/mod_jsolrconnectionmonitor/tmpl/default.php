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
 * Hayden Young					<hayden@knowledgearc.com> 
 * 
 */
defined('_JEXEC') or die;

?>

<div class="row-striped">

	<div class="row-fluid">

		<div class="span6">		

			<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_STATUS');?></strong>

		</div>

		
		<div id="jsolrStatus" class="span6">
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
	
	<div class="row-fluid">
		<div class="span6">
			<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_NUMDOCS'); ?></strong>
		</div>
		
		<div id="jsolrNumDocs" class="span6">
			<?php 
			if ($statistics = JArrayHelper::getValue($index, 'statistics')) :
				echo $statistics->numDocs;
			else :
				echo JText::_('MOD_JSOLRCONNECTIONMONITOR_NOVALUE');
			endif;
			?>			
		</div>
	</div>

	<div class="row-fluid">
		<div class="span6">
			<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_LASTINDEXED'); ?></strong>
		</div>
		
		<div class="span6" id="jsolrLastModified">
			<?php 
			if ($statistics = JArrayHelper::getValue($index, 'statistics')) :
				echo JHtml::_('date', $statistics->lastModified, JText::_('DATE_FORMAT_LC2'));
			else :
				echo JText::_('MOD_JSOLRCONNECTIONMONITOR_NOVALUE');
			endif;
			?>
		</div>
	</div>	
	
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

	<?php if ($extractor = JArrayHelper::getValue($index, 'extractor')) : ?>
		<?php if (JArrayHelper::getValue($extractor, 'type')) : ?>
		<div class="row-fluid">
			<div class="span6">
				<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_JSOLR_EXTRACTOR'); ?></strong>
			</div>
			
			<div class="span6">
				<?php echo JArrayHelper::getValue($extractor, 'type'); ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if (JArrayHelper::getValue($extractor, 'type') == 'local') : ?>
		<div class="row-fluid">
			<div class="span6">
				<strong class="row-title"><?php echo JText::_('MOD_JSOLRCONNECTIONMONITOR_JSOLR_EXTRACTOR_LOCAL'); ?></strong>
			</div>
			
			<div class="span6">
				<?php echo JArrayHelper::getValue($extractor, 'path'); ?>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>

</div>