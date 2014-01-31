<?php
/**
 * Provides a list of facet filters applied to the current search results.  
 * 
 * @copyright	Copyright (C) 2011-2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolr filter module for Joomla!.

   The JSolr filter module for Joomla! is free software: you can 
   redistribute it and/or modify it under the terms of the GNU General Public 
   License as published by the Free Software Foundation, either version 3 of 
   the License, or (at your option) any later version.

   The JSolr filter module for Joomla! is distributed in the hope 
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
 * Michał Kocztorz				<michalkocztorz@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

$form = $this->get('Form');
?>

<?php if (!is_null($form)): ?>
<ul>
	<?php foreach ($this->get('AppliedFacetFilters') as $field): ?>
	<?php
	$uri = clone JURI::getInstance();
	$uri->delVar($field->name);
	?>
	<li>
		<span class="jsolr-label"><?php echo $field->label; ?></span>
		<span class="jsolr-value"><?php echo str_replace('|', ' | ', $field->value); ?></span>

		<?php echo JHTML::link((string)htmlentities($uri), '(clear)'); ?>
	</li>
	<?php endforeach ?>
	
	<?php foreach ($this->get('AppliedAdvancedFilters') as $field): ?>
	<?php
	$uri = clone JURI::getInstance();
	$uri->delVar($field->name);
	?>
	<li>
		<span class="jsolr-label"><?php echo JText::_(strtoupper("COM_JSOLRSEARCH_FILTERS_".$field->name."_".$field->value)); ?></span>

		<?php echo JHTML::link((string)htmlentities($uri), '(clear)'); ?>
	</li>
	<?php endforeach ?>
</ul>
<?php endif ?>
<div class="clr"></div>