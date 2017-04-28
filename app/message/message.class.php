<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class message{
    public static $type_map = array(
        '0'=>'系统信息',
        '1'=>'私信',
        '2'=>'提醒',
        '3'=>'留言',
    );
	//type: 0 系统 1 用户对话 2 @ 3留言
	public static function send($a = array(
			"send_uid"    => 0,"send_name"   => NULL,
			"receiv_uid"  => 0,"receiv_name" => NULL,
			"content"     => NULL
		),$type=1){

		// $userid = (int)$a['userid'];
		// $friend = (int)$a['friend'];

		$send_uid    = (int)$a['send_uid'];
		$send_name   = iSecurity::escapeStr($a['send_name']);
		$receiv_uid  = (int)$a['receiv_uid'];
		$receiv_name = iSecurity::escapeStr($a['receiv_name']);

		$content  = iSecurity::escapeStr($a['content']);
		$sendtime = time();
		if($send_uid && $send_uid==$receiv_uid && !$a['self']){
			return;
		}
        $fields = array('userid', 'friend', 'send_uid', 'send_name', 'receiv_uid', 'receiv_name', 'content', 'type', 'sendtime', 'readtime', 'status');
        $data   = compact ($fields);
		$data['userid']   = $send_uid;
		$data['friend']   = $receiv_uid;
		$data['readtime'] = "0";
		$data['status']   = "1";
		iDB::insert('message',$data);
		if($type=="1"){
			$data['userid']   = $receiv_uid;
			$data['friend']   = $send_uid;
			iDB::insert('message',$data);
		}
	}
	//2 @/评论
	public static function at($a){
		self::send($a,2);
	}
	//0 系统
	public static function sys($a){
		$type = 1;
		if(empty($a['receiv_uid'])){
			$a['receiv_uid']  = "0";
			$a['receiv_name'] = "@所有人";
			$type = 0;
		}
		$a['send_uid']    = "10000";
		$a['send_name']   = "系统信息";
		self::send($a,$type);
	}
}
