<?php
/**
 * @version		$LastChangedBy: spauldingsmails $
 * @package		Wijiti
 * @subpackage	JSolrSearch
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
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
	<div class="jsolr-result-title">
		<span class="jsolr-result-location">[<?php echo $item->contentType; ?>]</span>
		<a href="<?php echo $item->href; ?>"><?php echo $item->title; ?></a>
	</div>
	<div class="jsolr-result-description"><?php echo $item->text; ?></div>
	<div class="jsolr-result-location"><?php echo JHTML::_("image.site", "attachment.gif", "media/com_jsolrsearch/images/", null, null, JText::_("Attached to"), array("title"=>JText::_("Attached to"))); ?><?php echo JHTML::link($item->parentHref, $item->parentTitle); ?></div>
</div>