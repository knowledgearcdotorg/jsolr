<?php
/**
 * @package		JSolr.Module
 * @copyright	Copyright (C) 2014 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolr Latest Items module for Joomla!.

   The JSolr Latest Items module for Joomla! is free software: you can
   redistribute it and/or modify it under the terms of the GNU General Public
   License as published by the Free Software Foundation, either version 3 of
   the License, or (at your option) any later version.

   The JSolr Latest Items module for Joomla! is distributed in the hope
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
 * Hayden Young					<hayden@knowledgearc.com>
 *
 */

jimport('jsolr.search.factory');

class ModJSolrLatestHelper
{
	public function getItems($params)
	{
		$results = array();

		if (class_exists('\JSolr\Search\Factory')) {
			try {
				$client = \JSolr\Search\Factory::getService();

				$query = \JSolr\Search\Factory::getQuery('*:*')
					->limit($params->get('count', 5))
					->sort($params->get('ordering', 'modified').' desc')
					->useQueryParser("edismax")
					->retrieveFields("*,score");

				if ($limit = $params->get('o')) {
					$query->filters('extension:'.$limit);
				}

				if ($fq = $params->get('fq', null)) {
					$filters = array();
					$parts = explode(',', $fq);

					foreach ($parts as $part) {
						$filters[] = trim($part);
					}

					$query->mergeFilters($filters);
				}

				$results = $query->search();
			} catch (Exception $e) {

			}
		}

		return $results;
	}
}