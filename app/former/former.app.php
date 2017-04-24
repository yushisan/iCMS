<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
*/
class formerApp{
    public function __construct() {
        $this->appid = iCMS_APP_FORMER;
    }
    /**
     * [创建表单]
     * @param  [type]  $app        [app数据/appid]
     * @param  [type]  $rs         [数据]
     * @param  boolean $union_data [是否查询数据表]
     * @return [type]              [description]
     */
    public static function add($app,$rs,$union_data=false){
        is_array($app) OR $app = apps::get($app);
        $data_table_name = apps_mod::data_table_name($app['app']);
        if($app['fields']){
            $data_table = $app['table'][$data_table_name];
            if($data_table){
                $data_fields = apps_mod::base_fields($app['app']);
                $primary_key = $data_table['primary'];
                $union_key   = $data_table['union'];
                $fpk = $data_fields[$primary_key];
                $fpk && $app['fields']+= array($primary_key=>$fpk);
                $fuk = $data_fields[$union_key];
                $fuk && $app['fields']+= array($union_key=>$fuk);

                if($union_data){
                    $table    = reset($app['table']);
                    $id       = $rs[$table['primary']];
                    $union_key&& $primary_key = $union_key;
                    $urs = (array)iDB::row("SELECT * FROM `{$data_table['table']}` WHERE `{$primary_key}`='$id' LIMIT 1;",ARRAY_A);
                    $urs && $rs+=$urs;
                }
            }
            former::$template['class'] = array(
                'group'    => 'input-prepend input-append',
                'label'    => 'add-on',
                'label2'   => 'add-on',
                'radio'    => 'add-on',
                'checkbox' => 'add-on',
            );
            former::$config['value']   = array(
                'userid'   => members::$userid,
                'username' => members::$data->username,
                'nickname' => members::$data->nickname
            );
            former::$config['gateway'] = 'admincp';
            former::create($app,$rs);
        }
    }
    /**
     * [保存表单]
     * @param  [type] $app    [app数据/appid]
     * @param  [type] $pri_id [主键值]
     * @return [type]         [description]
     */
    public static function save($app,$pri_id=null){
        is_array($app) OR $app = apps::get($app);

        if($app['fields']){

            list($variable,$tables,$orig_post,$imap,$tags) = former::post($app);

            // if(!$variable){
            //     iUI::alert("表单数据处理出错!");
            // }
            //非自定义应用数据
            if($pri_id){
                $pri_table = reset($app['table']);
            }

            $update = false;
            if($variable)foreach ($variable as $table_name => $_data) {
                if(empty($_data)){
                  continue;
                }
                // if($data && $table_name==$pri_table['name']){
                //     $data = array_merge($data,$_data);
                //     continue;
                // }
                //当前表 数据
                $_table   = $app['table'][$table_name];
                //当前表 主键
                $primary = $_table['primary'];
                //关联字符 && 关联数据
                if($_table['union'] && $union_data){
                  $_data[$_table['union']] = $union_data[$_table['union']];
                }
                //非自定义应用数据
                if($pri_id && $table_name==$pri_table['name']){
                    $_data[$pri_table['primary']] = $pri_id;
                }

                $id = $_data[$primary];
                unset($_data[$primary]);//主键不更新
                if(empty($id)){ //主键值为空
                    $id = iDB::insert($table_name,$_data);
                }else{
                    $update = true;
                    iDB::update($table_name, $_data, array($primary=>$id));
                }
                $union_id = apps_mod::data_union_id($table_name);
                empty($_table['union']) && $union_data[$union_id] = $id;
            }

            if($imap)foreach ($imap as $key => $value) {
                iMap::init($value[0],$app['id'],$key);
                if($update){
                    $orig = $orig_post[$key];
                    iMap::diff($value[1],$orig,$id);
                }else{
                    iMap::add($value[1],$id);
                }
            }

            if($tags)foreach ($tags as $key => $value) {
                if(empty($value[0])){
                    continue;
                }
                tag::$appid = $app['id'];
                if($update){
                    $orig = $orig_post[$key];
                    tag::diff($value[0],$orig,members::$userid,$id,$value[1]);
                }else{
                    tag::add($value[0],members::$userid,$id,$value[1]);
                }
            }
            return $update;
        }
    }
}
