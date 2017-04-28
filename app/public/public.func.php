<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class publicFunc{
	public static function public_ui($vars=null){
		iView::assign("public",$vars);
		iView::display("iCMS://public.ui.htm");
	}
	public static function public_seccode($vars=null){
		echo publicApp::seccode();
	}
	public static function public_crontab(){
		echo '<img src="'.iCMS_API.'?app=public&do=crontab&'.$_SERVER['REQUEST_TIME'].'" id="iCMS_public_crontab"/>';
	}
	public static function public_qrcode($vars=null){
		$data  = $vars['data'];
		$query = array('app'=>'public','do'=>'qrcode','url'=>$data);
		isset($vars['cache']) && $query['cache'] = true;
		$url = iURL::router('api');
		echo buildurl($url,$query);
	}
}
