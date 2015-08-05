<?php

/**

 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.

 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 */



namespace JSolr\Form\Fields;



use \JFactory as JFactory;

use \JArrayHelper as JArrayHelper;

use \JString as JString;

use \JLanguage as JLanguage;

use \JText as JText;



\JLoader::import('joomla.form.formfield');

\JLoader::import('joomla.form.helper');



class LanguageFilter extends HiddenFilter

{

	/**

	 * The form field type.

	 *

	 * @var         string

	 */

	protected $type = 'JSolr.LanguageFilter';



	/**

	 * (non-PHPdoc)

	 * @see JSolrFilterable::getFilters()

	 */

	public function getFilters()

	{

		$application = JFactory::getApplication();



		$filters = array();



		if (!$application->input->getString('lr', null)) {

			// Get language from current tag or use default joomla langugage.

			if (!($lang = JFactory::getLanguage()->getTag())) {

				$lang = JFactory::getLanguage()->getDefault();

			}



			if ($lang) {

				$filters[] = "(".$this->filter.":$lang OR ".$this->filter.":\*)";

			}

		} else {

			$filters[] = '('.$this->filter.':'.$application->input->getString('lr', null).

						 ' OR '.

						 $this->filter_alt.':'.$application->input->getString('lr', null).')';

		}



		return (count($filters)) ? $filters : array();

	}



	public function __get($name)

	{

		switch ($name) {

			case 'label':

				$application = JFactory::getApplication();

				$language = JLanguage::getInstance($application->input->getString('lr', null));



				return JText::sprintf('COM_JSOLRSEARCH_FILTER_'.JString::strtoupper($this->name), $language->getName());



			case 'filter':

				return 'lang';

				break;



			case 'filter_alt':

				return 'lang_alt';

				break;



			default:

				return parent::__get($name);

		}

	}

}