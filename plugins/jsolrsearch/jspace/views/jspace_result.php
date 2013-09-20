<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch JSpace plugin for Joomla!.

   The JSolrSearch JSpace plugin for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch JSpace plugin for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch JSpace plugin for Joomla!.  If not, see 
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
	<div class="jsolr-result-title"><a href="<?php echo $this->item->link; ?>"><?php echo JSolrHelper::highlight($this->hl, 'title', $this->item->title); ?></a></div>
	
	<div class="jsolr-result-author"><?php echo (is_array($this->item->author)) ? implode(', ', $this->item->author) : $this->item->author; ?></div>
	
	<?php if ($this->item->{'dc.date.available_dt'}) : ?>
	<div class="jsolr-result-date">
		<span class="jsolr-date-label"><?php echo JText::_("COM_JSOLRSEARCH_RESULT_ARCHIVED_LABEL"); ?>:</span><?php echo $this->item->{'dc.date.available_dt'}; ?>
	</div>
	<?php endif; ?>
	
	<?php if (isset($this->item->link)) : ?>
	<div class="jsolr-result-link"><a href="<?php echo $this->item->link; ?>"><?php echo $this->item->link; ?></a></div>
	<?php endif; ?>
</div>