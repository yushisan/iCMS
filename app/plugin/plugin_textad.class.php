<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class plugin_textad {
    public static $pregimg = "/<img.*?src\s*=[\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is";
    /**
     * [插件:正文插入广告]
     * @param [type] $content [参数]
     */
    public static function HOOK($content,&$resource=null){
        $pieces    = 1000;
        $html      = str_replace('</p>', "</p>\n", $content);
        $htmlArray = explode("\n", $html);
        $result  = array();
        //计算长度
        preg_match_all(self::$pregimg,$content,$img_array);
        $len = strlen($content)+(count($img_array[1])*300);

        if($len<($pieces*1.5)){
            return $content;
        }
        $i = 0;
        foreach ($htmlArray as $key => $phtm) {
            $pLen += strlen($phtm);
            if(strpos($phtm, '<img')!==false){
                $pLen +=100;
            }
            $llen = $len-$pLen;
            // if($_GET['debug']){
            //     var_dump(substr($phtm, 0,30));
            //     var_dump($pLen,$llen,floor($llen/$pieces),'=========');
            // }
            if($pLen>$pieces && floor($llen/$pieces)>=1){
                // if($_GET['debug']){
                //     var_dump('---------------------------');
                // }
                $ad = '<script>if(typeof showBodyUI==="function")showBodyUI("body.'.$i.'");</script>';
                $result[$i].= $phtm.$ad;
                $pLen = 0;
                $len = $llen;
                $i++;
            }else{
                $result[$i].= $phtm;
            }
        }
        unset($html,$htmlArray);
        return implode('', (array)$result);
    }
}
