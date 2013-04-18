<?php
/**
 * Class to select lang
 *
 * @author      $LastChangedBy: bartlomiejkielbasa $
 * @package     JSolr
 *
 * @author Bartlomiej Kielbasa <bartlomiejkielbasa@wijiti.com> *
 */

jimport('jsolr.form.fields.abstract');

class JSolrFormFieldLang extends JSolrFormFieldSelectAbstract {

    /**
     * Method to get default list of languages
     * @return array
     */
    protected function getDefaultOptions()
    {
        $result = array();

        $db = JFactory::getDbo();
        $db->setQuery(
        'SELECT lang_code, title' .
        ' FROM #__languages' .
        ' ORDER BY sef ASC'
        );
        $options = $db->loadObjectList();

        foreach ($options as $lang) {
            $result[$lang->lang_code] = $lang->title;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getFilter()
    {
        $facet = (string)$this->element['facet'];

        $filter = '';

        if (!empty($this->value)) {
            if (is_string($this->value)) {
                $filter = $facet . ':' . $this->escape($this->value);
            } elseif (is_array($this->value)) {
                $filter = $facet . ':' . implode(' OR ', $this->escape($this->value));
            }
        }        

        return $filter;
    }

    function getValueText()
    {
        if (!is_array($this->value) || count($this->value) == 0) {
            return JText::_("COM_JSOLRSEARCH_LANGUAGE_ALL");
        }

        $result = array();
        $options = $this->getFinalOptions();

        foreach ($this->value as $v) {
            $result[] = JArrayHelper::getValue($options,$v);
        }

        return implode(', ', $result);
    }
}