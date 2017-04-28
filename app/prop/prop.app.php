<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class propApp {
	public $methods = array('iCMS');
	public static function value($field,$app=null) {
        $app && $pieces[] = $app;
        $pieces[] = $field;
        $keys = implode('/', $pieces);
		$propArray 	= iCache::get("prop/{$keys}");
		$propArray && sort($propArray);
        return $propArray;
	}
    public static function url($url,$value=null) {
        $url = $url?$url:$_SERVER['REQUEST_URI'];
        $query = array();
        $query[$value['field']] = $value['val'];
        return buildurl($url,$query);
    }
}
