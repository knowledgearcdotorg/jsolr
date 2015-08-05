<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JSolr\Form\Fields;

use \JArrayHelper as JArrayHelper;

class Toggle extends Facets
{
	public $type = 'JSolr.Toggle';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		$facets = $this->getFacets();

		foreach ($facets as $key=>$value) {
			$class = '';

			if ($this->isSelected($key)) {
				$class = ' class="active"';
			}

			$count = '';

			if ((bool)$this->getAttribute('count')) {
				$count = '<span>('.$value.')</span>';
			}

			$options[] = '<li'.$class.'><a href="'.$this->getFilterURI($key).'">'.JArrayHelper::getValue($this->element, 'value').'</a>'.$count.'</li>';
		}

		reset($options);

		return $options;
	}
}