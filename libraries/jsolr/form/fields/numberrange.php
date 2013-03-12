<?php
/**
 * Supports a date picker.
 * 
 * @author		$LastChangedBy: bartlomiejkielbasa $
 * @package		JSolr
 * 
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Michał Kocztorz <michalkocztorz@wijiti.com> 
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com> 
 * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.daterange');
jimport('jsolr.helper.jsolr');

class JSolrFormFieldNumberRange extends JSolrFormFieldDateRange
{
	protected $type = 'JSolr.NumberRange';

	/**
	 * @inheritdoc
	 */
	public function getInputFacetFilter()
	{
		$id 	= $this->element['name'];
		$html 	= '';
		$name 	= (string)$this->element['name'];

		$from 	= $this->value['from'];
		$to 	= $this->value['to'];
		$value 	= $this->value['value'];

		$html .= '<input type="hidden" id="' .$id. '_value" name="' . $this->name .'[value]" value="' . $value .'" />';

		$html .= '<ul data-type="jnumberrange">';

		foreach ($this->getFinalOptions() as $value => $label) {
			$html .= '<li>' . JHTML::_('link', '#', $label, array('class' => 'jrange-option jnumberrange-option', 'data-value' => $value, 'data-name' => $id, 'id' => 'numberrange_option_' . $id)) . '</li>';
		}

		if ($this->useCustomRange()) {
			$html .= '<li class="jnumberrange-custom jrange-custom">' . JHTML::_('link', '#', JText::_(COM_JSOLRSEARCH_NUMBERRANGE_CUSTOM));
			$name = $this->name;
			
			$html .= '<span>';

			$html .= '<label>' . JText::_(COM_JSOLRSEARCH_FROM) .'<input type="text" name="' . $name .'[from]" value="' . $from .'" id="' . $id . '_from" /></label>';
			$html .= '<label>' . JText::_(COM_JSOLRSEARCH_TO) .'<input type="text" name="' . $name .'[to]" value="' . $to .'" id="' . $id . '_to" /></label>';

			$html .= '</span>';
		
			$html .= '</li>';
		}

		$html .= '</ul>';
		
		return $html;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getInputSearchTool()
	{
		$id 	= $this->element['name'];
		$html 	= '';
		$name 	= (string)$this->element['name'];

		$from 	= $this->value['from'];
		$to 	= $this->value['to'];
		$value 	= $this->value['value'];

		$html .= '<input type="hidden" id="' .$id. '_value" name="' . $this->name .'[value]" value="' . $value .'" />';

		$html .= '<ul data-type="jnumberrange">';

		foreach ($this->getFinalOptions() as $value => $label) {
			$html .= '<li>' . JHTML::_('link', '#', $label, array('class' => 'jrange-option jnumberrange-option', 'data-value' => $value, 'data-name' => $id, 'id' => 'numberrange_option_' . $id)) . '</li>';
		}

		if ($this->useCustomRange()) {
			$html .= '<li class="jnumberrange-custom jrange-custom">' . JHTML::_('link', '#', COM_JSOLRSEARCH_NUMBERRANGE_CUSTOM);
			$name = $this->name;
			
			$html .= '<span>';

			$html .= '<label>' . COM_JSOLRSEARCH_FROM .'<input type="text" name="' . $name .'[from]" value="' . $from .'" /></label>';
			$html .= '<label>' . COM_JSOLRSEARCH_TO .'<input type="text" name="' . $name .'[to]" value="' . $to .'" /></label>';

			$html .= '</span>';
		
			$html .= '</li>';
		}

		$html .= '</ul>';
		
		return $html;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getFilter()
	{
		$facet = (string)$this->element['facet'];

		$filter = '';

		if (is_array($this->value)) {
			$from 	= $this->value['from'];
			$to 	= $this->value['to'];
			$value  = $this->value['value'];

			if (!empty($from) || !empty($to)) {
				if (empty($from)) {
					$from = '*';
				} elseif (empty($to)) {
					$to = '*';
				}

				$filter = $facet . ':[' . $from . ' TO ' . $to . ']';
			} elseif (!empty($value)) {
				$value = explode('_', $value);

				$filter = $facet . ':[' . (int)$value[0] . ' TO ' . (int)$value[1] . ']';
			}
		}

		return $filter;
	}

	function getDefaultOptions()
	{
		$step 	= $this->getStep();
		$start 	= $this->getStart();
		$end 	= $this->getEnd();
		$options = array();

		while($start < $end) {
			if ($start + $step <= $end) {
				$options[$start . '_' . ($start + $step)] = 'From ' . $start . ' to ' . ($start + $step); 
			} else {
				$options[$start . '_' . $end] = 'From ' . $start . ' to ' . $end; 
			}

			$start += $step;
		}

		return $options;
	}

	function getStep()
	{
		return isset($this->element['step']) ? (int)$this->element['step'] : 10;
	}

	function getStart()
	{
		return isset($this->element['start']) ? (int)$this->element['start'] : 0;
	}

	function getEnd()
	{
		return isset($this->element['end']) ? (int)$this->element['end'] : 100;
	}
}