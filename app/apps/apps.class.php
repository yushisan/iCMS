<?php
/**
 * @package iCMS
 * @copyright 2007-2017, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */

class apps {
    public static $table   = 'article';
    public static $primary = 'id';
    public static $appid   = '1';
    public static $etc     = 'etc';
    public static $array   = array();
    public static $type_array = array(
        '2' => '自定义应用',
        '3' => '第三方应用',
        '1' => '系统应用',
        '0' => '系统组件',
    );

    public static function uninstall($app){
        is_array($app) OR $app = self::get($app);
        if($app){
            self::__uninstall($app);
            // $obj_name = $data['app'].'Admincp';
            // var_dump(@class_exists($obj_name));
            // $obj_name = $data['app'].'App';
            // var_dump(@class_exists($obj_name));
            // $obj_name = $data['app'];
            // var_dump(@class_exists($obj_name));
            // $app = iPHP::app($data['app'].'.app');
            // if(is_object($app)){
            //     $app_methods = get_class_methods($app);
            //     in_array('__uninstall', $app_methods) OR iUI::alert('卸载出错！ ['.$data['name'].']应用没有设置反安装程序[uninstall],请直接手动删除！');
            //     return $app->__uninstall($data,self);
            // }
        }
        // return false;
    }
    private  static function __uninstall($app){
        //删除分类
        categoryAdmincp::del_app_data($app['id']);
        //删除属性
        propAdmincp::del_app_data($app['id']);
        //删除文件
        files::del_app_data($app['id']);
        //删除配置
        configAdmincp::del($app['id'],$app['app']);
        //删除表
        self::drop_table($app['table']);
        //删除数据
        self::del_data($app['id']);
        //查找app目录
        $appdir = iPHP_APP_DIR . '/' . $app['app'];
        // 删除应用
        file_exists($appdir) && iFS::rmdir($appdir);
    }
    public static function installed($app,$r=false){
        $path  = iPHP_APP_DIR.'/'.$app.'/etc/iAPP.install.lock';
        if($r){
            return $path;
        }
        return file_exists($path);
    }
    public static function del_data($id){
        $id && iDB::query("DELETE FROM `#iCMS@__apps` WHERE `id` = '{$id}'; ");
    }
    public static function drop_table($table){
        if($table)foreach ((array)$table as $key => $value) {
            $value['table'] && iDB::query("DROP TABLE IF EXISTS `".$value['table']."`");
        }
    }

    public static function menu($app){
        $array = $app['menu'];
        if($app['config']['menu']){
            if($app['config']['menu']!='main'){
                $json = '[{"id": "'.$app['config']['menu'].'","children":[]}]';
                $_array = json_decode($json,true);
                $_array[0]['children'][]=$array[0];
                $array = $_array;
            }
        }
        return $array;
    }
    public static function item($rs){
        if($rs){
            $rs = (array)$rs;
            if($rs['table']){
                $table = json_decode($rs['table'],true);
                $table && $rs['table']  = apps::table_item($table);
            }
            $rs['config']&& $rs['config']  = json_decode($rs['config'],true);
            $rs['menu']  && $rs['menu']    = json_decode($rs['menu'],true);
            $rs['fields']&& $rs['fields']  = json_decode($rs['fields'],true);
        }
        return $rs;
    }
    public static function get($vars=0,$field='id'){
        if(empty($vars)) return array();
        if($vars=='all'){
            $sql      = '1=1';
            $is_multi = true;
        }else{
            list($vars,$is_multi)  = iSQL::multi_var($vars);
            $sql  = iSQL::in($vars,$field,false,true);
        }
        $data = array();
        $rs   = iDB::all("SELECT * FROM `#iCMS@__apps` where {$sql}",OBJECT);
        if($rs){
            if($is_multi){
                $_count = count($rs);
                for ($i=0; $i < $_count; $i++) {
                    $data[$rs[$i]->$field]= apps::item($rs[$i]);
                }
            }else{
                $data = apps::item($rs[0]);
            }
        }
        if(empty($data)){
            return;
        }
        return $data;
    }
    public static function check($app){
        $apps = iCMS::$config['apps'];
        if(is_numeric($app)){
            return array_search($app,$apps);
        }else{
            return array_key_exists($app, $apps);
        }
    }
    public static function get_array($vars,$field="*",$orderby=''){
        $sql = iSQL::where($vars,false);
        $orderby && $sql.= 'order by '.$orderby;
        $rs  = iDB::all("SELECT {$field} FROM `#iCMS@__apps` where {$sql}",OBJECT);
        $_count = count($rs);
        for ($i=0; $i < $_count; $i++) {
            $data[$rs[$i]->id]= apps::item($rs[$i]);
        }
        return $data;
    }
    public static function get_iurl(){
        $rs = apps::get_array(array('status'=>'1'));
        foreach ((array)$rs as $key => $value) {
            $router = apps_mod::iurl($value);
            $router && $array[$value['app']] = $router;
        }
        return $array;
    }
    public static function get_appsid($app=null,$trans=false){
        $rs = apps::get_array(array('status'=>'1'));
        foreach ((array)$rs as $key => $value) {
            $array[$value['app']] = $value['id'];
        }
        $trans && $array = array_flip($array);

        if($app){
            return $array[$app];
        }

        return $array;
    }
    // public static function get_hooks(){
    //     $rs = apps::get_array(array('status'=>'1'));
    //     foreach ($rs as $key => $value) {
    //         $config = $value['config'];
    //         if($config['hooks']){
    //             foreach ($config['hooks'] as $_app => $hooks) {
    //                 foreach ($hooks as $field => $callback) {
    //                     $array[$_app][$field][]= (array)$callback;
    //                 }
    //             }
    //         }

