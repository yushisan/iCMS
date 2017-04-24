<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
*/
defined('iPHP') OR exit('What are you doing?');

class patchAdmincp{

	public function __construct() {
		$this->msg   = "";
		if(isset($_GET['git'])){
			patch::$release = $_GET['release'];
			patch::$zipName = $_GET['zipname'];
			// patch::$test = true;
		}else{
			$this->patch = patch::init(isset($_GET['force'])?true:false);
		}
	}
    /**
     * [升级检查]
     */
    public function do_check(){
		if(empty($this->patch)){
			if($_GET['ajax']){
				iUI::json(array('code'=>0));
			}else{
				iUI::success("您使用的 iCMS 版本,目前是最新版本<hr />当前版本：iCMS ".iCMS_VERSION." [".iCMS_RELEASE."]",0,"5");
			}
		}else{
	    	switch(iCMS::$config['system']['patch']){
	    		case "1"://自动下载,安装时询问
					$this->msg = patch::download($this->patch[1]);
					$json      = array(
						'code' => "1",
						'url'  => __ADMINCP__.'=patch&do=install',
						'msg'  => "发现iCMS最新版本<br /><span class='label label-warning'>iCMS ".$this->patch[0]." [".$this->patch[1]."]</span><br />".$this->patch[3]."<hr />您当前使用的版本<br /><span class='label label-info'>iCMS ".iCMS_VERSION." [".iCMS_RELEASE."]</span><br /><br />新版本已经下载完成!! 是否现在更新?",
		    		);
	    		break;
	    		case "2"://不自动下载更新,有更新时提示
		    		$json	= array(
						'code' => "2",
						'url'  => __ADMINCP__.'=patch&do=download',
						'msg'  => "发现iCMS最新版本<br /><span class='label label-warning'>iCMS ".$this->patch[0]." [".$this->patch[1]."]</span><br />".$this->patch[3]."<hr />您当前使用的版本<br /><span class='label label-info'>iCMS ".iCMS_VERSION." [".iCMS_RELEASE."]</span><br /><br />请马上更新您的iCMS!!!",
		    		);
	    		break;
	    	}
	    	if($_GET['ajax']){
	    		iUI::json($json,true);
	    	}
		    $moreBtn=array(
		            array("text"=>"马上更新","url"=>$json['url']),
		            array("text"=>"以后在说","js" =>'return true'),
		    );
    		iUI::dialog('success:#:check:#:'.$json['msg'],0,30,$moreBtn);
		}
    }
    /**
     * [下载升级包]
     */
    public function do_download(){
		$this->msg	= patch::download();//下载文件包
		include admincp::view("patch");
    }
    /**
     * [安装升级包]
     */
    public function do_install(){
		$this->msg.= patch::update();//更新文件
		if(patch::$next){
			$this->msg.= patch::run();//数据库升级
		}
		include admincp::view("patch");
    }
    //===================git=========
    /**
     * [开发版升级检查]
     */
    public function do_git_check(){
    	$log =  patchAdmincp::git('log');
    	include admincp::view("git.log");
    }
    /**
     * [下载开发版升级包]
     */
    public function do_git_download(){
    	$zip_url = patchAdmincp::git('zip',null,'url');
		$release = $_GET['release'];
		$zipName = str_replace(patch::PATCH_URL.'/', '', $zip_url);

		// patch::$release = $release;
		// patch::$zipName = $zipName;
		// $this->do_download();
		iPHP::redirect(APP_URI.'&do=download&release='.$release.'&zipname='.$zipName.'&git=true');
    }
    /**
     * [查看开发版信息]
     */
    public function do_git_show(){
    	$log =  patchAdmincp::git('show',$_GET['commit_id']);
        $type_map = array(
          'D'=>'删除',
          'A'=>'增加',
          'M'=>'更改'
        );
    	include admincp::view("git.show");
    }
	public static function git($do,$commit_id=null,$type='array') {
        $commit_id===null && $commit_id = GIT_COMMIT;
        $last_commit_id = $_GET['last_commit_id'];

		$url = patch::PATCH_URL . '/git?do='.$do.'&commit_id=' .$commit_id.'&last_commit_id=' .$last_commit_id. '&t=' . time();
// 		$url = patch::PATCH_URL . '/git?do='.$do.'&commit_id=7e54fae6d0625f32&t=' . time();
// var_dump($url);
// exit;
		$data = iHttp::remote($url);
		if($type=='array'){
			if($data){
				return json_decode($data,true);
			}
			return array();
		}else{
			if($data){
				return $data;
			}
			if($type=='json'){
				return '[]';
			}
		}
	}
    public static function check_js() {
        include admincp::view("check","patch");
    }
}
