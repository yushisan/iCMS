<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class filesApp {
    public $methods = array('iCMS','download');

    public function do_iCMS(){}
    public function API_iCMS(){}

    public function do_download(){
        $filename = iSecurity::escapeStr($_GET['file']);
        files::config(iFS::$config['table']);
        $data = files::get('filename',$filename);
        $url  = iFS::fp($data->filepath, '+http');
        $path = iFS::fp($data->filepath, '+iPATH');
        self::attachment($path,$data->ofilename);
    }
    public static function attachment($path,$filename=null){
        $path_parts = pathinfo($path);
        $filename===null && $filename = $path_parts['basename'];
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Transfer-Encoding: binary");
        header('Content-Type: '.filesApp::mime_types($path_parts['extension']));
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Length: ' . filesize($path));
        readfile($path);
        flush();
        ob_flush();
    }
    public function API_download(){
        $this->do_download();
    }
    public static function get_url($value,$type='download'){
        $url = iCMS_API.'?app=files&do='.$type.'&file='.$value.'&t='.$_SERVER['REQUEST_TIME'];
        return $url;
    }
    public static function get_pic($src,$size=0,$thumb=0){
        if(empty($src)) return array();

        if(stripos($src, '://')!== false){
            return array(
                'src' => $src,
                'url' => $src,
                'width' => 0,
                'height' => 0,
            );
        }

        $data = array(
            'src' => $src,
            'url' => iFS::fp($src,'+http'),
        );
        if($size){
            $data['width']  = $size['w'];
            $data['height'] = $size['h'];
        }
        if($size && $thumb){
            $data+= bitscale(array(
                "tw" => (int)$thumb['width'],
                "th" => (int)$thumb['height'],
                "w" => (int)$size['w'],
                "h" => (int)$size['h'],
            ));
        }
        return $data;
    }
    public static function get_twh($width=null,$height=null){
        $ret    = array();
        $width  ===null OR $ret['width'] = $width;
        $height ===null OR $ret['height'] = $height;
        return $ret;
    }
    /**
     * Get the MIME type for a file extension.
     * @param string $ext File extension
     * @access public
     * @return string MIME type of file.
     * @static
     */
    public static function mime_types($ext = ''){
        $mimes = array(
            'xl'    => 'application/excel',
            'js'    => 'application/javascript',
            'hqx'   => 'application/mac-binhex40',
            'cpt'   => 'application/mac-compactpro',
            'bin'   => 'application/macbinary',
            'doc'   => 'application/msword',
            'word'  => 'application/msword',
            'class' => 'application/octet-stream',
            'dll'   => 'application/octet-stream',
            'dms'   => 'application/octet-stream',
            'exe'   => 'application/octet-stream',
            'lha'   => 'application/octet-stream',
            'lzh'   => 'application/octet-stream',
            'psd'   => 'application/octet-stream',
            'sea'   => 'application/octet-stream',
            'so'    => 'application/octet-stream',
            'oda'   => 'application/oda',
            'pdf'   => 'application/pdf',
            'ai'    => 'application/postscript',
            'eps'   => 'application/postscript',
            'ps'    => 'application/postscript',
            'smi'   => 'application/smil',
            'smil'  => 'application/smil',
            'mif'   => 'application/vnd.mif',
            'xls'   => 'application/vnd.ms-excel',
            'ppt'   => 'application/vnd.ms-powerpoint',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc'  => 'application/vnd.wap.wmlc',
            'dcr'   => 'application/x-director',
            'dir'   => 'application/x-director',
            'dxr'   => 'application/x-director',
            'dvi'   => 'application/x-dvi',
            'gtar'  => 'application/x-gtar',
            'php3'  => 'application/x-httpd-php',
            'php4'  => 'application/x-httpd-php',
            'php'   => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps'  => 'application/x-httpd-php-source',
            'swf'   => 'application/x-shockwave-flash',
            'sit'   => 'application/x-stuffit',
            'tar'   => 'application/x-tar',
            'tgz'   => 'application/x-tar',
            'xht'   => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'zip'   => 'application/zip',
            'mid'   => 'audio/midi',
            'midi'  => 'audio/midi',
            'mp2'   => 'audio/mpeg',
            'mp3'   => 'audio/mpeg',
            'mpga'  => 'audio/mpeg',
            'aif'   => 'audio/x-aiff',
            'aifc'  => 'audio/x-aiff',
            'aiff'  => 'audio/x-aiff',
            'ram'   => 'audio/x-pn-realaudio',
            'rm'    => 'audio/x-pn-realaudio',
            'rpm'   => 'audio/x-pn-realaudio-plugin',
            'ra'    => 'audio/x-realaudio',
            'wav'   => 'audio/x-wav',
            'bmp'   => 'image/bmp',
            'gif'   => 'image/gif',
            'jpeg'  => 'image/jpeg',
            'jpe'   => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'png'   => 'image/png',
            'tiff'  => 'image/tiff',
            'tif'   => 'image/tiff',
            'eml'   => 'message/rfc822',
            'css'   => 'text/css',
            'html'  => 'text/html',
            'htm'   => 'text/html',
            'shtml' => 'text/html',
            'log'   => 'text/plain',
            'text'  => 'text/plain',
            'txt'   => 'text/plain',
            'rtx'   => 'text/richtext',
            'rtf'   => 'text/rtf',
            'vcf'   => 'text/vcard',
            'vcard' => 'text/vcard',
            'xml'   => 'text/xml',
            'xsl'   => 'text/xml',
            'mpeg'  => 'video/mpeg',
            'mpe'   => 'video/mpeg',
            'mpg'   => 'video/mpeg',
            'mov'   => 'video/quicktime',
            'qt'    => 'video/quicktime',
            'rv'    => 'video/vnd.rn-realvideo',
            'avi'   => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie'
        );
        return (array_key_exists(strtolower($ext), $mimes) ? $mimes[strtolower($ext)]: 'application/octet-stream');
    }
}
