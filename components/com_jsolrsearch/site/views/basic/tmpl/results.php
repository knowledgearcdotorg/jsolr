<?php
/**
 * @version		$LastChangedBy$
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

$results = $this->get("Results");
?>
<form action="index.php?option=com_jsolrsearch&task=search" method="post" name="adminForm" class="jsolr-search-result-form">
	<div>
		<input type="text" name="q" id="q" value="<?php echo htmlspecialchars($this->get("Query")); ?>" class="jsolr-result-query"/><button type="submit" class="jsolr-search-result-button">Search</button>
	</div>
	<div>
		<a href="<?php echo $this->get("AdvancedSearchURL"); ?>" class="jsolr-advanced-link"><?php echo JText::_("Advanced search"); ?></a>	
	</div>
	<?php echo JHTML::_('form.token'); ?>
</form>

<?php if ($this->get("Total") > 0) : ?>
<div class="jsolr-total-results"><?php echo JText::sprintf("COM_JSOLRSEARCH_TOTAL_RESULTS", $this->get("Total")); ?></div>
<?php else: ?>
<div class="jsolr-no-results"><?php echo JText::_("COM_JSOLRSEARCH_NO_RESULTS"); ?></div>
<?php endif; ?>

<?php foreach ($results as $item) : ?>
<div class="jsolr-results">
	<div class="jsolr-result">
		<div class="jsolr-result-title"><a href="<?php echo $item->href; ?>"><?php echo $item->title; ?></a></div>
		<div class="jsolr-result-date"><?php echo $item->created; ?></div>
		<div class="jsolr-result-description"><?php echo $item->text; ?></div>
		<div class="jsolr-result-location"><?php echo $item->location; ?></div>
	</div>
</div>
<?php endforeach; ?>

<div class="jsolr-pagination">
	<?php echo $this->get("Pagination")->getPagesLinks(); ?>
</div>