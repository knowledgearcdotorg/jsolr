<?php

defined('JPATH_PLATFORM') or die;

jimport('jsolr.form.fields.selectabstract');

class JReviewFormFieldReadingLevel extends JSolrFormFieldList
{
	protected $type = 'JReview.ReadingLevel';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

        $db = JFactory::getDbo();
	        $db->setQuery(
	        'SELECT `text`, `value`, id' .
	        ' FROM #__jreviews_fieldoptions' .
	        ' ORDER BY ordering ASC'
	        );
        $cats = $db->loadObjectList();

		foreach ($cats as $option)
		{
			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option->id,
				JText::alt(trim((string) $option->text), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->value), 'value', 'text')
			);

			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}