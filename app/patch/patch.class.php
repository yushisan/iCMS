<?php
/**
 * iCMS - i Content Management System
 * Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
 *
 * @author icmsdev <master@icmsdev.com>
 * @site https://www.icmsdev.com
 * @licence https://www.icmsdev.com/LICENSE.html
 */
/**
 * 自动更新类
 *
 * @author icmsdev
 */
define('PATCH_DIR', iPATH . 'cache/iCMS/patch/');//临时文件夹
iHttp::$CURLOPT_REFERER = ACP_HOST;

class patch {
	const PATCH_URL = "https://patch.icmsdev.com";	//自动更新服务器
	public static $version = '';
	public static $release = '';
	public static $zipName = '';
	public static $next = false;
	public static $test = false;

	public static function init($force = false) {
		$info = self::info($force);
		if ($info->app == iPHP_APP &&
			version_compare($info->version, iCMS_VERSION, '>=') &&
			$info->release > iCMS_RELEASE) {
			self::$version = $info->version;
			self::$release = $info->release;
			self::$zipName = 'iCMS.' . self::$version . '.patch.' . self::$release . '.zip';
			return array(self::$version, self::$release, $info->update, $info->changelog);
		}
	}
	public static function git($do,$commit_id=null,$type='array') {
        $commit_id===null && $commit_id = GIT_COMMIT;
        $last_commit_id = $_GET['last_commit_id'];

		$url = patch::PATCH_URL . '/git?do='.$do
        ."&VERSION=".iCMS_VERSION
        ."&RELEASE=".iCMS_RELEASE
		.'&commit_id=' .$commit_id
		.'&last_commit_id='.$last_commit_id
		.'&t=' . time();

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
	public static function version($force = false) {
        $url = self::PATCH_URL."/cms.version?callback=?"
        ."&VERSION=".iCMS_VERSION
        ."&RELEASE=".iCMS_RELEASE
        ."&GIT_COMMIT=".GIT_COMMIT;
        $json = iHttp::remote($url);
        if ($json) {
            echo $json;
        }
	}
	public static function info($force = false) {
		iFS::mkdir(PATCH_DIR);
		$tFilePath = PATCH_DIR . 'version.json'; //临时文件夹
		if (iFS::ex($tFilePath) && time() - iFS::mtime($tFilePath) < 3600 && !$force) {
			$FileData = iFS::read($tFilePath);
		} else {
			$url = self::PATCH_URL . '/version.' . iPHP_APP . '.' . iCMS_VERSION . '.patch.' . iCMS_RELEASE . '?t=' . time();
			$FileData = iHttp::remote($url);
			iFS::write($tFilePath, $FileData);
		}
		return json_decode($FileData); //版本列表
	}
	public static function download() {
		$zipFile = PATCH_DIR . self::$zipName; //临时文件
		$zipHttp = self::PATCH_URL . '/' . self::$zipName;
		$msg = '正在下载 [' . self::$release . '] 更新包 ' . $zipHttp . '<iCMS>下载完成....<iCMS>';
		if (iFS::ex($zipFile)) {
			return $msg;
		}
		$FileData = iHttp::remote($zipHttp);
		if ($FileData) {
			iFS::mkdir(PATCH_DIR);
			iFS::write($zipFile, $FileData); //下载更新包
			return $msg;
		}
	}
	public static function update() {
		@set_time_limit(0);
		// Unzip uses a lot of memory
		@ini_set('memory_limit', '256M');
		iPHP::import(iPHP_LIB . '/pclzip.class.php'); //加载zip操作类
		$zipFile = PATCH_DIR . '/' . self::$zipName; //临时文件
		$msg = '正在对 [' . self::$zipName . '] 更新包进行解压缩<iCMS>';
		$zip = new PclZip($zipFile);
		if (false == ($archive_files = $zip->extract(PCLZIP_OPT_EXTRACT_AS_STRING))) {
			exit("ZIP包错误");
		}

		if (0 == count($archive_files)) {
			exit("空的ZIP文件");
		}

		$msg .= '解压完成<iCMS>';
		$msg .= '开始测试目录权限<iCMS>';
		$bakDir = iPATH . self::$release . 'bak';
		$update = true;
		if (!iFS::checkdir(iPATH)) {
			$update = false;
			$msg .= iPATH . ' 目录无写权限<iCMS>';
		}

		//测试目录文件是否写
		foreach ($archive_files as $file) {
			$folder = $file['folder'] ? $file['filename'] : dirname($file['filename']);
			$dp = iPATH . $folder;
			if (!iFS::checkdir($dp) && iFS::ex($dp)) {
				$update = false;
				$msg .= $dp . ' 目录无写权限<iCMS>';
			}
			if (empty($file['folder'])) {
				$fp = iPATH . $file['filename'];
				if (file_exists($fp) && !@is_writable($fp)) {
					$update = false;
					$msg .= $fp . ' 文件无写权限<iCMS>';
				}
			}
		}
		if (!$update) {
			$msg .= '权限测试无法完成<iCMS>';
			$msg .= '请设置好上面提示的文件写权限<iCMS>';
			$msg .= '然后重新更新<iCMS>';
			self::$next = false;
			$msg = str_replace(iPATH,'iPHP://',$msg);
			return $msg;
		}
		//测试通过！
		self::$next = true;
		iFS::mkdir($bakDir);
		$msg .= '权限测试通过<iCMS>';
		$msg .= '备份目录创建完成<iCMS>';
		$msg .= '开始更新程序<iCMS>';

		foreach ($archive_files as $file) {
			$folder = $file['folder'] ? $file['filename'] : dirname($file['filename']);
			$dp = iPATH . $folder;
			if (!iFS::ex($dp)) {
				$msg .= '创建 [' . $dp . '] 文件夹<iCMS>';
				iFS::mkdir($dp);
			}
			if (empty($file['folder'])) {
				$fp = iPATH . $file['filename'];
				$bfp = $bakDir . '/' . $file['filename'];
				iFS::mkdir(dirname($bfp));
				if (iFS::ex($fp)) {
					$msg .= '备份 [' . $fp . '] 文件 到 [' . $bfp . ']<iCMS>';
					@rename($fp, $bfp); //备份旧文件
				}
				$msg .= '更新 [' . $fp . '] 文件<iCMS>';
				self::$test OR iFS::write($fp, $file['content']);
				$msg .= '[' . $fp . '] 更新完成!<iCMS>';
			}
		}
		$msg .= '清除临时文件!<iCMS>注:原文件备份在 [' . $bakDir . '] 目录<iCMS>如没有特殊用处请删除此目录!<iCMS>';
		iFS::rmdir(PATCH_DIR, true, 'version.txt');
        $msg = str_replace(iPATH,'iPHP://',$msg);
		return $msg;
	}
	public static function run() {
		foreach (glob(iPATH."patch.db.*.php") as $filename) {
			$d = str_replace(array(iPATH,'patch.db.','.php'), '', $filename);
			$time = strtotime($d.'00');
			$release = strtotime(iCMS_RELEASE);
			if($time>$release){
				if(defined('GIT_TIME')){
					if($time>GIT_TIME){
						$files[$d] = $filename;
					}
				}else{
					$files[$d] = $filename;
				}
			}
		}
		// var_dump($files);
		if($files){
			ksort($files);
			foreach ($files as $key => $file) {
				require_once $file;
				$patch_func = 'patch_db_'.$key;
				if(function_exists($patch_func)){
					$msg.= '执行[patch.db.'.$key.']升级程序<iCMS>';
					self::$test OR $msg .= $patch_func();
					$msg.= '升级顺利完成!<iCMS>删除升级程序!<iCMS>';
				}else{
					$msg = '[patch.db.'.$key.']升级出错!<iCMS>找不到升级程序<iCMS>';
				}
				iFS::del($updateFile);
			}
		}else {
			$msg = '升级顺利完成!';
		}
		// var_dump($msg);
		return $msg;
	}
}
