<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class plugin_QRcode {
    /**
     * [插件:生成二维码]
     * @param [type] $content  [参数]
     */
    public static function HOOK($content) {
        plugin::library('phpqrcode.php');
		$expires = 86400;
		header("Cache-Control: maxage=" . $expires);
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
		header('Content-type: image/png');

		$filepath = false;
		if (isset($_GET['cache'])) {
			$name = substr(md5($content), 8, 16);
			$filepath = iPHP_APP_CACHE . '/QRcode.' . $name . '.png';
		}
		is_file($filepath) OR QRcode::png($content, $filepath, 'L', 4, 2);
		if ($filepath) {
			$content = readfile($filepath);
		}

        return $content;
    }
}
