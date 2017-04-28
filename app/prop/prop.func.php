<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class propFunc{
	public static function prop_array($vars){
		$field	= $vars['field'];
        $sapp    = $vars['sapp'];
        $variable = propApp::value($field,$sapp);

        $offset = $vars['start']?$vars['start']:0;
		$vars['row'] && $variable = array_slice($variable,$offset, $vars['row']);

        foreach ($variable as $key => $value) {
            if($field){
                $value['url'] = propApp::url($vars['url'],$value);
            }else{
                foreach ($value as $k => $v) {
                    $v['url'] = propApp::url($vars['url'],$v);
                    $value[$k] = $v;
                }
            }
            $variable[$key] = $value;
        }
		return $variable;
	}
}
