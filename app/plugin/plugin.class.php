<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class plugin{
    public static function library($file) {
        $path = iPHP_APP_DIR . '/'.__CLASS__.'/library/'.$file;
        iPHP::import($path);
    }
    public static function import($file) {
        $path = iPHP_APP_DIR . '/'.__CLASS__.'/'.$file;
        iPHP::import($path);
    }
}

