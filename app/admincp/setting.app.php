<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
*/
class settingApp{
    public function __construct() {}
    public function do_iCMS(){
    	$config	= $this->get();
    	$config['site']['indexName'] OR $config['site']['indexName'] = 'index';
        $redis    = extension_loaded('redis');
        $memcache = extension_loaded('memcached');
        admincp::$menu->url = __ADMINCP__.'='.admincp::$APP_NAME;
    	include admincp::view("setting");
    }
    public function apps(){
        iPHP::app('apps.class','static');
        APPS::scan();
        $apps  = array();
        foreach (APPS::$array as $key => $value) {
            $apps[] = $key;
        }
        return $apps;
    }
    /**
     * [do_save 保存配置]
     * @return [type] [description]
     */
    public function do_save(){
        $config = iSecurity::escapeStr($_POST['config']);

        iFS::allow_files($config['FS']['allow_ext']) OR iUI::alert("附件设置 > 允许上传类型设置不合法!");
        iFS::allow_files(trim($config['router']['html_ext'],'.')) OR iUI::alert('URL设置 > 文件后缀设置不合法!');

        $config['router']['html_ext']   = '.'.trim($config['router']['html_ext'],'.');
        $config['router']['URL']        = trim($config['router']['URL'],'/');
        $config['router']['public_url'] = rtrim($config['router']['public_url'],'/');
        $config['router']['user_url']   = rtrim($config['router']['user_url'],'/');
        $config['router']['DIR']        = rtrim($config['router']['DIR'],'/').'/';
        $config['router']['html_dir']   = rtrim($config['router']['html_dir'],'/').'/';
        $config['FS']['url']            = trim($config['FS']['url'],'/').'/';

        foreach ((array)$config['open'] as $platform => $value) {
            if($value['appid'] && $value['appkey']){
                $config['open'][$platform]['enable'] = true;
            }
        }

    	foreach($config AS $n=>$v){
    		$this->set($v,$n,0);
    	}
    	$this->cache();
    	iUI::success('更新完成','js:1');
    }
    /**
     * [cache 更新配置]
     * @return [type] [description]
     */
    public function cache(){
        $config         = $this->get();
        $config['apps'] = $this->apps();
        $this->write($config);
    }
    public static function head($title=null){
        include admincp::view("setting.head","admincp");
    }
    public static function foot(){
        include admincp::view("setting.foot","admincp");
    }
    /**
     * [app 其它应用配置接口]
     * @param  integer $appid [应用ID]
     * @param  [sting] $name   [应用名]
     */
    public function app($appid=0,$name=null,$ret=false){
        $name===null && $name = admincp::$APP_NAME;
        $config = $this->get($appid,$name);
        if($ret){
            return $config;
        }
        include admincp::view($name.".setting");
    }
    /**
     * [save 其它应用配置保存]
     * @param  integer $appid [应用ID]
     * @param  [sting] $app   [应用名]
     */
    public function save($appid=0,$name=null,$handler=null){
        $name===null   && $name = admincp::$APP_NAME;
        empty($appid) && iUI::alert("配置程序出错缺少APPID!");
        $config = iSecurity::escapeStr($_POST['config']);
        $this->set($config,$name,$appid,false);
        if (is_callable($handler)) {
            call_user_func_array($handler, array($config));
        }
        $this->cache();
        iUI::success('配置更新完成','js:1');
    }
    /**
     * [get 获取配置]
     * @param  integer $appid [应用ID]
     * @param  [type]  $name   [description]
     * @return [type]       [description]
     */
    public function get($appid = NULL, $name = NULL) {
        if ($name === NULL) {
            $sql = $appid === NULL?'':" AND `appid`='$appid'";
            $rs  = iDB::all("SELECT * FROM `#iCMS@__config` WHERE appid< '999999' $sql");
            foreach ($rs AS $c) {
                $value = $c['value'];
                strpos($c['value'], 'a:')===false OR $value = unserialize($c['value']);
                $config[$c['name']] = $value;
            }
            return $config;
        } else {
            $value = iDB::value("SELECT `value` FROM `#iCMS@__config` WHERE `appid`='$appid' AND `name` ='$name'");
            strpos($value, 'a:')===false OR $value = unserialize($value);
            return $value;
        }
    }
    /**
     * [set 更新配置]
     * @param [type]  $v     [description]
     * @param [type]  $n     [description]
     * @param [type]  $appid   [description]
     * @param boolean $cache [description]
     */
    public function set($value, $name, $appid, $cache = false) {
        $cache && iCache::set('iCMS/config/' . $name, $value, 0);
        is_array($value) && $value = addslashes(serialize($value));
        $check  = iDB::value("SELECT `name` FROM `#iCMS@__config` WHERE `appid` ='$appid' AND `name` ='$name'");
        $fields = array('appid','name','value');
        $data   = compact ($fields);
        if($check===null){
            iDB::insert('config',$data);
        }else{
            iDB::update('config', $data, array('appid'=>$appid,'name'=>$name));
        }
    }
    /**
     * [write 配置写入文件]
     * @param  [type] $config [description]
     * @return [type]         [description]
     */
    public function write($config=null){
        $config===null && $config = $this->get();
        $output = "<?php\ndefined('iPHP') OR exit('Access Denied');\nreturn ";
        $output.= var_export($config,true);
        $output.= ';';
        iFS::write(iPHP_APP_CONFIG,$output);
    }
    /**
     * [update 单个配置更新]
     * @param  [type] $k [description]
     * @return [type]    [description]
     */
    public function update($k){
        $this->set(iCMS::$config[$k],$k,0);
        $this->write();
    }
    public function view(){
        include admincp::view('setting',null,true);
    }
}
