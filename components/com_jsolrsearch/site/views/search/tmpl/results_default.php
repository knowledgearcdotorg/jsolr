<?php
/**
 * Provides a default results template.
 *
 * Includes total number of records, spelling suggestions and the list of
 * search results.
 *
 *  Override this template to customize the results display (does not affect
 *  the display of an individual result (use results_result or
 *  results_<plugin>).
 *
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
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
 * Hayden Young					<hayden@knowledgearc.com>
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$featuredItems = $this->get('FeaturedItems');
?>

<div id="jsolr-total">
<?php if ($this->get("Pagination")->get('pages.current') > 1) : ?>
	<?php echo JText::sprintf('COM_JSOLRSEARCH_TOTAL_RESULTS_CURRENTPAGE', $this->get("Pagination")->get('pages.current'), $this->items->get('numFound'), $this->items->get('qTimeFormatted')); ?>
<?php else : ?>
	<?php echo JText::sprintf('COM_JSOLRSEARCH_TOTAL_RESULTS', $this->items->get('numFound'), $this->items->get('qTimeFormatted')); ?>
<?php endif; ?>
</div>

<?php
if ($this->items->getSuggestions()) :
	foreach ($this->get("SuggestionQueryURIs") as $item) :
	?>
	<div>Did you mean <a href="<?php echo JArrayHelper::getValue($item, 'uri'); ?>"><?php echo JArrayHelper::getValue($item, 'title'); ?></a></div>
	<?php
	endforeach;
endif;
?>

<?php if (!count($this->items)) : ?>
<span><?php JText::_("COM_JSOLRSEARCH_NO_RESULTS"); ?></span>
<?php endif; ?>

<?php if ($this->get("Pagination")->get('pages.current') == 1 && $featuredItems->get("numFound")) : ?>
    <?php echo $this->loadResultTemplate($featuredItems->getIterator()->current(), $featuredItems->getHighlighting()->{$featuredItems->getIterator()->current()->key}); ?>
<?php endif; ?>

<ol>
	<?php foreach ($this->items as $item) : ?>
	<li>
		<?php echo $this->loadResultTemplate($item, $this->items->getHighlighting()->{$item->key}); ?>
	</li>
	<?php endforeach; ?>
</ol>