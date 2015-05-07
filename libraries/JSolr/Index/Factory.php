<?php
/**
 * @copyright   Copyright (C) 2013-2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JSolr\Index;

use \JSolr\Index\Filesystem;

class Factory extends \JSolr\Factory
{
	protected static $component = 'com_jsolrindex';

	/**
	 * Gets a file extractor for the file or url provided.
	 *
	 * @param string $fileOrUrl A file path or url.
	 *
	 * @return JSolrIndexFilesystemExtractor A sub class of the
	 * JSolrIndexFilesystemExtractor, based on the JSolr Index component's
	 * configuration.
	 */
	public static function getExtractor($fileOrUrl)
	{
		$params = JComponentHelper::getParams('com_jsolrindex', true);

		$params->loadArray(array('component'=>$params->toArray()));

		$type = JString::ucfirst($params->get('component.extractor'));

		$class = $type;

		return new $class($fileOrUrl);
	}
}