<?php
/**
 * @package		JSolr
 * @subpackage	Helper
 * @copyright	Copyright (C) 2013 KnowledgeARC Ltd. All rights reserved.
 * @license     This file is part of the JSolr library for Joomla!.
 *
 *   The JSolr library for Joomla! is free software: you can redistribute it 
 *   and/or modify it under the terms of the GNU General Public License as 
 *   published by the Free Software Foundation, either version 3 of the License, 
 *   or (at your option) any later version.
 *
 *   The JSolr library for Joomla! is distributed in the hope that it will be 
 *   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with the JSolrIndex component for Joomla!.  If not, see 
 *   <http://www.gnu.org/licenses/>.
 *
 * Contributors
 * Please feel free to add your name and email (optional) here if you have 
 * contributed any source code changes.
 * @author Michał Kocztorz <michalkocztorz@wijiti.com> 
 * @author Bartłomiej Kiełbasa <bartlomiejkielbasa@wijiti.com> 
 * @author Hayden Young <haydenyoung@knowledgearc.com>
 * 
 */

class JSolrHtML extends JHTML {
    public static function datepicker($value, $name, $id, $format = '')
    {
        return '<div class="input-append date" id="' . $id .'" data-date="'.$value.'" data-date-format="'.$format.'">
    <input class="span2 datepicker" size="16" type="text" value="'.$value.'">
    </div>';
    }
}