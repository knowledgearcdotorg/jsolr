<?php
/**
 * Provides the base for the search results display.
 * 
 * Loads the form, facet filters, facets, results and pagination templates.
 * 
 * @copyright	Copyright (C) 2012-2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.
 *
 *   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
 *   and/or modify it under the terms of the GNU General Public License as 
 *   published by the Free Software Foundation, either version 3 of the License, 
 *   or (at your option) any later version.
 *
 *   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
 *   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with the JSolrSearch Component for Joomla!.  If not, see 
 *   <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Hayden Young <hayden@knowledgearc.com>
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com>
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$document = JFactory::getDocument();

$document->addScript(JURI::base().'/media/jsolr/js/dropdown.js');

$document->addStyleSheet(JURI::base().'/media/com_jsolrsearch/css/jsolrsearch.css');
$document->addStyleSheet(JURI::base().'/media/jsolr/css/dropdown.css');
?>

<!-- This is not well styled but rather provides the functionality for 
integrating faceting within the component. Use template overrides to improve. -->
<?php if ($this->params->get('facets_embed')) : ?>
<section id="jsolrFacetFilters">
	<?php
	if ($module = JModuleHelper::getModule('mod_jsolrfilter')) {
		$renderer = $document->loadRenderer('module');
		echo $renderer->render($module);
	}
	?>
</section>
<?php endif; ?>

<section id="jsolrSearchResults">
	<header>
		<?php echo $this->loadTemplate('form'); ?>
	
		<div id="jsolrFacetfilters">
		   <?php echo $this->loadTemplate('appliedfilters'); ?>
		</div>
	</header>

	<?php if (!is_null($this->items)): ?>
		<?php echo $this->loadResultsTemplate(); ?>
	<?php endif ?>
		      
	<footer>
		<div class="pagination">
		<?php echo $this->get('Pagination')->getPagesLinks(); ?>
		</div>
	</footer>
</section>