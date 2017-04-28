<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class members{
    const SUPERADMIN_UID ="1";
    const SUPERADMIN_GID ="1";

    public static $userid       = 0;
    public static $data         = array();
    public static $nickname     = NULL;
    public static $group        = array();
    public static $priv         = array();
    public static $GATEWAY      = false;
    public static $AUTH         = 'iCMS_AUTH';
    public static $LOGIN_PAGE   = 'login.php';
    private static $LOGIN_COUNT = 0;

    public static function check($a,$p) {
    	if(empty($a) && empty($p)) {
        	return false;
    	}

        self::$data = iDB::row("SELECT * FROM `#iCMS@__members` WHERE `username`='{$a}' AND `password`='{$p}' AND `status`='1' LIMIT 1;");
        if(empty(self::$data)){
            return false;
        }
        unset(self::$data->password);
        self::$userid   = self::$data->uid;
        self::$nickname = self::$data->nickname?self::$data->nickname:self::$data->username;

        self::$data->info   = json_decode(self::$data->info);
        self::$data->config = json_decode(self::$data->config);

        self::$group  = iDB::row("SELECT * FROM `#iCMS@__group` WHERE `gid`='".self::$data->gid."' LIMIT 1;");
        if(self::$group){
            self::$group->config = json_decode(self::$group->config);
        }else{
            self::$group = new stdClass();
        }

        self::$priv['menu']     = self::merge_priv(self::$data->config->mpriv,self::$group->config->mpriv);
        self::$priv['app']      = self::merge_priv(self::$data->config->apriv,self::$group->config->apriv);
        self::$priv['category'] = self::merge_priv(self::$data->config->cpriv,self::$group->config->cpriv);
        return true;
    }
    //登陆验证
    public static function check_login() {
//        self::$LOGIN_COUNT = (int)authcode(get_cookie('iCMS_LOGIN_COUNT'),'DECODE');
//        if(self::$LOGIN_COUNT>iCMS_LOGIN_COUNT) exit();

        $a   = iSecurity::escapeStr($_POST['username']);
        $p   = iSecurity::escapeStr($_POST['password']);
        $ip  = iPHP::get_ip();
        $sep = iPHP_AUTH_IP?'#=iCMS['.$ip.']=#':'#=iCMS=#';
        if(empty($a) && empty($p)) {
            $auth       = iPHP::get_cookie(self::$AUTH);
            list($a,$p) = explode($sep,authcode($auth,'DECODE'));
            $c = self::check($a,$p);
        }else {
            $p = md5($p);
            $c = self::check($a,$p);
            if ($c){
                iDB::query("
                    UPDATE `#iCMS@__members`
                    SET `lastip`='".$ip."',
                    `lastlogintime`='".time()."',
                    `logintimes`=logintimes+1
                    WHERE `uid`='".self::$userid."'
                ");
                iPHP::set_cookie(self::$AUTH,authcode($a.$sep.$p,'ENCODE'));
            }
        }
        return self::result($c);
    }
    public static function gateway($way){
        self::$GATEWAY = $way;
        return new self();
    }
	private static function result($s=null){
        $s OR self::logout();
        switch (self::$GATEWAY) {
            case 'ajax':
                iUI::json(array('code'=>$s));
            break;
            case 'bool':
                return (bool)$s;
            break;
            default:
                if(!$s){
                    include self::$LOGIN_PAGE;
                    exit;
                }
            break;
        }
	}
	//注销
	public static function logout(){
		iPHP::set_cookie(self::$AUTH,'',-31536000);
	}
	private static function merge_priv($p1,$p2){
        return array_merge((array)$p1,(array)$p2);
	}
    public static function is_superadmin() {
        return (members::$data->gid === self::SUPERADMIN_GID);
    }
    public static function check_priv($p=null, $ret = null) {
        if (members::is_superadmin()) {
            return true;
        }
        if(is_array($p)){
            isset($p['priv']) && $p = $p['priv'];
        }
        //判断当前访问链接权限
        if (stripos($p, '?') !==false){
            // $p = preg_replace('@app=(\w+)_category@is', 'app=category', $p);
            $parse = parse_url($p);
            parse_str($parse['query'], $output);
            $pieces = array($output['app']);
            $output['do'] && $pieces['do']='do='.$output['do'];
            // $output['do'] && $pieces['do'] = $output['do'];
            $pp  = implode('&', $pieces);
            $priv = iPHP::check_priv($pp,self::$priv['menu']);
            //在菜单权限无权限时 查找应用权限
            if(!$priv){
                $output['app'] = preg_replace('@(\w+)_category@is', 'category', $output['app']);
                $pieces = array($output['app']);
                $output['do'] && $pieces['do']=$output['do'];
                $pp = implode('.', $pieces);
                $priv = iPHP::check_priv($pp,self::$priv['app']);
            }
        }else{
            //一般用于判断菜单权限
            $priv = iPHP::check_priv($p,self::$priv['menu']);
        }

        $priv OR self::permission($p, $ret);
        return $priv?true:false;
    }
    public static function permission($p=null, $ret = null) {
        if($ret){
            $title = $p;
            if (stripos($p, '?') !==false){
                $priv = iCache::get('app/priv');
                $p = preg_replace('@app=(\w+)_category@is', 'app=category', $p);
                $priv[$p] && $title = $priv[$p];
            }
            iUI::permission($title, $ret);
            // include self::view("members.permission",'members');
        }
    }
}

