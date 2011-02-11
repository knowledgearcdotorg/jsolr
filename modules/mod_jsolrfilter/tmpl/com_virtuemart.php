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

defined('_JEXEC') or die('Restricted access');


JPluginHelper::importPlugin("jsolrsearch", "jsolrvirtuemart");
$dispatcher =& JDispatcher::getInstance();
			
$array = $dispatcher->trigger('onPrepareFacets', array($helper->getLang()));
$facets = JArrayHelper::getValue($array, 0);

$array = $dispatcher->trigger('onPrepareCurrency', array($helper->getLang()));
$currency = JArrayHelper::getValue($array, 0);
?>

<div id="jSolrSearchCategories" class="jsolr-context-filter">
	<ul>
		<?php
		if (!JRequest::getString("fcat", null)) {
			$link = "#";
			$class = "jsolr-fo-selected";	
		} else {
			$url = new JURI($helper->getSearchURL());
			$url->delVar("qdr");
			$url->delVar("fcat");
			
			$link = $url->toString();
			
			$class = "";
		}
		?>	
		<li class="jsolr-filter-item"><a href="<?php echo $link; ?>" class="<?php echo $class ?>"><?php echo JText::_("MOD_JSOLRFILTER_OPTION_ALL_CATEGORIES"); ?></a></li>
		<?php foreach ($facets as $key=>$value) : ?>
			<?php
			if ($key == JRequest::getString("fcat")) {
				$link = "#";
				$class = "jsolr-fo-selected";
			} else {
				$url = new JURI($helper->getSearchURL());
				$url->delVar("qdr");
				$url->delVar("dmin");
				$url->delVar("dmax");
				$url->setVar("fcat", $key);
				
				$link = $url->toString();
				
				$class = "";
			}
			?>
		<li class="jsolr-filter-item"><a href="<?php echo $link; ?>" class="<?php echo $class ?>"><?php echo $key; ?></a><span class="jsolr-cat-count">(<?php echo $value; ?>)</span></li>
		<?php endforeach; ?>
	</ul>
</div>

<div id="jSolrSearchPrices" class="jsolr-context-filter">
	<ul>
		<?php
		if (!JRequest::getString("pmin", null) && !JRequest::getString("pmax", null)) {
			$link = "#";
			$class = "jsolr-fo-selected";	
		} else {
			$url = new JURI($helper->getSearchURL());
			$url->delVar("qdr");
			$url->delVar("dmin");
			$url->delVar("dmax");
			$url->delVar("pmin");
			$url->delVar("pmax");
			
			$link = $url->toString();
			
			$class = "";
		}
		?>
		<li class="jsolr-filter-item"><a href="<?php echo $link; ?>" class="<?php echo $class ?>"><?php echo JText::_("MOD_JSOLRFILTER_OPTION_ANY_PRICE"); ?></a></li>	

		<?php
		$range = null;
		
		$tag = "<span class=\"jsolr-virtuemart-currency\">%s</span>%s";

		if (JRequest::getString("pmin", "") !== "" && JRequest::getString("pmax", "") === "") {
			$min = JText::sprintf($tag, $currency, JRequest::getString("pmin"));
			$range = JText::sprintf("MOD_JSOLRFILTER_PRICE_RANGE_HIGHER", $min);
		}

		if (JRequest::getString("pmin", "") === "" && JRequest::getString("pmax", "") !== "") {
			$max = JText::sprintf($tag, $currency, JRequest::getString("pmax"));
			$range = JText::sprintf("MOD_JSOLRFILTER_PRICE_RANGE_LOWER", $max);
		}
		
		if (JRequest::getString("pmin", "") !== "" && JRequest::getString("pmax", "") !== "") {
			$min = JText::sprintf($tag, $currency, JRequest::getString("pmin"));
			$max = JText::sprintf($tag, $currency, JRequest::getString("pmax"));
			$range = JText::sprintf("MOD_JSOLRFILTER_PRICE_RANGE_TO", $min, $max);
		}

		if ($range) :
		?>
		<li class="jsolr-filter-item"><a href="#" class="jsolr-fo-selected"><?php echo $range; ?></a></li>
		<?php 
		endif;
		?>
	</ul>
	
	<form 
		id="jSolrPriceRange" 
		name="jSolrPriceRange"
		method="post"
		action="<?php echo $helper->getFormURL(array("q", "lr", "option", "o", "view", "fcat", "Itemid")); ?>">
		<div>
			<label><?php echo JText::_("MOD_JSOLRFILTER_PRICE_MIN"); ?>:</label>
			<span class="jsolr-virtuemart-currency"><?php echo $currency; ?></span><input type="text" name="pmin" id="pmin" value="<?php echo JRequest::getString("pmin", ""); ?>" size="6"/>
		</div>
		<div>
			<label><?php echo JText::_("MOD_JSOLRFILTER_PRICE_MAX"); ?>:</label>
			<span class="jsolr-virtuemart-currency"><?php echo $currency; ?></span><input type="text" name="pmax" id="pmax" value="<?php echo JRequest::getString("pmax", ""); ?>" size="6"/>
		</div>
		<button type="submit"><?php echo JText::_("MOD_JSOLRFILTER_SEARCH"); ?></button>

		<input type="hidden" name="ccode" value="<?php echo $currency; ?>"/>		
	</form>
</div>