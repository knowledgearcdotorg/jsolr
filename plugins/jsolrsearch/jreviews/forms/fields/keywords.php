<?php

defined('JPATH_PLATFORM') or die;

jimport('jsolr.form.fields.list');

class JReviewsFormFieldKeywords extends JSolrFormFieldList
{
	protected $type = 'JReviews.Keywords';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

        $cats = $this-> getLevelsList();

		foreach ($cats as $option)
		{
			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option->value,
				JText::alt(trim((string) $option->text), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->value), 'value', 'text')
			);

			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}

	function getLevelsList()
	{
		$q = 'SELECT `text`, `value`, optionid' .
	        ' FROM #__jreviews_fieldoptions' .
	        ' WHERE fieldid = 16' .
	        ' ORDER BY ordering ASC LIMIT 100';

        $db = JFactory::getDbo();
	    $db->setQuery($q);

	    return $db->loadObjectList();
	}

	function getValueText()
	{
		$value = $this->value;

		$result = array();

		$cats = $this-> getLevelsList();

		foreach ($cats as $option) {
			if (is_array($value)) {
				if (in_array((string)$option->value, $value)) {
					$result[] = JText::_($option->value);
				}
			}elseif ($option->value == $value) {
				$result[] = JText::_($option->value);
			}
		}

		return implode(', ', $result);
	}
}