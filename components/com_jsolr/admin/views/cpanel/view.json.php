<?php
/**
 * A view for configuring the JSolr component's settings.
 *
 * @package     JSolr.Search
 * @subpackage  View
 * @copyright   Copyright (C) 2012-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class JSolrViewCPanel extends JViewLegacy
{
    protected $item;

    function display($tpl = null)
    {
        $this->item = $this->get("Item");

        $array = $this->item->toArray();

        if (($status = $this->item->get('status')) == "OK") {
            $array['status'] = JText::_("COM_JSOLR_CPANEL_CONNECTED");
        } else {
            $array['status'] = JText::_("COM_JSOLR_CPANEL_".strtoupper($status));
        }

        if ($this->item->get('statistics.index.lastModified')) {
            $lastModified =
                JHtml::_(
                    'date',
                    $this->item->get('statistics.index.lastModified'),
                    JText::_('DATE_FORMAT_LC2'));
        } else {
            $lastModified = JText::_('COM_JSOLR_CPANEL_NOVALUE');
        }

        $array['statistics']['index']['lastModifiedFormatted'] = $lastModified;

        echo json_encode($array);
    }
}
