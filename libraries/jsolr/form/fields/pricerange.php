<?php

jimport('jsolr.form.fields.numberrange');

class JSolrFormFieldPriceRange extends JSolrFormFieldNumberRange
{
	protected $type = 'JSolr.PriceRange';

	function getDefaultOptions()
	{
		$step 	= $this->getStep();
		$start 	= $this->getStart();
		$end 	= $this->getEnd();

		$options = array('' => JText::_("COM_JSOLRSEARCH_NUMBERRANGE_ALL"));

		while($start < $end) {
			if ($start + $step <= $end) {
				$options[$start . '_' . ($start + $step)] = sprintf(JText::_("COM_JSOLRSEARCH_PRICERANGE_FROM_TO"), $start, $start + $step); 
			} else {
				$options[$start . '_' . $end] = sprintf(JText::_("COM_JSOLRSEARCH_PRICERANGE_FROM_TO"), $start, $end);
			}

			$start += $step;
		}

		return $options;
	}
}