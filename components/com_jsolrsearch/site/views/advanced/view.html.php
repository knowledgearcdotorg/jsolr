<?php
/**
 * @package		JSolr
 * @subpackage	Search
 * @copyright	Copyright (C) 2012 Wijiti Pty Ltd. All rights reserved.
 * @license     This file is part of the JSolrSearch Component for Joomla!.

   The JSolrSearch Component for Joomla! is free software: you can redistribute it 
   and/or modify it under the terms of the GNU General Public License as 
   published by the Free Software Foundation, either version 3 of the License, 
   or (at your option) any later version.

   The JSolrSearch Component for Joomla! is distributed in the hope that it will be 
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with the JSolrSearch Component for Joomla!.  If not, see 
   <http://www.gnu.org/licenses/>.

 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * Name							Email
 * Hayden Young					<haydenyoung@wijiti.com>
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
class JSolrSearchViewAdvanced extends JViewLegacy
{
	protected $form;
	
    public function display($tpl = null)
    {
		JHtml::_('behavior.framework', true);
    	
		$document = JFactory::getDocument();

    	$document->addStyleSheet(JURI::base()."media/com_jsolrsearch/css/jsolrsearch.css");
    	$document->addScript(JURI::base()."media/com_jsolrsearch/js/jsolrsearch.js");
		
		$this->form	= $this->get('Form');
		$this->state = $this->get('State');
		
		parent::display($tpl);
    }
    
    public function ParseQueryToFields() {

        $eq = array();
	preg_match("/(?<=\").*?(?=\")/", JRequest::getString("q", "", "default", 2), $eq);
        

        $nq = "";
        $matches = array();
        preg_match_all("/(?<=-)(.*?)(?=\s|$)/", JRequest::getString("q"), $matches);

        foreach (JArrayHelper::getValue($matches, 0) as $item) {
                $nq .= " $item";
        }
        
        if ( strpos(JRequest::getString("q"), ' OR ') !== false ) {
            $oq = explode(' OR ', JRequest::getString("q")) ;

            if ( isset($oq[0]) ) {
                $oq0 = explode(' ', $oq[0]) ;
                $oq0 = array_reverse($oq0) ;
            }

            if ( isset($oq[1]) ) {
                $oq1 = explode(' ', $oq[1]) ;
            }

            if ( isset($oq[0]) && isset($oq1[0]) ) {
                $oq = $oq0[0].' '.$oq1[0] ;
            }
        } else {
            $oq = '' ;
        }
        
        $aq = preg_replace('/(".*?")/', '', JRequest::getString("q"), 1);
        $aq = preg_replace('/(-.*?)(?=\s|$)/', '', $aq);
        $aq = trim(preg_replace('/"/', "", $aq));
        $aq = str_replace(array(' OR','"'),'',$aq) ;
        
                
        if ( !empty($eq) ) {
            $this->form->setValue('eq', null, $eq[0]);
        }
        if ( !empty($nq) ) {
            $this->form->setValue('nq', null, trim($nq));
        }
        if ( !empty($oq) ) {
            $this->form->setValue('oq', null, $oq);
        }
        if ( !empty($aq) ) {
            $this->form->setValue('aq', null, $aq);
        }
    }
}