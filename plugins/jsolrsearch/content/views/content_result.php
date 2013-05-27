<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.

   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch Component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com>
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="jsolr-result">
	<div class="jsolr-result-title"><a href="<?php echo  $this->item->link; ?>"><?php echo JSolrHelper::highlight($this->item->key, 'title', $this->item->title); ?></a></div>
	
	<?php if ($this->item->created) : ?>
	<div class="jsolr-result-date">
		<span class="jsolr-date-label"><?php echo JText::_("COM_JSOLRSEARCH_RESULT_CREATED_LABEL"); ?>:</span><?php echo JSolrHelper::datetime($this->item->created); ?>
	</div>
	<?php endif; ?>

	<?php if ($this->item->modified) : ?>
	<div class="jsolr-result-date">			
		<span class="jsolr-date-label"><?php echo JText::_("COM_JSOLRSEARCH_RESULT_MODIFIED_LABEL"); ?>:</span><?php echo JSolrHelper::datetime($this->item->modified); ?>
	</div>
	<?php endif; ?>

	<div class="jsolr-result-description"><?php echo JSolrHelper::highlight($this->item->key, 'body_en'); ?></div>
	<div class="jsolr-result-location"><?php echo $this->item->category; ?></div>
</div>