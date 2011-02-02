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

$client = $helper->getSolrClient();
$facetField = "category".$helper->getLang();

$array = array();

try {
	$query = new SolrQuery();
			
	$query->setQuery(JRequest::getString("q"));

	$query->addFilterQuery("option:com_virtuemart");
	
	$query->setFacet(true);
	$query->addFacetField($facetField);
	$query->setFacetLimit(10);
	$query->setFacetMinCount(1);
	
	$queryResponse = $client->query($query);

	$response = $queryResponse->getResponse();
	
	$array = JArrayHelper::getValue($response->facet_counts->facet_fields, $facetField, array());

} catch (SolrClientException $e) {
	$log = JLog::getInstance();
	$log->addEntry(array("c-ip"=>"", "comment"=>$e->getMessage()));
}
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
			$url->delVar("pmin");
			$url->delVar("pmax");
			$url->setVar("fcat", "");
			
			$link = $url->toString();
			
			$class = "";
		}
		?>	
		<li class="jsolr-filter-item"><a href="<?php echo $link; ?>" class="<?php echo $class ?>"><?php echo JText::_("MOD_JSOLRFILTER_OPTION_ALL_CATEOGORIES"); ?></a></li>
		<?php foreach ($array as $key=>$value) : ?>
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