<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
ini_set('display_errors', 'ON');
error_reporting(E_ALL & ~E_NOTICE);

define('iPHP',TRUE);
define('iPHP_APP','iCMS'); //应用名
define('iPHP_APP_MAIL','master@icmsdev.com');
define('iPATH',dirname(strtr(__FILE__,'\\','/'))."/../");

if($_POST['action']=='install'){
    $db_host     = trim($_POST['DB_HOST']);
    $db_user     = trim($_POST['DB_USER']);
    $db_password = trim($_POST['DB_PASSWORD']);
    $db_name     = trim($_POST['DB_NAME']);
    $db_prefix   = trim($_POST['DB_PREFIX']);
    $db_port     = trim($_POST['DB_PORT']);

	define('iPHP_DB_HOST',$db_host);	// 服务器名或服务器ip,一般为localhost
    define('iPHP_DB_PORT', $db_port);   //数据库端口
	define('iPHP_DB_USER',$db_user);		// 数据库用户
	define('iPHP_DB_PASSWORD',$db_password);//数据库密码
	define('iPHP_DB_NAME',$db_name);		// 数据库名
	define('iPHP_DB_PREFIX',$db_prefix);	// 表名前缀, 同一数据库安装多个请修改此处
    define('iPHP_DB_PREFIX_TAG','#iCMS@__');

    require_once iPATH.'iPHP/iPHP.php';
    require_once iPHP_CORE.'/iUI.class.php';
    require_once iPHP_APP_CORE.'/iCMS.class.php';

    iPHP::$apps = iCMS::default_apps();

    $router_url     = iSecurity::escapeStr(trim($_POST['ROUTER_URL'],'/'));
    $admin_name     = iSecurity::escapeStr(trim($_POST['ADMIN_NAME']));
    $admin_password = iSecurity::escapeStr(trim($_POST['ADMIN_PASSWORD']));

	$lock_file = iPATH.'cache/install.lock';
	file_exists($lock_file) && iUI::alert('请先删除 cache/install.lock 这个文件。','js:top.callback();');

	iPHP_DB_HOST OR iUI::alert("请填写数据库服务器地址",'js:top.callback("#DB_HOST");');
	iPHP_DB_USER OR iUI::alert("请填写数据库用户名",'js:top.callback("#DB_USER");');
	iPHP_DB_PASSWORD OR iUI::alert("请填写数据库密码",'js:top.callback("#DB_PASSWORD");');
	iPHP_DB_NAME OR iUI::alert("请填写数据库名",'js:top.callback("#DB_NAME");');
	strstr(iPHP_DB_PREFIX, '.') && iUI::alert("您指定的数据表前缀包含点字符，请返回修改",'js:top.callback("#DB_PREFIX");');
	//preg_match('/([a-zA-z\_]+)/is', $db_prefix) OR iUI::alert("您指定的数据表前缀包含非法字符，请返回修改",'js:top.callback("#DB_PREFIX");');

	$admin_name OR iUI::alert("请填写超级管理员账号",'js:top.callback("#ADMIN_NAME");');
	$admin_password OR iUI::alert("请填写超级管理员密码",'js:top.callback("#ADMIN_PASSWORD");');
	strlen($admin_password)<6 && iUI::alert("请填写超级管理员密码",'js:top.callback("#ADMIN_PASSWORD");');

    $mysql_link = iDB::connect('link');
	// $mysql_link = @mysql_connect($db_host,$db_user,$db_password);
	$mysql_link OR iUI::alert("数据库连接出错",'js:top.callback();');
    //(MYSQL ERROR:".iDB::$last_error.")
	// mysql_query("SET NAMES '".iPHP_DB_CHARSET."'");
	// @mysql_select_db($db_name,$mysql_link) OR iUI::alert("数据库{$db_name}不存在",'js:top.callback("#DB_NAME");');
    if(isset($_POST['CREATE_DATABASE'])){
        iDB::connect('!select_db');
        iDB::query("CREATE DATABASE `".iPHP_DB_NAME."`CHARACTER SET utf8 COLLATE utf8_general_ci",'get')
        OR
        iUI::alert('数据库创建失败,请确认数据库是否已存在或该用户是否有权限创建数据库','js:top.callback();');
    }else{
        iDB::connect();
    }
    iDB::pre_set();
    iDB::select_db(true) OR iUI::alert("不能链接到数据库".iPHP_DB_NAME,'js:top.callback("#DB_NAME");');

	$config_file  = iPATH.'config.php';
	$content = iFS::read($config_file,false);
	$content = preg_replace("/define\('iPHP_DB_HOST',\s*'.*?'\)/is", 		"define('iPHP_DB_HOST','".iPHP_DB_HOST."')", $content);
	$content = preg_replace("/define\('iPHP_DB_USER',\s*'.*?'\)/is", 		"define('iPHP_DB_USER','".iPHP_DB_USER."')", $content);
	$content = preg_replace("/define\('iPHP_DB_PASSWORD',\s*'.*?'\)/is", 	"define('iPHP_DB_PASSWORD','".iPHP_DB_PASSWORD."')", $content);
	$content = preg_replace("/define\('iPHP_DB_NAME',\s*'.*?'\)/is", 		"define('iPHP_DB_NAME','".iPHP_DB_NAME."')", $content);
	$content = preg_replace("/define\('iPHP_DB_PREFIX',\s*'.*?'\)/is", 	    "define('iPHP_DB_PREFIX','".iPHP_DB_PREFIX."')", $content);
	$content = preg_replace("/define\('iPHP_KEY',\s*'.*?'\)/is", 			"define('iPHP_KEY','".random(64)."')",$content);

	iFS::write($config_file,$content,false);
//开始安装 数据库 结构
	$sql_file = dirname(strtr(__FILE__,'\\','/')).'/iCMS.sql';
	is_readable($sql_file) OR iUI::alert('数据库文件不存在或者读取失败','js:top.callback();');
	$sql = iFS::read($sql_file);
	run_query($sql);
//导入默认数据
    $data_sql_file = dirname(strtr(__FILE__,'\\','/')).'/iCMS-data.sql';
    is_readable($data_sql_file) OR iUI::alert('数据库文件不存在或者读取失败','js:top.callback();');
    $data_sql = iFS::read($data_sql_file);
    run_query($data_sql);

//设置超级管理员
	$admin_password = md5($admin_password);
	iDB::query("
		UPDATE `#iCMS@__members`
		SET `username` = '{$admin_name}', `password` = '{$admin_password}'
		WHERE `uid` = '1';
	");

//配置程序
    define('iPHP_APP_CONFIG', iFS::path(iPHP_CONF_DIR . '/' . iPHP_APP . '/config.php')); //网站配置文件

    $config = configAdmincp::get();
    $config['router']['url']    = $router_url;
    $config['router']['public'] = $router_url.'/public';
    $config['router']['user']   = $router_url.'/user';
    $config['router']['404']    = $router_url.'/public/404.htm';
    $config['FS']['url']        = $router_url.'/res/';
    $config['tag']['url']       = $router_url;
	$config['template']['mobile']['domain'] = $router_url;

    foreach($config AS $n=>$v){
        config_set($v,$n);
    }
    configAdmincp::cache();

//写入数据库配置<hr />开始安装数据库<hr />数据库安装完成<hr />设置超级管理员<hr />更新网站缓存<hr />
	iFS::write($lock_file,'iCMS.'.time(),false);
	iFS::rmdir(iPATH.'install');
	iUI::success("安装完成",'js:top.install.step4();');
}
function config_set($value, $name) {
    is_array($value) && $value = addslashes(json_encode($value));
    $sql = "`name` ='$name'";
    $check  = iDB::value("SELECT `name` FROM `#iCMS@__config` WHERE {$sql}");
    $fields = array('name','value');
    $data   = compact ($fields);
    if($check===null){
        iDB::insert('config',$data);
    }else{
        iDB::update('config', $data, array('name'=>$name));
    }
}

function run_query($sql) {
	$sql      = str_replace("\r", "\n", $sql);
	$resource = array();
	$num      = 0;
	$sql_array = explode(";\n", trim($sql));
    foreach($sql_array as $query) {
        $queries = explode("\n", trim($query));
        foreach($queries as $query) {
            $resource[$num] .= $query[0] == '#' ? '' : $query;
        }
        $num++;
    }
    unset($sql);

    foreach($resource as $key=>$query) {
        $query = trim($query);
        $query = str_replace('`icms_', '`#iCMS@__', $query);
        $query && iDB::query($query);
    }
}
