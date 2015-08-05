<?php

/**

 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.

 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 */



namespace JSolr\Form\Fields;



use \JFactory as JFactory;



\JLoader::import('joomla.form.formfield');



class QueryFilter extends HiddenFilter

{

	/**

	 * The form field type.

	 *

	 * @var         string

	 */

	protected $type = 'JSolr.QueryFilter';



	/**

	 * (non-PHPdoc)

	 * @see JSolrFilterable::getFilters()

	 */

	public function getFilters()

	{

		$application = JFactory::getApplication();



		$filters = array();



		if ($this->filter && $application->input->getString('q', null)) {

			$filters[] = $this->filter.":".$application->input->getString('q', null);

		}



		return (count($filters)) ? $filters : array();

	}



	public function __get($name)

	{

		switch ($name) {

			case 'filter':

				$application = JFactory::getApplication();

				return $application->input->getString($this->name, null);

				break;



			default:

				return parent::__get($name);

				break;

		}

	}

}