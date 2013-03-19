<?php

class JSolrHtML extends JHTML {
    public static function datepicker($value, $name, $id, $format = '')
    {
        return '<div class="input-append date" id="' . $id .'" data-date="'.$value.'" data-date-format="'.$format.'">
    <input class="span2 datepicker" size="16" type="text" value="'.$value.'">
    </div>';
    }
}