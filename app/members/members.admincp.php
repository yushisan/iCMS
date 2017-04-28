<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
// class membersApp extends admincp{
class membersAdmincp{
    public $groupAdmincp =null;

    public function __construct() {
        $this->uid      = (int)$_GET['id'];
        $this->groupAdmincp = new groupAdmincp(1);
    }
    /**
     * [工作统计]
     * @return [type] [description]
     */
    public function do_job(){
		$job	= new members_job();
        $this->uid OR $this->uid = members::$userid;
		$job->count_post($this->uid);
        $month  = $job->month();
        $pmonth = $job->month($job->pmonth['start']);
        $rs     = iDB::row("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
		include admincp::view("members.job");
    }
    public function do_add(){
        if($this->uid) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
            $rs->config = json_decode($rs->config,true);
            $rs->info   = json_decode($rs->info,true);
        }
        include admincp::view("members.add");
    }
    /**
     * [个人信息]
     * @return [type] [description]
     */
    public function do_profile(){
        $this->uid = members::$userid;
        if($this->uid) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
            $rs->config = json_decode($rs->config,true);
            $rs->info   = json_decode($rs->info,true);
        }
        include admincp::view("members.add");
    }
    public function do_iCMS(){
    	if($_GET['job']){
    		$job = new members_job();
    	}
    	$sql	= "WHERE 1=1";
    	//isset($this->type)	&& $sql.=" AND `type`='$this->type'";
		$_GET['gid'] && $sql.=" AND `gid`='{$_GET['gid']}'";
        $orderby    = $_GET['orderby']?$_GET['orderby']:"uid DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__members` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个用户");
        $rs         = iDB::all("SELECT * FROM `#iCMS@__members` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count		= count($rs);
    	include admincp::view("members.manage");
    }
    public function do_save(){
        $uid      = (int)$_POST['uid'];
        $gender   = (int)$_POST['gender'];
        $type     = $_POST['type'];
        $username = iSecurity::escapeStr($_POST['uname']);
        $nickname = iSecurity::escapeStr($_POST['nickname']);
        $realname = iSecurity::escapeStr($_POST['realname']);
        $gid      = 0;

        $info   = array_map(array("iSecurity","escapeStr"), (array)$_POST['info']);
        $info   = addslashes(json_encode($info));

        $config = (array)$_POST['config'];
        $config = addslashes(json_encode($config));
        $_POST['pwd'] && $password = md5($_POST['pwd']);

        $username OR iUI::alert('账号不能为空');

        if(members::is_superadmin()){
            $gid = (int)$_POST['gid'];
        }else{
            isset($_POST['gid']) && iUI::alert('您没有权限更改角色');
            $gid = members::$data->gid;
        }

        $fields = array('gid','gender','username','nickname','realname','info');
        $data   = compact ($fields);

        if(members::is_superadmin()){
            $data['config'] = $config;
        }
        if(empty($uid)) {
            iDB::value("SELECT `uid` FROM `#iCMS@__members` where `username` ='$username' LIMIT 1") && iUI::alert('该账号已经存在');
            $_data = compact(array('password','regtime', 'lastip', 'lastlogintime', 'logintimes', 'post', 'type', 'status'));
            $_data['regtime']       = time();
            $_data['lastip']        = iPHP::get_ip();
            $_data['lastlogintime'] = time();
            $_data['status']        = '1';
            $data = array_merge($data, $_data);
            iDB::insert('members',$data);
            $msg="账号添加完成!";
        }else {
            iDB::value("SELECT `uid` FROM `#iCMS@__members` where `username` ='$username' AND `uid` !='$uid' LIMIT 1") && iUI::alert('该账号已经存在');
            iDB::update('members', $data, array('uid'=>$uid));
            $password && iDB::query("UPDATE `#iCMS@__members` SET `password`='$password' WHERE `uid` ='".$uid."'");
            $msg="账号修改完成!";
        }
        iUI::success($msg,'js:1');
    }
    public function do_batch(){
    	$idA	= (array)$_POST['id'];
    	$idA OR iUI::alert("请选择要操作的用户");
    	$ids	= implode(',',(array)$_POST['id']);
    	$batch	= $_POST['batch'];
    	switch($batch){
    		case 'dels':
                iUI::$break = false;
	    		foreach($idA AS $id){
	    			$this->do_del($id,false);
	    		}
                iUI::$break = true;
				iUI::success('用户全部删除完成!','js:1');
    		break;
		}
	}
    public function do_del($uid = null,$dialog=true){
    	$uid===null && $uid=$this->uid;
		$uid OR iUI::alert('请选择要删除的用户');
		$uid=="1" && iUI::alert('不能删除超级管理员');
		iDB::query("DELETE FROM `#iCMS@__members` WHERE `uid` = '$uid'");
		$dialog && iUI::success('用户删除完成','js:parent.$("#tr'.$uid.'").remove();');
    }
}
