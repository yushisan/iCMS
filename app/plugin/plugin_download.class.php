<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class plugin_download{
    /**
     * [插件:正文文件下载]
     * @param [type] $content  [参数]
     * @param [type] $resource [返回替换过的内容]
     */
    public static function HOOK($content) {
        preg_match_all('#<a\s*class="attachment".*ext=".*?"\s*fid=".*?"\s*path="(.*?)"\s*href="(.*?)"\s*title=".*?">.*?</a>#is',
            $content, $variable);
        foreach ((array)$variable[1] as $key => $path) {
           $urlArray[$key]= filesApp::get_url(basename($path));
        }
        if($urlArray){
            $content = str_replace($variable[2], $urlArray, $content);
        }
        return $content;
    }
}
