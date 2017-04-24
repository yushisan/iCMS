<?php
/**
 * @package iCMS
 * @copyright 2007-2017, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class plugin{
    public static $DIR = iPHP_APP_DIR . '/'.__CLASS__;
    public static $LIB = iPHP_APP_DIR . '/'.__CLASS__.'/library';

    public static function library($file) {
        $path = self::$LIB.'/'.$file;
        iPHP::import($path);
    }
    public static function import($file) {
        $path = self::$DIR.'/'.$file;
        iPHP::import($path);
    }

}
