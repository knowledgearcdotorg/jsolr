<?php
/**
 * @author		$LastChangedBy$
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

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.mootools');
JHTML::_('behavior.calendar');

$document = JFactory::getDocument();
$document->addScript(JURI::base()."/media/mod_jsolrfilter/js/jsolrfilter.js");
$document->addStyleSheet(JURI::base()."/media/mod_jsolrfilter/css/jsolrfilter.css");
?>
<div id="jSolrOptions">
	<ul>
	<?php 
	foreach (modJSolrFilterHelper::getFilterOptions() as $key=>$value) : 
		if ($value) :
	?>
		<li class="jsolr-filter-item jsolr-filter-option"><?php echo $value; ?></li>
	<?php
		endif; 
	endforeach;
	?>
	</ul>
</div>

<?php
modJSolrFilterHelper::renderFilterContext();
?>