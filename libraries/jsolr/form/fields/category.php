<?php

defined('JPATH_PLATFORM') or die;
/* error_reporting(E_ALL);
 ini_set("display_errors", 1); */
jimport('jsolr.form.fields.checkboxes');

class JSolrFormFieldCategory extends JSolrFormFieldCheckboxes
{
	protected $type = 'JSolr.Category';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

        $cats = $this->getCategories($this->getParentCategory());

		foreach ($cats as $option)
		{
			$tmp = JHtml::_(
				'select.option', (string) $option['id'],
				JText::alt(trim((string) $option['title']), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $option['id']), 'value', 'text')
			);

			$options[] = $tmp;

			if (count($option['children'])) {
				foreach ($option['children'] as $option) {
					$tmp = JHtml::_(
						'select.option', (string) $option['id'],
						JText::alt(trim(' - ' . (string) $option['title']), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $option['id']), 'value', 'text')
					);

					$options[] = $tmp;
				}
			}
		}

		reset($options);

		return $options;
	}

	function getCategories($parent_id)
	{
		$db = JFactory::getDbo();
	        $db->setQuery(
	        'SELECT id, title' .
	        ' FROM #__categories' .
	        ' WHERE parent_id = ' . $parent_id .
	        " AND published = 1 and extension = 'com_content'" . 
	        ' ORDER BY title ASC'
	    );

	    $result = array();

	    $cats = $db->loadObjectList();

	    foreach ($cats as $cat) {
	    	$result[] = array(
	    		'id' => $cat->title,
	    		'title' => $cat->title,
	    		'children' => $this->getCategories($cat->id)
	    	);
	    }

	    return $result;
	}

	function getValueText()
	{
		$value = $this->value;

		$result = array();

		$cats = $this->getCategories($this->getParentCategory());

		foreach ($cats as $option) {
			if (is_array($value)) {
				if (in_array((string)$option['title'], $value)) {
					$result[] = JText::_($option['title']);
					continue;
				}
			}elseif ($option['title'] == $value) {
				$result[] = JText::_($option['title']);
				continue;
			}

			$parent = $option;

			foreach ($option['children'] as $option) {
				if (is_array($value)) {
					if (in_array((string)$option['title'], $value)) {
						$result[] = JText::_($parent['title'] . ' => ' . $option['title']);
						continue;
					}
				}elseif ($option['title'] == $value) {
					$result[] = JText::_($parent['title'] . ' => ' . $option['title']);
					continue;
				}
			}
		}

		return implode(', ', $result);
	}

    function getParentCategory()
    {
        return JArrayHelper::getValue($this->element, 'parentCategory', 0);
    }
}