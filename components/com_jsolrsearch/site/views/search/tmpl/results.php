<?php
/**
 * Provides the base for the search results display.
 *
 * Loads the form, facet filters, facets, results and pagination templates.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.framework');

JHtml::_('behavior.calendar');

$jsolrsearchJs = "jsolrsearch.js";

if (version_compare(JVERSION, "3.0", "ge")) {
    $jsolrsearchJs = "jsolrsearch.jquery.js";

    JHtml::_('bootstrap.framework');
}

$document = JFactory::getDocument();

$document->addScript(JURI::base().'media/jsolr/js/dropdown.js');

$document->addScript(JURI::base().'media/com_jsolrsearch/js/'.$jsolrsearchJs);

$document->addStyleSheet(JURI::base().'media/com_jsolrsearch/css/jsolrsearch.css');

$document->addStyleSheet(JURI::base().'media/jsolr/css/dropdown.css');
?>

<!-- This is not well styled but rather provides the functionality for

integrating faceting within the component. Use template overrides to improve. -->

<?php if ($this->params->get('facets_embed')) : ?>

<section id="jsolrFacetFilters">

    <?php
    if ($module = JModuleHelper::getModule('mod_jsolrfilter')) :
        $renderer = $document->loadRenderer('module');

        echo $renderer->render($module);
    endif;
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

    <footer>
        <div class="pagination">

        <?php echo $this->get('Pagination')->getPagesLinks(); ?>

        </div>
    </footer>

    <?php endif ?>

</section>
