<?php
/**
 * Supports a date picker.
 * 
 * @author      $LastChangedBy: bartlomiejkielbasa $
 * @package     JSolr
 * 
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Michał Kocztorz <michalkocztorz@wijiti.com> 
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com> 
 * 
 */

defined('JPATH_BASE') or die;

jimport('jsolr.form.fields.price');
jimport('jsolr.helper.jsolr');

class JSolrFormFieldNumberPrice extends JSolrFormFieldNumberRange
{
    protected $type = 'JSolr.Price';

    function getDefaultOptions()
    {
        $step   = $this->getStep();
        $start  = $this->getStart();
        $end    = $this->getEnd();
        $options = array('' => JText::_(COM_JSOLRSEARCH_NUMBERRANGE_ALL));

        while($start < $end) {
            if ($start + $step <= $end) {
                $options[$start . '_' . ($start + $step)] = 'From $' . $start . ' to $' . ($start + $step); 
            } else {
                $options[$start . '_' . $end] = 'From $' . $start . ' to $' . $end; 
            }

            $start += $step;
        }

        return $options;
    }
}