<?php
/**
 * A model that provides configuration options for JSolrIndex.
 *
 * @package     JSolr.Index
 * @subpackage  Model
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

JLoader::import('joomla.registry.registry');
JLoader::import('joomla.filesystem.file');
JLoader::import('joomla.application.component.helper');

use \JSolr\Index\Factory;

class JSolrIndexModelConfiguration extends JModelItem
{
    public function __construct()
    {
        parent::__construct();
    }
}