<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
defined('iPHP') OR exit('What are you doing?');

class iCMS {
    public static $config    = array();

	public static function init(){
        self::$config = iPHP::config(array(
            'apps' => array(__CLASS__,'default_apps')
        ));

        iDevice::init(self::$config);

        define('iCMS_URL',       self::$config['router']['url']);
        define('iCMS_PUBLIC_URL',self::$config['router']['public']);
        define('iCMS_USER_URL',  self::$config['router']['user']);
        define('iCMS_FS_URL',    self::$config['FS']['url']);
        define('iCMS_API',       iCMS_PUBLIC_URL.'/api.php');
        define('iCMS_API_URL',   iCMS_API.'?app=');

        iFS::init(self::$config['FS']);
        iCache::init(self::$config['cache']);
        iURL::init(self::$config['router'],array(
            'user_url' => iCMS_USER_URL,
            'api_url'  => iCMS_PUBLIC_URL,
            'tag'      => self::$config['tag'],//标签配置
            'iurl'     => self::$config['iurl'],//应用路由定义
            'device'   => array('iDevice','urls'),//设备网址
            'domain'   => array('categoryApp','domain')//绑定域名回调
        ));
	}
    /**
     * 运行应用程序
     * @param string $app 应用程序名称
     * @param string $do 动作名称
     */
    public static function run($app = NULL,$do = NULL,$args = NULL,$prefix="do_") {
        iView::init();
        iView::$apps = self::$config['apps'];
        iView::$func = 'content';
        iView::$handle->_iVARS = array(
            'VERSION' => iCMS_VERSION,
            'API'     => iCMS_API,
            'SAPI'    => iCMS_API_URL,
            'DEVICE'  => iPHP_DEVICE,
            'CONFIG'  => self::$config,
            'APPID'   => array()
        );
        foreach ((array)self::$config['apps'] as $_app => $_appid) {
            iView::$handle->_iVARS['APPID'][strtoupper($_app)] = $_appid;
        }
        self::send_access_control();
        self::assign_site();

        return iPHP::run($app,$do,$args,$prefix);
    }

    public static function loader($name){
        return iPHP::loader($name,iPHP_APP_CORE);
    }

    public static function API($app = NULL,$do = NULL) {
        $app OR $app = iSecurity::escapeStr($_GET['app']);
        return self::run($app,null,null,'API_');
    }
    public static function send_access_control() {
        header("Access-Control-Allow-Origin: " . iCMS_URL);
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
    }
    public static function assign_site(){
        $site          = self::$config['site'];
        $site['title'] = $site['name'];
        $site['404']   = iPHP_URL_404;
        $site['url']   = iCMS_URL;
        $site['murl']  = self::$config['template']['mobile']['domain'];
        $site['tpl']   = iPHP_DEFAULT_TPL;
        $site['urls']  = array(
            "tpl"    => iCMS_URL.'/template/'.iPHP_DEFAULT_TPL,
            "public" => iCMS_PUBLIC_URL,
            "user"   => iCMS_USER_URL,
            "res"    => iCMS_FS_URL,
            "ui"     => iCMS_PUBLIC_URL.'/ui',
            "avatar" => iCMS_FS_URL.'avatar/',
            "mobile" => $site['murl'],
        );
        if(self::$config['template']['device']){
            foreach (self::$config['template']['device'] as $key => $value) {
                if($value['domain']){
                    $name = trim($value['name']);
                    $site['urls'][$name] = $value['domain'];
                }
            }
        }
        iView::assign('site',$site);
        iUI::$dialog['title']  = $site['name'];
    }
    public static function redirect_html($fp,$url='') {
        if(iView::$gateway=='html'||empty($url)||stristr($url, '.php?')||iPHP_DEVICE!='desktop') return;

        is_file($fp) && iPHP::redirect($url);
    }
    //分页数缓存
    public static function page_total_cache($sql, $type = null,$cachetime=3600) {
        $total = (int) $_GET['total_num'];
        if($type=="G"){
            empty($total) && $total = iDB::value($sql);
        }else{
            $cache_key = 'page_total/'.substr(md5($sql), 8, 16);
            if(empty($total)){
                if (!isset($_GET['page_total_cache'])|| $type === 'nocache'||!$cachetime) {
                    $total = iDB::value($sql);
                    $type === null && iCache::set($cache_key,$total,$cachetime);
                }else{
                    $total = iCache::get($cache_key);
                }
            }
        }
        return (int)$total;
    }
    public static function default_apps() {
        return array(
            'admincp' => '10',
            'config'  => '11',
            'files'   => '12',
            'menu'    => '13',
            'group'   => '14',
            'members' => '15',
            'apps'    => '17',
            'former'  => '18',
            'patch'   => '19',
            'cache'   => '23'
        );
    }

}
