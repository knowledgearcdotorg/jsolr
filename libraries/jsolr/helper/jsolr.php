<?php

class JSolrHelper {
	public static function getSolrDate($date)
	{
		return date('Y-m-d\TH\\\\:i\\\\:s\Z', strtotime($date));
	}
}