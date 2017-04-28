<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class formsFunc{
	public static function forms_create($vars){
        if(isset($vars['formid'])){
            $formid = $vars['formid'];
            $form   = forms::get($formid);
        }else if(isset($vars['form'])){
            is_array($vars['form']) && $form = $vars['form'];
        }

        if(empty($form)||empty($form['status'])){
            return false;
        }
        isset($vars['main']) && former::$template['main'] = $vars['main'];
        isset($vars['label']) && former::$template['label'] = $vars['label'];
        foreach ($vars as $key => $value) {
            if(stripos($key, 'class_') !== false){
                $key = str_replace('class_', '', $key);
                former::$template['class'][$key] = $value;
            }
        }
        former::$config['value']   = array(
            'userid'   => user::$userid,
            'username' => user::$username,
            'nickname' => user::$nickname
        );
        former::$config['gateway'] = 'usercp';
        former::create($form);
        echo former::layout("#former_".$form['id']);
	}
    public static function forms_list($vars){
    }
}
