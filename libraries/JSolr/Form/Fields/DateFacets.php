<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JSolr\Form\Fields;

use \JFactory as JFactory;
use \JArrayHelper as JArrayHelper;
use \JString as JString;

\JLoader::import('joomla.form.helper');
\JFormHelper::loadFieldClass('list');

/**
 * The DateFacets form field builds a specialized list of date
 * facets which a user can then apply to the current search result set to
 * narrow their search by a particular date.
 *
 * By default, DateFacets will build a list of facets by year.
 */
class DateFacets extends Facets
{
	const RANGE_GAP = "+1YEAR";

	protected $type = 'JSolr.DateFacets';

	private $ranges = null;

	/**
	 * (non-PHPdoc)
	 * @see JSolrFormFieldFacets::getFacetParams()
	 */
	public function getFacetParams()
	{
		$array = array();

		$array[] = array("f.".$this->facet.".facet.range.start"=>$this->min);
		$array[] = array("f.".$this->facet.".facet.range.end"=>$this->max);
		$array[] = array("f.".$this->facet.".facet.range.gap"=>$this->gap);
		$array[] = array("facet.range"=>$this->facet);

		return $array;
	}

	/**
	 * Gets an array of facets from the current search results (provided via the
	 * user's session).
	 *
	 * @return array An array of facets from the current search results.
	 */
	protected function getFacets()
	{
		$array = array();

		if ($facet = $this->facet) {
			$app = JFactory::getApplication('site');
			$facets = $app->getUserState('com_jsolrsearch.facets.ranges', null);

			if (isset($facets->{$facet}->counts)) {
				foreach ($facets->{$facet}->counts as $key=>$value) {
					$parts = explode('-', $key);

					$array[JArrayHelper::getValue($parts, 0)] = $value;
				}
			}
		}

		return $array;
	}

	/**
	 * (non-PHPdoc)
	 * @see JSolrFilterable::getFilters()
	 */
	public function getFilters()
	{
		$cleaned = JString::trim($this->value);
		$array = explode(self::FACET_DELIMITER, $cleaned);
		$filters = array();

		if ($cleaned) {
			for ($i = 0; $i < count($filters); $i++) {
				if ($this->exactmatch) {
					$filters[$i] = '"'.$filters[$i].'"';
				}

				$filters[$i] = $this->filter.":".$filters[$i].'*';
			}
		}

		return (count($filters)) ? $filters : array();
	}

	private function _loadDefaultRanges()
	{
		if (!$this->ranges) {
			$params = array();
			$params["stats"] = "true";
			$params["stats.field"] = $this->filter;

			$query = \JSolr\Search\Factory::getQuery("*:*")
				->mergeParams($params)
				->rows(0);

			$results = $query->search();

			$stats = $results->getStats();

			if (isset($stats->{$this->filter})) {
				$this->ranges = JArrayHelper::fromObject($stats->{$this->filter});
			}
		}
	}

	public function __get($name)
	{
		switch ($name) {
			case 'min':
			case 'max':
				if (!($this->$name = JArrayHelper::getValue($this->element, $name, null, 'string'))) {
					// lazy load defaults.
					$this->_loadDefaultRanges();
					$this->$name = JArrayHelper::getValue($this->ranges, $name);
				}

				return $this->$name;
				break;

			case 'gap':
				if (!($this->$name = JArrayHelper::getValue($this->element, $name, null, 'string'))) {
					$this->$name = self::RANGE_GAP;
				}

				return $this->$name;
				break;

			default:
				return parent::__get($name);
				break;
		}
	}
}