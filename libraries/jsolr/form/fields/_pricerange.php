<?php

defined('_JEXEC') or die('Restricted access');

jimport('solr.form.fields.facet');
JFormHelper::loadFieldClass('daterange');

class JFormFieldPriceRange extends JFormFieldDateRange 
{
	protected $cssClass = 'pricerange';
	protected $selectedName = 'pdr';
    protected $rangeMinName = 'pmin';
    protected $rangeMaxName = 'pmax';

	protected function getDefaultOptions()
    {
    	$options = array();

    	for ($i = $this->getRangeStart(); $i < $this->getRangeEnd(); $i += $this->getRangeSkip()) {
    		$options[] = $i . '-'. ($i + $this->getRangeSkip());
    	}

    	return $options;
    }

    protected function getRangeStart()
    {
    	return isset($this->element['rangeStart']) ? (int)$this->element['rangeStart'] : 0;
    }

    protected function getRangeEnd()
    {
    	return isset($this->element['rangeEnd']) ? (int)$this->element['rangeEnd'] : 100;
    }

    protected function getRangeSkip()
    {
    	return isset($this->element['rangeSkip']) ? (int)$this->element['rangeSkip'] : 10;
    }

    protected function getOptionText($label)
    {
        return '$' . ($this->getRangeStart() + ($label * $this->getRangeSkip())) . ' - $' . ($this->getRangeStart() + (($label + 1) * $this->getRangeSkip()));
    }
}