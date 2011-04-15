<?php
/**
 * @author		$LastChangedBy: spauldingsmails $
 * @package		JSolr
 * @copyright	Copyright (C) 2011 Wijiti Pty Ltd. All rights reserved.
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
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */
?>

<div id="jSolrSearchDates" class="jsolr-context-filter">
	<ul>
		<li id="jSolrAnytime" class="jsolr-filter-item jsolr-range<?php if ($helper->isDateRangeSelected("ANYTIME")) : echo " jsolr-range-selected"; endif; ?>"><?php echo $helper->getDateLink("ANYTIME"); ?></li>
		<li id="jSolr1d" class="jsolr-filter-item jsolr-range<?php if ($helper->isDateRangeSelected("1D")) : echo " jsolr-range-selected"; endif; ?>"><?php echo $helper->getDateLink("1D"); ?></li>
		<li id="jSolr1w" class="jsolr-filter-item jsolr-range<?php if ($helper->isDateRangeSelected("1W")) : echo " jsolr-range-selected"; endif; ?>"><?php echo $helper->getDateLink("1W"); ?></li>
		<li id="jSolr1m" class="jsolr-filter-item jsolr-range<?php if ($helper->isDateRangeSelected("1M")) : echo " jsolr-range-selected"; endif; ?>"><?php echo $helper->getDateLink("1M"); ?></li>
		<li id="jSolr1y" class="jsolr-filter-item jsolr-range<?php if ($helper->isDateRangeSelected("1Y")) : echo " jsolr-range-selected"; endif; ?>"><?php echo $helper->getDateLink("1Y"); ?></li>
		<li id="jSolrCustom" class="jsolr-filter-item jsolr-range"><?php echo $helper->getCustomRangeLink(); ?></li>
	</ul>
	<form 
		id="jSolrDateRange" 
		name="jSolrDateRange"
		method="post"
		action="<?php echo $helper->getFormURL(); ?>"
		class="<?php echo $helper->isCustomRangeSelected() ? "jsolr-show" : "jsolr-hide"; ?>">
		<div>
			<label>From:</label>
			<?php echo JHTML::_('calendar', JRequest::getVar("dmin"), 'dmin', 'dmin', '%Y-%m-%d', array("size"=>10)); ?>
		</div>
		<div>
			<label>To:</label>
			<?php echo JHTML::_('calendar', JRequest::getVar("dmax"), 'dmax', 'dmax', '%Y-%m-%d', array("size"=>10)); ?>
		</div>
		<div class="jsolr-example">eg. 2010-01-26</div>
		<button type="submit"><?php echo JText::_("MOD_JSOLRFILTER_SEARCH"); ?></button>
	</form>
</div>