    //     }
    //     return $array;
    // }
    public static function get_type_select($not=null){
      $option = '';
      foreach (self::$type_array as $key => $type) {
        if($not!==null){
            $notArray = explode(',', $not);
            if(array_search($key, $notArray)!==false){
                continue;
            }
        }
        $option.='<option value="'.$key.'">'.$type.'[type=\''.$key.'\']</option>';
      }
      $option.= propAdmincp::get("type");
      return $option;
    }
    // public static function get_file($app,$filename,$sapp=null){
    //     $app_path = iPHP_APP_DIR."/$app/".$filename;
    //     if(file_exists($app_path)){
    //         return array($app,$filename,$sapp);
    //     }else{
    //         return false;
    //     }
    // }
    // public static function scan($pattern='*.app',$appdir='*',$ret=false){
    //     $array = array();
    //     foreach (glob(iPHP_APP_DIR."/{$appdir}/{$pattern}.php") as $filename) {
    //         $parts = pathinfo($filename);
    //         $app   = str_replace(iPHP_APP_DIR.'/','',$parts['dirname']);

    //         if(stripos($app, '/') !== false){
    //             list($app,) = explode('/', $app);
    //         }
    //         $path = str_replace(iPHP_APP_DIR.'/','',$filename);
    //         list($a,$b,) = explode('.', $parts['filename']);
    //         $array[$app] = array($a,$b,$path);
    //     }
    //     if($ret){
    //         return $array;
    //     }
    //     self::$array = $array;
    //     // var_dump(self::$array);
    // }
    // public static function config($pattern='iAPP.json',$dir='*'){
    //     $array = self::scan('etc/'.$pattern,$dir,true);
    //     $data  = array();
    //     foreach ($array as $key => $path) {
    //         if(stripos($path, $pattern) !== false){
    //             $rpath  = iPHP_APP_DIR.'/'.$path;
    //             $json  = file_get_contents($rpath);
    //             $json  = substr($json, 56);
    //             $jdata = json_decode($json,true);
    //             $error = json_last_error();
    //             if($error!==JSON_ERROR_NONE){
    //                 $data[$path] = array(
    //                     'title'        => $path,
    //                     'description' => json_last_error_msg()
    //                 );
    //             }
    //             if($jdata && is_array($jdata)){
    //                 $data[$jdata['app']] = $jdata;
    //             }
    //         }
    //     }
    //     return $data;
    // }

    // public static function setting($t='setting',$appdir='*',$pattern='*.setting'){

    //     $array = self::scan('admincp/'.$pattern,$appdir,true);
    //     // var_dump($array);
    //     $app_array = iCache::get('app/cache_name');
    //     // var_dump($app_array);
    //     $paths = array();
    //     foreach ($array as $key => $path) {
    //         $appinfo = $app_array[$key];
    //         if($t=='tabs'){
    //             echo '<li><a href="#setting-'.$key.'" data-toggle="tab">'.$appinfo['title'].'</a></li>';
    //         }
    //         if ($t == 'setting'){
    //             $paths[$key] =  iPHP_APP_DIR.'/'.$path;
    //         }
    //     }
    //     return $paths;
    // }

