<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class categoryFunc{
	public static function category_array($vars){
		$cid = (int)$vars['cid'];
		return categoryApp::category($cid,false);
	}
	public static function category_list($vars){
		$row        = isset($vars['row'])?(int)$vars['row']:"100";
		$cache_time = isset($vars['time'])?(int)$vars['time']:"-1";
		$status     = isset($vars['status'])?(int)$vars['status']:"1";
		$maxperpage	= isset($vars['row'])?(int)$vars['row']:"10";
		$where_sql  =" WHERE `status`='$status'";
		$resource   = array();

		// isset($vars['appid']) OR $vars['appid'] = iCMS_APP_ARTICLE;

		isset($vars['appid']) && $where_sql.= iSQL::in($vars['appid'],'appid');
		isset($vars['mode']) && $where_sql.= iSQL::in($vars['mode'],'mode');
		isset($vars['cid']) && !isset($vars['stype']) && $where_sql.= iSQL::in($vars['cid'],'cid');
		isset($vars['cid!']) && $where_sql.= iSQL::in($vars['cid!'],'cid','not');
		switch ($vars['stype']) {
			case "top":
				$vars['cid'] && $where_sql.= iSQL::in($vars['cid'],'cid');
				$where_sql.=" AND rootid='0'";
			break;
			case "sub":
				$vars['cid'] && $where_sql.= iSQL::in($vars['cid'],'rootid');
			break;
			case "suball":
				$cids = categoryApp::get_cids($vars['cid'],false);
				$where_sql.= iSQL::in($cids,'cid');
			break;
			case "self":
				$parentid = categoryApp::get_cahce('parent',$vars['cid']);
				$where_sql.=" AND `rootid`='$parentid'";
			break;
		}
		if(isset($vars['pids'])){
			iMap::init('prop',iCMS_APP_CATEGORY,'pid');
			$where_sql.= iMap::exists($vars['pids'],'`#iCMS@__category`.cid'); //主表小 map表大
	//		$map_where=iMap::where($vars['pids']); //主表大 map表大
	//		$map_ids    = iMap::ids($vars['pid']);
	//		$map_sql    = iMap::sql($vars['pid']); //map 表小的用 in
	//		$where_sql.=" AND `pid` = '{$vars['pid']}'";
			//if(empty($map_ids)) return $resource;
			//$where_sql.=" AND `cid` IN ($map_ids)";
			//$where_sql.=" AND `cid` IN ($map_sql)";
		}
		$by = $vars['by']=='DESC'?"DESC":"ASC";

		switch ($vars['orderby']) {
			case "hot":		$order_sql=" ORDER BY `count` $by";		break;
			case "new":		$order_sql=" ORDER BY `cid` $by";			break;
			default:		$order_sql=" ORDER BY `sortnum` $by";
		}

		$offset	= 0;
		$limit  = "LIMIT {$maxperpage}";
		if($vars['page']){
			$total	= iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__category` {$where_sql} ",null,iCMS::$config['cache']['page_total']);
			$multi  = iUI::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iUI::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
			$offset = $multi->offset;
			$limit  = "LIMIT {$offset},{$maxperpage}";
			iView::assign("category_list_total",$total);
		}

	    if($vars['orderby']=='rand'){
	        $ids_array = iSQL::get_rand_ids('#iCMS@__category',$where_sql,$maxperpage,'cid');
	    }

		$hash = md5($where_sql.$order_sql.$limit);

		if($vars['cache']){
			$cache_name = iPHP_DEVICE.'/category/'.$hash;
	        $vars['page'] && $cache_name.= "/".(int)$GLOBALS['page'];
			$resource = iCache::get($cache_name);
	        if($resource){
	            return $resource;
	        }
		}

		$resource = iDB::all("SELECT `cid` FROM `#iCMS@__category` {$where_sql} {$order_sql} {$limit}");
		if($resource){
			foreach ($resource as $key => $value) {
				$cate = categoryApp::get_cahce_cid($value['cid']);
				$cate && $resource[$key] = categoryApp::get_lite($cate);
			}
		}
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);

		return $resource;
	}
}
