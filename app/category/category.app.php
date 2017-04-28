<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class categoryApp{
	public $methods	= array('iCMS','category');
    public function __construct($appid = iCMS_APP_ARTICLE) {
    	// $this->appid = iCMS_APP_ARTICLE;
    	// $appid && $this->appid = $appid;
    	// $_GET['appid'] && $this->appid	= (int)$_GET['appid'];
    }
    public function do_iCMS($tpl = 'index') {
        $cid = (int)$_GET['cid'];
        $dir = iSecurity::escapeStr($_GET['dir']);
		if(empty($cid) && $dir){
			$cid = categoryApp::get_cahce('dir2cid',$dir);
            $cid OR iPHP::error_404('找不到该栏目<b>dir:'.$dir.'</b> 请更新栏目缓存或者确认栏目是否存在', 20002);
		}
    	return $this->category($cid,$tpl);
    }

    public function API_iCMS(){
        return $this->do_iCMS();
    }
    /**
     * [hooked 钩子]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function hooked($data){
        return iPHP::hook('category',$data,iCMS::$config['hooks']['category']);
    }
    public static function category($cid,$tpl='index') {
        $category = categoryApp::get_cahce_cid($cid);
        if(empty($category) && $tpl){
            iPHP::error_404('找不到该栏目<b>cid:'. $cid.'</b> 请更新栏目缓存或者确认栏目是否存在', 20001);
        }
        if($category['status']==0) return false;
        $iurl = $category['iurl'];
        if($tpl){
            if(iView::$gateway=="html"){
                if(strpos($category['rule']['index'], '{PHP}') !== false||
                    $category['outurl']||!$category['mode']) return false;
            }
            $category['outurl'] && iPHP::redirect($category['outurl']);
            $category['mode']=='1' && iCMS::redirect_html($iurl['path'],$iurl['href']);
        }

        $category['param'] = array(
            "sappid" => $category['sappid'],
            "appid"  => $category['appid'],
            "iid"    => $category['cid'],
            "cid"    => $category['rootid'],
            "suid"   => $category['userid'],
            "title"  => $category['name'],
            "url"    => $category['url']
        );

        $category = self::hooked($category);

        if($tpl) {
            $category['mode'] && iURL::page_url($iurl);
            iView::assign('app', apps::get_app_lite($category['appid']));
            iView::assign('category',$category);
            if(isset($_GET['tpl'])){
                $tpl = iSecurity::escapeStr($_GET['tpl']);
                if(strpos($tpl, '..') !== false){
                    exit('what the fuck!!');
                }else{
                    $tpl = $tpl.'.htm';
                }
            }
            if(strpos($tpl, '.htm')!==false){
            	return iView::render($tpl,'category');
            }
            $GLOBALS['page']>1 && $tpl='list';

            if($category['template']){
                $view = iView::render($category['template'][$tpl],'category.'.$tpl);
            }else{
                iPHP::error_404('找不到该栏目的模板配置,请设置栏目'.$tpl.'模板', 20002);
            }
            if($view) return array($view,$category);
        }else{
        	return $category;
        }
    }
    public static function get_lite($category){
        $keyArray = array('sortnum','password','mode','domain','config','addtime');
        foreach ($keyArray as $i => $key) {
             unset($category[$key]);
        }
        iDevice::router($category);
        iDevice::router($category['iurl']);
        iDevice::router($category['navArray'],true);
        return $category;
    }
    public static function get_cids($cid = "0",$all=true,$root_array=null) {
        $root_array OR $root_array = categoryApp::get_cahce("rootid");
        $cids = array();
        is_array($cid) OR $cid = explode(',', $cid);
        foreach($cid AS $_id) {
            $cids+=(array)$root_array[$_id];
        }
        if($all){
            foreach((array)$cids AS $_cid) {
                $root_array[$_cid] && $cids+= self::get_cids($_cid,$all,$root_array);
            }
        }
        $cids = array_unique($cids);
        $cids = array_filter($cids);

        return $cids;
    }
    public static function get_cahce_cid($cid="0") {
        return iCache::get('category/C'.$cid);;
    }
    public static function get_cahce($key=null,$value=null){
        if($value){
            return iCache::get('category/'.$key,$value);
        }
        return iCache::get('category/'.$key);
    }
    //绑定域名 iURL 回调函数
    public static function domain($i,$cid,$base_url) {
        $domain_array = (array)iCMS::$config['category']['domain'];
        if($domain_array){
            $domain_array = array_flip($domain_array);
            $domain = $domain_array[$cid];
            if(empty($domain)){
                $rootid_array = categoryApp::get_cahce("domain_rootid");
                if($rootid_array){
                    $rootid = $rootid_array[$cid];
                    $rootid && $domain = $domain_array[$rootid];
                }
            }
        }
        if($domain){
            if(iFS::checkHttp($domain)){
                $i->href    = str_replace($base_url, $domain, $i->href);
                $i->hdir    = str_replace($base_url, $domain, $i->hdir);
                $i->pageurl = str_replace($base_url, $domain, $i->pageurl);
            }
        }
        return $i;
    }
}
