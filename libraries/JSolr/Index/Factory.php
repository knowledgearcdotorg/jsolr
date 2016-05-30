<?php
/**
 * @copyright   Copyright (C) 2013-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace JSolr\Index;

use \JComponentHelper as JComponentHelper;
use \JString as JString;

class Factory extends \JSolr\Factory
{
    /**
     * Gets a file extractor for the file or url provided.
     *
     * @param string $fileOrUrl A file path or url.
     *
     * @return FileSystem\Extractor A sub class of the
     * FileSystem\Extractor, based on the JSolr Index component's
     * configuration.
     */
    public static function getExtractor($fileOrUrl)
    {
        $params = JComponentHelper::getParams(self::$component, true);

        $params->loadArray(array('component'=>$params->toArray()));

        $type = JString::ucfirst($params->get('component.extractor'));

        $class = "\JSolr\Index\FileSystem\\$type";

        return new $class($fileOrUrl);
    }
}
