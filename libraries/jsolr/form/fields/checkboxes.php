<?php

defined('JPATH_PLATFORM') or die;

jimport('jsolr.form.fields.abstract');

class JSolrFormFieldCheckboxes extends JSolrFormFieldAbstract
{
	protected $type = 'JSolr.Checkboxes';

	protected $forceMultiple = true;

	protected function getInputSearchTool()
	{
		// Initialize variables.
		$html = array();

		// Initialize some field attributes.
		$class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';

		// Start the checkbox field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

		// Get the field options.
		$options = $this->getOptions();

		// Build the checkbox field output.
		$html[] = '<ul>';
		foreach ($options as $i => $option)
		{

			// Initialize some option attributes.
			$checked = (in_array((string) $option->value, (array) $this->value) ? ' checked="checked"' : '');
			$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
			$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';

			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

			$html[] = '<li>';
			$html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '"' . ' value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';

			$html[] = '<label for="' . $this->id . $i . '"' . $class . '>' . JText::_($option->text) . '</label>';
			$html[] = '</li>';
		}
		$html[] = '</ul>';

		// End the checkbox field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

	protected function getInputFacetFilter()
	{
		// Initialize variables.
		$html = array();

		// Initialize some field attributes.
		$class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';

		// Start the checkbox field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

		// Get the field options.
		$options = $this->getOptions();

		// Build the checkbox field output.
		$html[] = '<ul>';
		foreach ($options as $i => $option)
		{

			// Initialize some option attributes.
			$checked = (in_array((string) $option->value, (array) $this->value) ? ' checked="checked"' : '');
			$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
			$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';

			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

			$html[] = '<li>';
			$html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '"' . ' value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';

			$html[] = '<label for="' . $this->id . $i . '"' . $class . '>' . JText::_($option->text) . '</label>';
			$html[] = '</li>';
		}
		$html[] = '</ul>';

		// End the checkbox field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		foreach ($this->element->children() as $option)
		{

			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option['value'], trim((string) $option), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}

	function getFilter()
	{
		$facet = $this->element['facet'];
		$value = $this->value;

		if (!empty($value)) {
			if (count($value)) {
				return $facet . ':' . implode(' OR ', $value);
			}
		}

		return '';
	}

	function getValueText()
    {
        if (!is_array($this->value) || count($this->value) == 0) {
            return NULL;
        }

        $result = array();

        foreach ($this->value as $v) {
            $result[] = $v;
        }

        return implode(', ', $result);
    }
}
