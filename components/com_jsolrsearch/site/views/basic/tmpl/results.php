<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
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
 * @author Hayden Young <haydenyoung@wijiti.com>
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com>
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$document = JFactory::getDocument();
$document->addScript(JURI::base().'/media/com_jsolrsearch/js/jquery/jquery.js');
$document->addScript(JURI::base().'/media/com_jsolrsearch/js/jsolrsearch.js');

$document->addStyleSheet(JURI::base().'/media/com_jsolrsearch/css/jsolrsearch.css');

$document->addScriptDeclaration('
jQuery(document).ready(function() {
	var jsolrsearch_autocomplete_url = "'.JRoute::_('index.php?option=jsolrsearch&view=basic').'";
	var jsolrsearch_search_url = "'.JRoute::_('index.php?option=jsolrsearch&view=basic').'";
});
');
?>

<div class="jsolr-content jsolr-main">
	<?php echo $this->loadFormTemplate()?>

	<div id="jsolr-facet-filters-selected">
	   <?php echo $this->loadFacetFiltersSelectedTemplate() ?>
	</div>

	<?php if( $this->showFilters ): ?>
	   <div class="jsolr-filters">
	      <?php echo $this->loadFacetFiltersTemplate(); ?>
	   </div>
	<?php endif; ?>


	<?php if( $this->showFilters ): ?>
	<div class="jsolr-filters-results">
	<?php endif; ?>

		
		   <div class="jsolr-results">
		   	<?php if (!is_null($this->items)): ?>
		      <?php echo $this->loadResultsTemplate(); ?>
		    <?php endif ?>
		   </div>
		      
		   <div class="pagination jsolr-pagination">
		      <?php echo $this->get('Pagination')->getPagesLinks(); ?>
		   </div>

	<?php if( $this->showFilters ): ?>
	</div>
	<?php endif; ?>
</div>