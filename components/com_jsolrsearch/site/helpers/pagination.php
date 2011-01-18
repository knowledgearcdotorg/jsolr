<?php
/**
 * A customized pagination class to overcome some of the default pagination 
 * limitations.
 * 
 * @author		$LastChangedBy$
 * @package		Wijiti
 * @subpackage	JSolrSearch
 * @copyright	Copyright (C) 2010 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch component for Joomla!.

   The JSolrSearch component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com> 
 * 
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.pagination');

class JSolrSearchPagination extends JPagination
{
	/**
	 * Create and return the pagination data object
	 *
	 * @access	public
	 * @return	object	Pagination data object
	 * @since	1.5
	 */
	function _buildDataObject()
	{
		// Initialize variables
		$data = new stdClass();

		$url = new JURI(JURI::current()."?".http_build_query(JRequest::get('get')));
		
		$data->all	= new JPaginationObject(JText::_('View All'));
		if (!$this->_viewall) {
			$url->delVar("start");
			
			$data->all->base	= '0';
			$data->all->link	= JRoute::_($url->toString());
		}

		// Set the start and previous data objects
		$data->start	= new JPaginationObject(JText::_('Start'));
		$data->previous	= new JPaginationObject(JText::_('Prev'));

		if ($this->get('pages.current') > 1)
		{
			$page = ($this->get('pages.current') -2) * $this->limit;

			$page = $page == 0 ? '' : $page; //set the empty for removal from route

			$url->delVar("start");
			
			$data->start->base	= '0';
			$data->start->link	= JRoute::_($url->toString());
			$data->previous->base	= $page;
			
			$url->setVar("start", $page);
			
			$data->previous->link	= JRoute::_($url->toString());
		}

		// Set the next and end data objects
		$data->next	= new JPaginationObject(JText::_('Next'));
		$data->end	= new JPaginationObject(JText::_('End'));

		if ($this->get('pages.current') < $this->get('pages.total'))
		{
			$next = $this->get('pages.current') * $this->limit;
			$end  = ($this->get('pages.total') -1) * $this->limit;

			$url->setVar("start", $next);

			$data->next->base	= $next;
			$data->next->link	= JRoute::_($url->toString());
			
			$url->setVar("start", $end);
			
			$data->end->base	= $end;
			$data->end->link	= JRoute::_($url->toString());
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');
		for ($i = $this->get('pages.start'); $i <= $stop; $i ++)
		{
			$offset = ($i -1) * $this->limit;

			$offset = $offset == 0 ? '' : $offset;  //set the empty for removal from route

			$data->pages[$i] = new JPaginationObject($i);
			if ($i != $this->get('pages.current') || $this->_viewall)
			{
				$url->setVar("start", $offset);
				
				$data->pages[$i]->base	= $offset;
				$data->pages[$i]->link	= JRoute::_($url->toString());
			}
		}
		return $data;
	}
}