<?php

jimport('jsolr.form.fields.numberrange');

class JSolrFormFieldPriceRange extends JSolrFormFieldNumberRange
{
	protected $type = 'JSolr.PriceRange';

	function getValue()
	{
		$result = array();

		if (is_array($this->value)) {
			$from 	= JArrayHelper::getValue($this->value, 'from');
			$to 	= JArrayHelper::getValue($this->value, 'to');
			$value 	= JArrayHelper::getValue($this->value, 'value');

			if (is_numeric($from) || is_numeric($to)) {
				if (!is_numeric($from)) {
					$from = '*';
				} elseif (!is_numeric($to)) {
					$to = '*';
				}

				$result[] = sprintf(JText::_("COM_JSOLRSEARCH_PRICERANGE_FROM_TO"), $from, $to);
			} elseif (!empty($value)) {
				$filters = array();

				foreach (explode('|', $value) as $val) {
					$val = explode('_', $val);

					$result[] = sprintf(JText::_("COM_JSOLRSEARCH_PRICERANGE_FROM_TO"), $val[0], $val[1]);
				}
			}
		}

		return implode(', ', $result);
	}

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