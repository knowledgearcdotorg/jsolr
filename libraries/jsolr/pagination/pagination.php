<?php
class JSolrPagination extends JPagination
{
	/**
	 * Create and return the pagination data object, ensuring that links are  
	 * encoded correctly.
	 *
	 * @return  object  Pagination data object.
	 *
	 * @since   1.5
	 */
	protected function _buildDataObject()
	{
		$data = new stdClass;
	
		// Build the additional URL parameters string.
		$params = JSolrSearchFactory::getURI();

		if (!empty($this->additionalUrlParams))
		{
			foreach ($this->additionalUrlParams as $key => $value)
			{
				$params .= '&' . $key . '=' . $value;
			}
		}
	
		$data->all = new JPaginationObject(JText::_('JLIB_HTML_VIEW_ALL'), $this->prefix);
	
		if (!$this->viewall)
		{
			$data->all->base = '0';
			$data->all->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=');
		}
	
		// Set the start and previous data objects.
		$data->start = new JPaginationObject(JText::_('JLIB_HTML_START'), $this->prefix);
		$data->previous = new JPaginationObject(JText::_('JPREV'), $this->prefix);
	
		if ($this->pagesCurrent > 1)
		{
			$page = ($this->pagesCurrent - 2) * $this->limit;
	
			// Set the empty for removal from route
			// @todo remove code: $page = $page == 0 ? '' : $page;
	
			$data->start->base = '0';
			$data->start->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=0');
			$data->previous->base = $page;
			$data->previous->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $page);
		}
	
		// Set the next and end data objects.
		$data->next = new JPaginationObject(JText::_('JNEXT'), $this->prefix);
		$data->end = new JPaginationObject(JText::_('JLIB_HTML_END'), $this->prefix);
	
		if ($this->pagesCurrent < $this->pagesTotal)
		{
			$next = $this->pagesCurrent * $this->limit;
			$end = ($this->pagesTotal - 1) * $this->limit;
	
			$data->next->base = $next;
			$data->next->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $next);
			$data->end->base = $end;
			$data->end->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $end);
		}
	
		$data->pages = array();
		$stop = $this->pagesStop;
	
		for ($i = $this->pagesStart; $i <= $stop; $i++)
		{
		$offset = ($i - 1) * $this->limit;
	
		$data->pages[$i] = new JPaginationObject($i, $this->prefix);
	
		if ($i != $this->pagesCurrent || $this->viewall)
		{
		$data->pages[$i]->base = $offset;
		$data->pages[$i]->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $offset);
		}
				else
		{
		$data->pages[$i]->active = true;
		}
		}
	
		return $data;
	}
}