<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

define('STORE_DIR', iPATH . 'cache/iCMS/store/');//临时文件夹

class apps_store {
    const STORE_URL = "https://store.icmsdev.com";
    public static $zipName = null;
    public static $zipFile = null;
    public static $next = false;
    public static $test = false;
    public static $msg_mode = null;
    public static $app_id = null;
    public static $app_name = null;
    public static $app_data = false;

    public static function download($url=null,$name,$zipName=null) {
        iFS::mkdir(STORE_DIR);
        $zipName===null && $zipName = basename($url);
        self::$zipName = $zipName;
        self::$zipFile = STORE_DIR . self::$zipName; //临时文件

        $msg = self::msg('正在下载 [' . $name . '] 安装包',true);
        // $msg.= self::msg($url,true);
        $msg.= self::msg('安装包下载完成',true);

        if (iFS::ex(self::$zipFile) && (filemtime(self::$zipFile)-time()<3600)) {
            return $msg;
        }
        $FileData = iHttp::remote($url);
        if ($FileData) {
            iFS::mkdir(STORE_DIR);
            iFS::write(self::$zipFile, $FileData); //下载更新包
            return $msg;
        }
    }
    public static function install_template($dir=null) {
        $zipFile = self::$zipFile;
        if(!file_exists($zipFile)){
            return self::msg("安装包不存在",false);
        }

        iPHP::import(iPHP_LIB . '/pclzip.class.php'); //加载zip操作类
        $zip = new PclZip($zipFile);
        if (false == ($archive_files = $zip->extract(PCLZIP_OPT_EXTRACT_AS_STRING))) {
          return self::msg("ZIP包错误",false);
        }

        if (0 == count($archive_files)) {
          return self::msg("空的ZIP文件",false);
        }

        $msg = null;
        if ($archive_files) {
            $setup_msg = self::setup_template_file($archive_files,$dir);
            if(is_array($setup_msg)){
                $msg.= $setup_msg[0];
            }else{
                return self::msg($msg.$setup_msg);
            }
        }
        self::$test OR iFS::rm(self::$zipFile);
        $msg.= self::msg('模板安装完成',true);
        return $msg;
    }
    public static function setup_template_file(&$archive_files,$dir){
        $msg = self::msg('开始测试目录权限',true);
        if (!iFS::checkDir(iPATH)) {
            return self::msg(iPATH.'根目录无写权限',false);
        }

        if (!iFS::checkDir(iPHP_TPL_DIR)) {
            return self::msg(iPHP_TPL_DIR . '目录无写权限',false);
        }

        $path = iPHP_TPL_DIR.'/'.$dir;
        self::$test OR iFS::mkdir($path);

        $msg.= self::msg('开始安装模板',true);
        foreach ($archive_files as $file) {
            $folder = $file['folder'] ? $file['filename'] : dirname($file['filename']);
            $dp = $path .'/'.trim($folder,'/').'/';
            if (!iFS::ex($dp)) {
                self::$test OR iFS::mkdir($dp);
                $msg.= self::msg('创建文件夹 [' . $dp . ']',true);
            }
            if (!$file['folder']) {
                $fp = $path .'/'. $file['filename'];
                self::$test OR iFS::write($fp, $file['content']);
                $msg.= self::msg('安装文件 [' . $fp . ']',true);
            }
        }
        return array($msg,true);
    }
    public static function install() {
        $zipFile = self::$zipFile;
        if(!file_exists($zipFile)){
            return self::msg("安装包不存在",false);
        }

        iPHP::import(iPHP_LIB . '/pclzip.class.php'); //加载zip操作类
        $zip = new PclZip($zipFile);
        if (false == ($archive_files = $zip->extract(PCLZIP_OPT_EXTRACT_AS_STRING))) {
          return self::msg("ZIP包错误",false);
        }

        if (0 == count($archive_files)) {
          return self::msg("空的ZIP文件",false);
        }
        $msg = null;
        //安装应用数据
        $setup_msg = self::setup_app_data($archive_files);
        if($setup_msg===true){
            $msg.= self::msg('应用数据安装完成',true);
        }else{
            return self::msg($setup_msg.'安装出错',false);
        }
        //创建应用表
        if(self::setup_app_table($archive_files)){
            $msg.= self::msg('应用表创建完成',true);
        }

        if (count($archive_files)>1) {
            $setup_msg = self::setup_app_file($archive_files);
            if(is_array($setup_msg)){
                $msg.= $setup_msg[0];
            }else{
                return self::msg($msg.$setup_msg);
            }
        }
        self::$test OR iFS::rm(self::$zipFile);
        apps::cache() && $msg.= self::msg('更新应用缓存',true);
        menu::cache() && $msg.= self::msg('更新菜单缓存',true);
        $msg.= self::msg('应用安装完成',true);
        return $msg;
    }
    public static function setup_app_file(&$archive_files){
        $msg = self::msg('开始测试目录权限',true);
        if (!iFS::checkDir(iPATH)) {
            return self::msg(iPATH.'根目录无写权限',false);
        }

        if (!iFS::checkDir(iPHP_APP_DIR)) {
            return self::msg(iPHP_APP_DIR . '目录无写权限',false);
        }

        self::$next = true;
        $msg.= self::msg('开始安装应用',true);
        foreach ($archive_files as $file) {
            $folder = $file['folder'] ? $file['filename'] : dirname($file['filename']);
            $dp = iPHP_APP_DIR .'/'.trim($folder,'/').'/';

            if (!iFS::ex($dp)) {
                self::$test OR iFS::mkdir($dp);
                $msg.= self::msg('创建文件夹 [' . $dp . ']',true);
            }
            if (!$file['folder']) {
                $fp = iPHP_APP_DIR .'/'. $file['filename'];
                self::$test OR iFS::write($fp, $file['content']);
                $msg.= self::msg('安装文件 [' . $fp . ']',true);
            }
        }
        return array($msg,true);
    }
    public static function setup_app_data(&$archive_files){
        foreach ($archive_files AS $key => $file) {
            $filename = basename($file['filename']);
            if($filename=="iCMS.APP.DATA.php"){
              $content = get_php_content($file['content']);
              $content = base64_decode($content);
              $array   = unserialize($content);
              $check_app = iDB::value("
                SELECT `id` FROM `#iCMS@__apps`
                WHERE `app` ='".$array['app']."'
              ");
              if($check_app){
                $_msg = self::msg('检测应用是否存在',false);
                return self::msg($_msg.'该应用已经存在',false);
              }

              if($array['table']){
                $tableArray = apps::table_item($array['table']);
                foreach ($tableArray AS $value) {
                  if(apps_db::check_table($value['table'])){
                    $_msg = self::msg('检测应用表是否存在',false);
                    return self::msg($_msg.'['.$value['table'].']数据表已经存在');
                  }
                }
              }
              $array['addtime'] = time();
              $array = array_map('addslashes', $array);
              self::$test OR self::$app_id = iDB::insert("apps",$array);
              unset($archive_files[$key]);
              self::$app_data = $array;
              self::$app_name = $array['name'];
              return true;
            }
        }
        return false;
    }
    public static function setup_app_table(&$archive_files){
        foreach ($archive_files AS $key => $file) {
            $filename = basename($file['filename']);
            if($filename=="iCMS.APP.TABLE.php"){
              $content = get_php_content($file['content']);
              if(!self::$test){
                $content && apps_db::multi_query($content);
              }
              unset($archive_files[$key]);
              return true;
            }
        }
        return false;
    }
    public static function msg($text,$s=0){
        $text = str_replace(iPATH,'iPHP://',$text);
        if(self::$msg_mode=='alert'){
            $s OR iUI::alert($text);
        }else{
            $a = 80;
            $c = $a-strlen($text);
            $c<1 && $c = 1;
            return $text.str_repeat('.',$c).iUI::check($s).'<iCMS>';
        }
    }
    // public static function check_tpl_path($path){
    //     $pos = stripos($path, '/template/');
    //     if ($pos!==false){
    //          $path = iPHP_TPL_DIR.'/'.substr($path, $pos+10);
    //     }
    //     return $path;
    // }
}
