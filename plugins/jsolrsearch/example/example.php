<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
* A plugin for searching JReviews listings.
 *
 * @package     JSolr.Plugins
 * @subpackage  Search
 * @copyright   Copyright (C) 2013 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolr Search JReviews plugin for Joomla!.
 *
 * The JSolr Search JReviews plugin for Joomla! is free software: you can 
 * redistribute it and/or modify it under the terms of the GNU General Public 
 * License as published by the Free Software Foundation, either version 3 of 
 * the License, or (at your option) any later version.
 *
 * The JSolr Search JReviews plugin for Joomla! is distributed in the hope 
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the JSolr Search JReviews plugin for Joomla!.  If not, see 
 * <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Hayden Young <haydenyoung@wijiti.com> 
 * @author Bartłomiej Kiełbasa <bartlomiej.kielbasa@wijiti.com>
 * 
 */

jimport('joomla.error.log');

jimport('jsolr.search.search');

class plgJSolrSearchExample extends JSolrSearchSearch
{
   protected $extension = 'com_example';

   /**
    * Event that returns array with informations about the plugin
    */
   public function onJSolrSearchRegisterComponents()
   {
      return array(
         'name' => 'Example',
         'plugin' => $this->extension,
         'path' => __DIR__ . DS . 'forms' . DS . 'tools.xml'
         // or
         // 'path' => __DIR__ . DS . 'forms' . DS . 'facets.xml'
      );
   }
}
