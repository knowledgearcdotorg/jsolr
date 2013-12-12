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
<article class="jsolrsearch-result">
	<header>
		<h4>
			<a href="<?php echo JRoute::_($this->item->link); ?>"><?php echo JSolrHelper::highlight($this->item->key, 'title', $this->item->title); ?></a>
		</h4>
	</header>
	<p><?php echo JSolrHelper::highlight($this->item->key, 'body_en'); ?></p>
	<footer>
		<dl>		
			<?php if ($this->item->created) : ?>
			<dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_CREATED_LABEL"); ?></dt>
			<dd>
				<time datetime="<?php echo JFactory::getDate($this->item->created)->toISO8601(); ?>"><?php echo JFactory::getDate($this->item->created)->format(JText::_('DATE_FORMAT_LC2')); ?></time>
			</dd>
			<?php endif; ?>
			
			<?php if ($this->item->modified) : ?>
			<dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_MODIFIED_LABEL"); ?></dt>
			<dd>
				<time datetime="<?php echo JFactory::getDate($this->item->modified)->toISO8601(); ?>"><?php echo JFactory::getDate($this->item->modified)->format(JText::_('DATE_FORMAT_LC2')); ?></time>
			</dd>
			<?php endif; ?>
			
			<?php if (isset($this->item->link)) : ?>
			<dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_LINK_LABEL"); ?></dt>
			<dd>
				<a href="<?php echo JRoute::_($this->item->link); ?>"><?php echo JURI::getInstance()->toString(array('scheme', 'host', 'port')).JRoute::_($this->item->link); ?></a>
			</dd>
			<?php endif; ?>

			<?php if (isset($this->item->category)) : ?>
			<dt><?php echo JText::_("COM_JSOLRSEARCH_RESULT_CATEGORY_LABEL"); ?></dt>
			<dd>
				<a href="<?php echo JRoute::_($this->item->link); ?>"><?php echo $this->item->category; ?></a>
			</dd>
			<?php endif; ?>
		</dl>
	</footer>
</article>