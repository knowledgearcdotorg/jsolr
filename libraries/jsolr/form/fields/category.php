<?php

defined('JPATH_PLATFORM') or die;

class JSolrFormFieldCategry extends JSolrFormFieldList
{

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

        $db = JFactory::getDbo();
	        $db->setQuery(
	        'SELECT lang_code, title' .
	        ' FROM #__categories' .
	        ' ORDER BY sef ASC'
	        );
        $cats = $db->loadObjectList();

		foreach ($cats as $option)
		{
			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option->id,
				JText::alt(trim((string) $option->title), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text')
			);

			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}