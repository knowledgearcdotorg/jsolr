<?php 
/**
 * A model that provides facet browsing.
 * 
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.
 *
 *   The JSolrSearch component for Joomla! is free software: you can redistribute it 
 *   and/or modify it under the terms of the GNU General Public License as 
 *   published by the Free Software Foundation, either version 3 of the License, 
 *   or (at your option) any later version.
 *
 *   The JSolrSearch component for Joomla! is distributed in the hope that it will be 
 *   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with the JSolrSearch component for Joomla!.  If not, see 
 *   <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com>
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.error.log');
jimport('joomla.language.helper');
jimport('joomla.application.component.modellist');

jimport('jsolr.search.factory');

class JSolrSearchModelAutocomplete extends JModelList
{
   /**
    * @return array
    */
   function getResults($q = '', $fields = '', $show = 'title_ac')
   {
      $query = JSolrSearchFactory::getQuery('*:*')
         ->useQueryParser("edismax")
         ->retrieveFields("*,score")
         ->limit(10) // TODO: move to config
         ->highlight(200, "<strong>", "</strong>", 1);

      $fields = explode(',', $fields);

      $resutls = array();

      if (empty($q)) {
         return $resutls;
      }

      foreach ($fields as &$field) {
         $field = explode('^', $field);
         $field = $field[0];
         $field = $field .':' . $q . '*';
      }

      $filters = implode(' OR ', $fields);

      $query->filters($filters);

      $response = $query->search();
      $response = json_decode($response->getRawResponse());

      foreach ($response->response->docs as $doc) {
         if (is_array($doc->$show)) {
            $v = (array)$doc->$show;
            $resutls[] = $v[0];
         } else {
            $resutls[] = $doc->$show;
         }
      }

      return $resutls;
   }
}
