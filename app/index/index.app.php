<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class indexApp {
	public $methods	= array('iCMS');
    public function __construct() {}
    public function do_iCMS($a = null) {
        if(iView::$gateway!="html"){
            $domain = $this->domain();
            if($domain) return;
        }
        return $this->index($a);
    }
    public function API_iCMS(){
        return $this->do_iCMS();
    }
    private function index($a = null){
        $index_name = $a[1]?:iCMS::$config['template']['index']['name'];
        $index_name OR $index_name = 'index';
        $index_tpl  = $a[0]?:iPHP_INDEX_TPL;
        $iurl = iURL::get('index',array('rule'=>$index_name.iCMS::$config['router']['ext']));
        if(iCMS::$config['template']['index']['mode'] && iPHP_DEVICE=="desktop"){
            iCMS::redirect_html($iurl->path,$iurl->href);
        }
        if(iView::$gateway=="html" || iCMS::$config['template']['index']['rewrite']){
            iURL::page_url($iurl);
        }
        $view = iView::render($index_tpl);
        if($view) return array($view,$iurl);
    }
    public function domain(){
        $domain = iCMS::$config['category']['domain'];
        if($domain){
            $host = iSecurity::escapeStr($_GET['host']);
            empty($host) && $host = $_SERVER['HTTP_HOST'];
            $cid  = (int)$domain[$host];
            if($cid){
                categoryApp::category($cid);
                return true;
            }
        }
        return false;
    }
}
