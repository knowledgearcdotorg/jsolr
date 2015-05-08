<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JSolr\Form\Fields;

/**
 * A filter interface.
 *
 * Implement this interface when the form field must provide filters for the
 * query (E.g. Solr field fq).
 */
interface Filterable
{
	/**
	 * Gets a array of currently selected filters for the field.
	 *
	 * Array must contain valid Solr fq values:
	 *
	 * E.g.
	 *
	 * $filter = array();
	 * $filter[] = "title:welcome";
	 *
	 * @return array An array of currently selected filters for the field.
	 */
	function getFilters();
}