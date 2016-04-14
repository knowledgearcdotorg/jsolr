<?php
/**
 * A plugin for searching archived items.
 *
 * @package     JSolr.Plugin
 * @subpackage  Search
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use \JSolr\Search\Search;

class PlgJSolrSearchArchive extends Search
{
    protected $context = 'archive';

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->set('highlighting', array("title", "body_*", "author"));

        $this->set('operators', array('author_fc'=>'author', 'type_fc'=>'type'));
    }

    /**
     * Add custom filters to the main query.
     */
    public function onJSolrSearchFQAdd()
    {
        $array = array('-context:'.$this->get('context').'.asset');

        return $array;
    }

    public function onJSolrSearchURIGet($document)
    {
        if ($this->get('context').'.item' == $document->context) {
            if (JComponentHelper::isInstalled("com_jcar")) {
                require_once(JPATH_ROOT."/components/com_jcar/helpers/route.php");

                return JCarHelperRoute::getItemRoute($document->id);
            } else if (JComponentHelper::isInstalled("com_jspace")) {
                // @TODO for backwards compatibility. Will be removed.
                require_once(JPATH_ROOT."/components/com_jspace/helpers/route.php");

                return JSpaceHelperRoute::getItemFullRoute($document->id);
            }
        }

        return null;
    }

    public function onJSolrSearchRegisterPlugin()
    {
        return array(
            'name'=>$this->_name,
            'label'=>'PLG_JSOLRSEARCH_'.JString::strtoupper($this->_name).'_LABEL',
            'context'=>$this->get('context').'.*'
        );
    }
}
