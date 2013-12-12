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
<article class="jsolrsearch-result">
	<header>
		<h4>
			<a href="<?php echo JRoute::_($this->item->link); ?>"><?php echo JSolrHelper::highlight($this->hl, 'title', $this->item->title); ?></a>
		</h4>
	</header>

	<footer>
		<dl>
			<dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_AUTHORS_LABEL"); ?></dt>			
			<dd>
			<?php echo (is_array($this->item->author)) ? implode('</dd><dd>', $this->item->author) : $this->item->author; ?>
			</dd>
		
			<?php if ($this->item->{'dc.date.available_dt'}) : ?>
			<dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_ARCHIVED_LABEL"); ?></dt>
			<dd>
				<time datetime="<?php echo $this->item->{'dc.date.available_dt'}; ?>"><?php echo $this->item->{'dc.date.available_dt'}; ?></time>
			</dd>
			<?php endif; ?>
			
			<?php if (isset($this->item->link)) : ?>
			<dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_LINK_LABEL"); ?></dt>
			<dd>
				<a href="<?php echo JRoute::_($this->item->link); ?>"><?php echo JRoute::_($this->item->link); ?></a>
			</dd>
			<?php endif; ?>
		</dl>
	</footer>
</article>