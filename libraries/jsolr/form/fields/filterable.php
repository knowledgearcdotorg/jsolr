<?php
/**
 * An interface to implement when a class should be filterable.
 * 
 * @package		JSolr
 * @subpackage	Form
 * @copyright	Copyright (C) 2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSpace component for Joomla!.

   The JSpace component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSpace component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSpace component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * @author Hayden Young <haydenyoung@knowledgearc.com>
 */

/**
 * A filter interface.
 * 
 * Implement this interface when the form field must provide filters for the 
 * query (E.g. Solr field fq).
 */
interface JSolrFilterable
{
	/**
	 * Gets a array of currently selected filters for the field.
	 * 
	 * @return array An array of currently selected filters for the field.
	 */
	function getFilters();
}