<?php

defined('JPATH_PLATFORM') or die;
/* error_reporting(E_ALL);
 ini_set("display_errors", 1); */
jimport('jsolr.form.fields.list');

class JSolrFormFieldCategory extends JSolrFormFieldList
{
	protected $type = 'JSolr.Category';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

        $cats = $this->getCategoriesTree();

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

	function getCategoriesTree()
	{
		$db = JFactory::getDbo();
	        $db->setQuery(
	        'SELECT id, title' .
	        ' FROM #__categories' .
	        ' WHERE parent_id = 0' .
	        ' ORDER BY title ASC'
	    );

	    $rootCategory = $db->loadObject();

	    return $this->getCategories($rootCategory->id);
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

		$cats = $this-> getCategoriesTree();

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
}