<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class JSolrCrawlerComponent extends S2Component {

    var $plugin_order = 100;

    var $name = 'jsolrcrawler';

    var $published = true;

    function plgAfterSave(&$model)
    {
		if (is_a($model, 'EverywhereComContentModel')) {
			if ($model->name == 'Listing') {
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('jsolrcrawler');
				
				$results = $dispatcher->trigger('onJSolrIndexAfterSave', array('com_jreviews.listing', $model));
			}
		}
    }
}