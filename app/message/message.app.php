<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class messageApp {
	public $methods = array('iCMS','pm');

    public function ACTION_pm() {
        user::get_cookie() OR iUI::code(0, 'iCMS:!login', 0, 'json');

        $receiv_uid  = (int) $_POST['uid'];
        $receiv_name = iSecurity::escapeStr($_POST['name']);
        $content     = iSecurity::escapeStr($_POST['content']);

        $receiv_uid OR iUI::code(0, 'iCMS:error', 0, 'json');
        $content OR iUI::code(0, 'iCMS:pm:empty', 0, 'json');

        $send_uid  = user::$userid;
        $send_name = user::$nickname;

        $setting = (array)user::value($receiv_uid,'setting');
        if($setting['inbox']['receive']=='follow'){
            if($mid){
                $mid = iSecurity::escapeStr($_POST['mid']);
                $mid = authcode($mid);
                // $row = iDB::row("SELECT `send_uid`,`receiv_uid` FROM `#iCMS@__message` where `id`='$mid'");
                $muserid = iDB::value("SELECT `userid` FROM `#iCMS@__message` where `id`='$mid'");
            }
            if($muserid!=user::$userid){
                $check = user::follow($receiv_uid, $send_uid);
                $check OR iUI::code(0, 'iCMS:pm:nofollow', 0, 'json');
            }
        }

        $fields = array('send_uid', 'send_name', 'receiv_uid', 'receiv_name', 'content');
        $data = compact($fields);
        message::send($data, 1);
        iUI::code(1, 'iCMS:pm:success', $id, 'json');
    }
}