    public static function table_item($variable){
        is_array($variable) OR $variable = json_decode($variable,true);
        if($variable){
            foreach ($variable as $key => $value) {
                if(count($value)>3){
                    $table[$value[0]]=array(
                            'table'   => iPHP_DB_PREFIX.$value[0],
                            'name'    => $value[0],
                            'primary' => $value[1],
                            'union'   => $value[2],
                            'label'   => $value[3],
                        );
                }else{
                    $table[$value[0]]=array(
                        'table'   => iPHP_DB_PREFIX.$value[0],
                        'name'    => $value[0],
                        'primary' => $value[1],
                        'label'   => $value[2],
                    );
                }
            }
            return $table;
        }
    }

	public static function cache(){
        $rs = iDB::all("SELECT * FROM `#iCMS@__apps`");

        foreach((array)$rs AS $a) {
            $a = apps::item($a);
			$appid_array[$a['id']] = $a;
			$app_array[$a['app']]  = $a;

            self::set_app_cache($a);
        }
        iCache::set('app/idarray',  $appid_array,0);
        iCache::set('app/array',$app_array,0);
        configAdmincp::cache();
        return true;
	}
    public static function set_app_cache($a){
        if(!is_array($a)){
            $a = self::get($a);
        }
        iCache::set('app/'.$a['id'],$a,0);
        iCache::set('app/'.$a['app'],$a,0);
    }
    public static function get_path($app,$type='app',$arr=false){
        $path = iPHP_APP_DIR . '/' . $app . '/' . $app.'.'.$type.'.php';
        if($arr){
            $obj  = $app.ucfirst($type);
            return array($path,$obj);
        }
        return $path;
    }
    public static function get_func($app,$tag=false){
        list($path,$obj_name)= apps::get_path($app,'func',true);
        if(is_file($path)){
            $class_methods = get_class_methods($obj_name);
            if($tag){
                foreach ($class_methods as $key => $value) {
                    $tag_array[]= iPHP_APP.':'.str_replace('_', ':', $value);
                }
                return $tag_array;
            }else{
                return $class_methods;
            }
        }
    }
	public static function get_app($appid=1){
		$rs	= iCache::get('app/'.$appid);
       	$rs OR iPHP::error_throw('application no exist', '0005');
       	return $rs;
	}

    public static function get_app_lite($data=null) {
        if(!is_array($data)){
            $data = apps::get_app($data);
        }
        unset($data['table'],$data['config'],$data['fields'],$data['menu']);
        return $data;
    }
	public static function get_url($appid=1,$primary=''){
        $rs    = self::get_app($appid);
        if($rs['table']){
            $table = reset($rs['table']);
            $key   = $table['primary'];
        }
        empty($key) && $key = 'id';

		return iCMS_URL.'/'.$rs['app'].'.php?'.$key.'='.$primary;
	}
	public static function get_table($app=1,$master=true){
		if(is_array($app)){
            $rs = $app;
        }else{
            $rs = self::get_app($app);
        }
        $table = $rs['table'];
        $master && $table = reset($rs['table']);
       	return $table;
	}
	public static function get_label($appid=0){
        $rs = self::get_app($appid);
        $table = reset($rs['table']);

		if($table['label']){
			return $table['label'];
		}else{
            return $rs['name'];
        }
	}
    public static function get_zip($name,$dir,$REMOVE_PATH=null) {
        iPHP::import(iPHP_LIB . '/pclzip.class.php'); //加载zip操作类
        $zipFile = iPHP_APP_CACHE.'/'.$name.'.zip';
        $zip = new PclZip($zipFile);
        if($REMOVE_PATH){
            $v_list = $zip->create($dir,PCLZIP_OPT_REMOVE_PATH, $REMOVE_PATH); //将文件进行压缩
        }else{
            $v_list = $zip->create($dir); //将文件进行压缩
        }
        $v_list == 0 && iPHP::error_throw($zip->errorInfo(true)); //如果有误，提示错误信息。
        return $zipFile;
    }
    public static function update_count($id,$appid=0,$field,$math='+',$count=1){
        $rs = self::get_app($appid);
        $tables = reset($rs['table']);
        if($tables){
            $fields = apps_db::fields($tables['table']);
            if($fields[$field]){
                $math=='-' && $sql = " AND `{$field}`>0";
                iDB::query("
                    UPDATE `".$tables['table']."`
                    SET `{$field}` = {$field}{$math}{$count}
                    WHERE `".$tables['primary']."` = '$id' {$sql}
                ");
            }
        }
    }
}
