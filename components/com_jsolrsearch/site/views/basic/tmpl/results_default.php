<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2010-2012 Wijiti Pty Ltd. All rights reserved.
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

<form action="<?php echo JRoute::_(JURI::base()."index.php?option=com_jsolrsearch&task=search"); ?>" method="post" name="adminForm" class="jsolr-search-result-form">
	<div class="jsolr-query">
		<input type="text" name="q" id="q" value="<?php echo htmlspecialchars($this->state->get('query.q')); ?>" class="jsolr-result-query"/><button type="submit" class="jsolr-search-button">Search</button>
	</div>
	
	<div class="jsolr-advanced-link">
		<a href="<?php echo $this->get("AdvancedSearchURL"); ?>"><?php echo JText::_("Advanced search"); ?></a>	
	</div>	
        
        <?php
            $modules =& JModuleHelper::getModules('position-jsolrsearch');
            
            foreach ($modules as $module){
                
                echo JModuleHelper::renderModule($module);
            }
        ?>

	<?php if ($this->get("Total") > 0) : ?>
	<div class="jsolr-total-results"><?php echo JText::sprintf("COM_JSOLRSEARCH_TOTAL_RESULTS", $this->get("Total"), $this->get("QTime")); ?></div>
	<?php endif; ?>
	
	<?php if ($this->get("Total") == 0) : ?>
	<div class="jsolr-no-results"><?php echo JText::_("COM_JSOLRSEARCH_NO_RESULTS"); ?></div>
	<?php endif; ?>
        
        <?php
        JHTML::_('behavior.mootools');
        JHTML::_('behavior.calendar');

        $document = JFactory::getDocument();
        $document->addScript('http://code.jquery.com/jquery.min.js');
        $document->addScript(JURI::base()."/media/com_jsolrsearch/js/site/jsolrfiltertoolbar.js");
        $document->addStyleSheet(JURI::base()."/media/com_jsolrsearch/css/jsolrfiltertoolbar.css");
        ?>
        <div id="jSolrOptions">
                <ul>
                <?php 
                $jSorlSearchParams = &JComponentHelper::getParams( 'com_jsolrsearch' );
                $FilterCount = $jSorlSearchParams->get('filter_count');

                foreach (JSolrSearchHelperToolbar::getFilterOptions() as $key=>$value) : 
                        if ($value and $key < $FilterCount) :
                ?>
                        <li class="jsolr-filter-item jsolr-filter-option"><?php echo $value; ?></li>
                <?php
                        endif; 
                endforeach;
                ?>
                </ul>
        </div>

        <?php
        JSolrSearchHelperToolbar::renderFilterContext();
        ?>

        <div id="jSolrSearchDates" class="jsolr-context-filter">
                <ul>
                        <?php
                            $Ranges = array('Anytime','1d','1w','1m','1y') ;

                            foreach( $Ranges as $Range ) {

                                if (JSolrSearchHelperToolbar::isDateRangeSelected(strtoupper($Range))) {

                                    echo '<li id="jSolr'.$Range.'" class="jsolr-filter-item jsolr-range jsolr-range-selected">'.JSolrSearchHelperToolbar::getDateLink(strtoupper($Range)).'</li>' ;
                                    break ;
                                }
                            }
                        ?>



                        <li id="jSolrShowRanges" class="jsolr-filter-item jsolr-range"><a id="jSolrShowRange"><img src="/media/com_jsolrsearch/images/arrow-down.png" /></a></li>
                        <div class="jSolrRanges jsolr-hide">

                            <?php 
                                foreach( $Ranges as $Range ) {

                                    if (JSolrSearchHelperToolbar::isDateRangeSelected(strtoupper($Range))) {

                                        continue ;
                                    } else {
                                        echo '<li id="jSolr'.$Range.'" class="jsolr-filter-item jsolr-range">'.JSolrSearchHelperToolbar::getDateLink(strtoupper($Range)).'</li>' ;
                                    }
                                }
                            ?>

                            <li id="jSolrCustom" class="jsolr-filter-item jsolr-range"><?php echo JSolrSearchHelperToolbar::getCustomRangeLink(); ?></li>

                            <form 
                                    id="jSolrDateRange" 
                                    name="jSolrDateRange"
                                    method="post"
                                    action="<?php echo JSolrSearchHelperToolbar::getFormURL(); ?>"
                                    class="<?php echo JSolrSearchHelperToolbar::isCustomRangeSelected() ? "jsolr-show" : "jsolr-hide"; ?>">
                                    <div>
                                            <label>From:</label>
                                            <?php echo JHTML::_('calendar', JRequest::getVar("dmin"), 'dmin', 'dmin', '%Y-%m-%d', array("size"=>10)); ?>
                                    </div>
                                    <div>
                                            <label>To:</label>
                                            <?php echo JHTML::_('calendar', JRequest::getVar("dmax"), 'dmax', 'dmax', '%Y-%m-%d', array("size"=>10)); ?>
                                    </div>
                                    <div class="jsolr-example">eg. 2010-01-26</div>
                                    <button type="submit"><?php echo JText::_("COM_JSOLR_TOOLBAR_SEARCH"); ?></button>
                            </form>
                        </div>
                </ul>
        </div>
	
	<div class="jsolr-results">
	<?php	
	foreach ($this->items as $item) :
		echo $this->loadResultTemplate($item);
	endforeach;
	?>
	</div>

	<?php if (JRequest::getWord("lr")) : ?>
	<input type="hidden" name="lr" value="<?php echo JRequest::getWord("lr", ""); ?>"/>
	<?php endif; ?>
	
	<?php if (JRequest::getWord("o")) : ?>
	<input type="hidden" name="o" value="<?php echo JRequest::getWord("o", ""); ?>"/>
	<?php endif; ?>
	
	<?php if (JRequest::getWord("qdr")) : ?>
	<input type="hidden" name="qdr" value="<?php echo JRequest::getWord("qdr", ""); ?>"/>
	<?php endif; ?>
	
	<?php echo JHTML::_('form.token'); ?>
</form>	
	
<div class="pagination">
	<?php echo $this->get("Pagination")->getPagesLinks(); ?>
</div>