<?php
/**
 * @package iCMS
 * @copyright 2007-2017, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
defined('iPHP') OR exit('What are you doing?');

iPHP::app('tag.class', 'static');
function article_list($vars) {
	if ($vars['loop'] === "rel" && empty($vars['id'])) {
		return false;
	}
	$resource = array();
	$map_where = array();
	$status = '1';
	isset($vars['status']) && $status = (int) $vars['status'];
	$where_sql = "WHERE `status`='{$status}'";
	$vars['call'] == 'user' && $where_sql .= " AND `postype`='0'";
	$vars['call'] == 'admin' && $where_sql .= " AND `postype`='1'";
	$hidden = iCache::get('iCMS/category/hidden');
	$hidden && $where_sql .= iPHP::where($hidden, 'cid', 'not');
	$maxperpage = isset($vars['row']) ? (int) $vars['row'] : 10;
	$cache_time = isset($vars['time']) ? (int) $vars['time'] : -1;
	isset($vars['userid']) && $where_sql .= " AND `userid`='{$vars['userid']}'";
	isset($vars['weight']) && $where_sql .= " AND `weight`='{$vars['weight']}'";

	if (isset($vars['ucid']) && $vars['ucid'] != '') {
		$where_sql .= " AND `ucid`='{$vars['ucid']}'";
	}

	if (isset($vars['cid!'])) {
		$ncids = explode(',', $vars['cid!']);
		$vars['sub'] && $ncids += iPHP::app("category")->get_cids($ncids, true);
		$where_sql .= iPHP::where($ncids, 'cid', 'not');
	}
	if ($vars['cid'] && !isset($vars['cids'])) {
		$cid = explode(',', $vars['cid']);
		$vars['sub'] && $cid += iPHP::app("category")->get_cids($cid, true);
		$where_sql .= iPHP::where($cid, 'cid');
	}
	if (isset($vars['cids']) && !$vars['cid']) {
		$cids = explode(',', $vars['cids']);
		$vars['sub'] && $cids += iPHP::app("category")->get_cids($vars['cids'], true);

		if ($cids) {
			iCMS::core('Map');
			iMap::init('category', iCMS_APP_ARTICLE);
			$map_where += iMap::where($cids);
		}
	}
	if (isset($vars['pid']) && !isset($vars['pids'])) {
		$where_sql .= iPHP::where($vars['pid'], 'pid');
	}

	if (isset($vars['pids']) && !isset($vars['pid'])) {
		iCMS::core('Map');
		iMap::init('prop', iCMS_APP_ARTICLE);
		$map_where += iMap::where($vars['pids']);
	}

	if (isset($vars['tids'])) {
		iCMS::core('Map');
		iMap::init('tags', iCMS_APP_ARTICLE);
		$map_where += iMap::where($vars['tids']);
	}
	if (isset($vars['keywords'])) {
//最好使用 iCMS:article:search
		if (empty($vars['keywords'])) {
			return;
		}

		if (strpos($vars['keywords'], ',') === false) {
			$vars['keywords'] = str_replace(array('%', '_'), array('\%', '\_'), $vars['keywords']);
			$where_sql .= " AND CONCAT(title,keywords,description) like '%" . addslashes($vars['keywords']) . "%'";
		} else {
			$kws = explode(',', $vars['keywords']);
			foreach ($kws AS $kwv) {
				$keywords .= addslashes($kwv) . "|";
			}
			$keywords = substr($keywords, 0, -1);
			$where_sql .= " AND CONCAT(title,keywords,description) REGEXP '$keywords' ";
		}
	}

	$vars['id'] && $where_sql .= iPHP::where($vars['id'], 'id');
	$vars['id!'] && $where_sql .= iPHP::where($vars['id!'], 'id', 'not');
	$by = $vars['by'] == "ASC" ? "ASC" : "DESC";
	isset($vars['pic']) && $where_sql .= " AND `haspic`='1'";
	isset($vars['nopic']) && $where_sql .= " AND `haspic`='0'";

	switch ($vars['orderby']) {
        case "id":       $order_sql = " ORDER BY `id` $by"; break;
        case "hot":      $order_sql = " ORDER BY `hits` $by"; break;
        case "week":     $order_sql = " ORDER BY `hits_week` $by"; break;
        case "month":    $order_sql = " ORDER BY `hits_month` $by"; break;
        case "comment":  $order_sql = " ORDER BY `comments` $by"; break;
        case "pubdate":  $order_sql = " ORDER BY `pubdate` $by"; break;
        case "sort": $order_sql = " ORDER BY `sortnum` $by"; break;
        case "weight":   $order_sql = " ORDER BY `weight` $by"; break;
		default:$order_sql = " ORDER BY `id` $by";
	}
	isset($vars['startdate']) && $where_sql .= " AND `pubdate`>='" . strtotime($vars['startdate']) . "'";
	isset($vars['enddate']) && $where_sql .= " AND `pubdate`<='" . strtotime($vars['enddate']) . "'";
	isset($vars['where']) && $where_sql .= $vars['where'];

	if ($map_where) {
		$map_sql = iCMS::map_sql($map_where, 'join');
		//join
		//empty($vars['cid']) && $map_order_sql = " ORDER BY map.`iid` $by";
		$map_table = 'map';
		$vars['map_order_table'] && $map_table = $vars['map_order_table'];
		$map_order_sql = " ORDER BY {$map_table}.`iid` $by";
		//$map_order_sql = " ORDER BY `icms_article`.`id` $by";
		//
		$where_sql .= ' AND ' . $map_sql['where'];
		$where_sql = ",{$map_sql['from']} {$where_sql} AND `#iCMS@__article`.`id` = {$map_table}.`iid`";
		//derived
		// $where_sql = ",({$map_sql}) map {$where_sql} AND `id` = map.`iid`";
	}
	$offset = 0;
	$limit = "LIMIT {$maxperpage}";
	if ($vars['page']) {
		$total_type = $vars['total_cache'] ? $vars['total_cache'] : null;
		$total = iPHP::total('sql.md5', "SELECT count(*) FROM `#iCMS@__article` {$where_sql}", $total_type);
		$pagenav = isset($vars['pagenav']) ? $vars['pagenav'] : "pagenav";
		$pnstyle = isset($vars['pnstyle']) ? $vars['pnstyle'] : 0;
		$multi = iPHP::page(array('total_type' => $total_type, 'total' => $total, 'perpage' => $maxperpage, 'unit' => iUI::lang('iCMS:page:list'), 'nowindex' => $GLOBALS['page']));
		$offset = $multi->offset;
		$limit = "LIMIT {$offset},{$maxperpage}";
		iPHP::assign("article_list_total", $total);
	}
	//随机特别处理
	if ($vars['orderby'] == 'rand') {
		$ids_array = iCMS::get_rand_ids('#iCMS@__article', $where_sql, $maxperpage, 'id');
		if ($map_order_sql) {
			$map_order_sql = " ORDER BY `#iCMS@__article`.`id` $by";
		}
	}
	$hash = md5($where_sql . $order_sql . $limit);
	if ($offset) {
		if ($vars['cache']) {
			$map_cache_name = iPHP_DEVICE . '/article_page/' . $hash;
			$ids_array = iCache::get($map_cache_name);
		}
		if (empty($ids_array)) {
			$ids_order_sql = $map_order_sql ? $map_order_sql : $order_sql;
			$ids_array = iDB::all("SELECT `#iCMS@__article`.`id` FROM `#iCMS@__article` {$where_sql} {$ids_order_sql} {$limit}");
			$vars['cache'] && iCache::set($map_cache_name, $ids_array, $cache_time);
		}
	} else {
		if ($map_order_sql) {
			$order_sql = $map_order_sql;
		}
	}
	if ($ids_array) {
		$ids = iPHP::values($ids_array);
		$ids = $ids ? $ids : '0';
		$where_sql = "WHERE `#iCMS@__article`.`id` IN({$ids})";
		$limit = '';
	}
	if ($vars['cache']) {
		$cache_name = iPHP_DEVICE . '/article/' . $hash;
		$resource = iCache::get($cache_name);
	}
	// $func = 'article_array';
	// if($vars['func']=="user_home"){ //暂时只有一个选项
	//     $func = '__article_user_home_array';
	// }
	if (empty($resource)) {
		$resource = iDB::all("SELECT `#iCMS@__article`.* FROM `#iCMS@__article` {$where_sql} {$order_sql} {$limit}");
		$resource = article_array($vars, $resource);
		$vars['cache'] && iCache::set($cache_name, $resource, $cache_time);
	}
	//print_r($resource);
	return $resource;
}
function article_search($vars) {
	if (empty(iCMS::$config['sphinx']['host'])) {
		return array();
	}

	$resource = array();
	$hidden = iCache::get('iCMS/category/hidden');
	$hidden && $where_sql .= iPHP::where($hidden, 'cid', 'not');
	$SPH = iPHP::vendor('SPHINX',iCMS::$config['sphinx']['host']);
	$SPH->init();
	$SPH->SetArrayResult(true);
	if (isset($vars['weights'])) {
		//weights='title:100,tags:80,keywords:60,name:50'
		$wa = explode(',', $vars['weights']);
		foreach ($wa AS $wk => $wv) {
			$waa = explode(':', $wv);
			$FieldWeights[$waa[0]] = $waa[1];
		}
		$FieldWeights OR $FieldWeights = array("title" => 100, "tags" => 80, "name" => 60, "keywords" => 40);
		$SPH->SetFieldWeights($FieldWeights);
	}

	$page = (int) $_GET['page'];
	$maxperpage = isset($vars['row']) ? (int) $vars['row'] : 10;
	$start = ($page && isset($vars['page'])) ? ($page - 1) * $maxperpage : 0;
	$SPH->SetMatchMode(SPH_MATCH_EXTENDED);
	if ($vars['mode']) {
		$vars['mode'] == "SPH_MATCH_BOOLEAN" && $SPH->SetMatchMode(SPH_MATCH_BOOLEAN);
		$vars['mode'] == "SPH_MATCH_ANY" && $SPH->SetMatchMode(SPH_MATCH_ANY);
		$vars['mode'] == "SPH_MATCH_PHRASE" && $SPH->SetMatchMode(SPH_MATCH_PHRASE);
		$vars['mode'] == "SPH_MATCH_ALL" && $SPH->SetMatchMode(SPH_MATCH_ALL);
		$vars['mode'] == "SPH_MATCH_EXTENDED" && $SPH->SetMatchMode(SPH_MATCH_EXTENDED);
	}

	isset($vars['userid']) && $SPH->SetFilter('userid', array($vars['userid']));
	isset($vars['postype']) && $SPH->SetFilter('postype', array($vars['postype']));

	if (isset($vars['cid'])) {
		$cids = $vars['sub'] ? iPHP::app("category")->get_cids($vars['cid'], true) : (array) $vars['cid'];
		$cids OR $cids = (array) $vars['cid'];
		$cids = array_map("intval", $cids);
		$SPH->SetFilter('cid', $cids);
	}
	if (isset($vars['startdate'])) {
		$startime = strtotime($vars['startdate']);
		$enddate = empty($vars['enddate']) ? time() : strtotime($vars['enddate']);
		$SPH->SetFilterRange('pubdate', $startime, $enddate);
	}
	$SPH->SetLimits($start, $maxperpage, 10000);

	$orderby = '@id DESC, @weight DESC';
	$order_sql = ' order by id DESC';

	$vars['orderby'] && $orderby = $vars['orderby'];
	$vars['ordersql'] && $order_sql = ' order by ' . $vars['ordersql'];

	$vars['pic'] && $SPH->SetFilter('haspic', array(1));
	$vars['id!'] && $SPH->SetFilter('@id', array($vars['id!']), true);

	$SPH->setSortMode(SPH_SORT_EXTENDED, $orderby);

	$query = str_replace(',', '|', $vars['q']);
	$vars['acc'] && $query = '"' . $vars['q'] . '"';
	$vars['@'] && $query = '@(' . $vars['@'] . ') ' . $query;

	$res = $SPH->Query($query, iCMS::$config['sphinx']['index']);

	if (is_array($res["matches"])) {
		foreach ($res["matches"] as $docinfo) {
			$aid[] = $docinfo['id'];
		}
		$aids = implode(',', (array) $aid);
	}
	if (empty($aids)) {
		return;
	}

	$where_sql = " `id` in($aids)";
	$offset = 0;
	if ($vars['page']) {
		$total = $res['total'];
		iPHP::assign("article_search_total", $total);
		$pagenav = isset($vars['pagenav']) ? $vars['pagenav'] : "pagenav";
		$pnstyle = isset($vars['pnstyle']) ? $vars['pnstyle'] : 0;
		$multi = iPHP::page(array('total' => $total, 'perpage' => $maxperpage, 'unit' => iUI::lang('iCMS:page:list'), 'nowindex' => $GLOBALS['page']));
		$offset = $multi->offset;
	}
	$resource = iDB::all("SELECT * FROM `#iCMS@__article` WHERE {$where_sql} {$order_sql} LIMIT {$maxperpage}");
	$resource = article_array($vars, $resource);
	return $resource;
}
function article_data($vars) {
	$vars['aid'] OR iUI::warning('iCMS&#x3a;article&#x3a;data 标签出错! 缺少"aid"属性或"aid"值为空.');
	$data = iDB::row("SELECT body,subtitle FROM `#iCMS@__article_data` WHERE aid='" . (int) $vars['aid'] . "' LIMIT 1;", ARRAY_A);
	if ($data['body']) {
		$articleApp = iPHP::app("article");
		$data['body'] = $articleApp->ubb($data['body']);
		if (substr($data['body'], 0, 19) == '#--iCMS.Markdown--#') {
			$data['body'] = substr($data['body'], 19);
			$data['markdown'] = ture;
			// $data['body'] = iPHP::Markdown($data['body']);
		}
		$data['body'] = $articleApp->keywords($data['body']);
		$data['body'] = $articleApp->taoke($data['body']);
	}
	return $data;
}
function article_prev($vars) {
	$vars['order'] = 'p';
	return article_next($vars);
}
function article_next($vars) {
	// if($vars['param']){
	//     $vars+= $vars['param'];
	//     unset($vars['param']);
	// }
	empty($vars['order']) && $vars['order'] = 'n';

	$cache_time = isset($vars['time']) ? (int) $vars['time'] : -1;
	if (isset($vars['cid'])) {
		$sql = " AND `cid`='{$vars['cid']}' ";
	}
	if ($vars['order'] == 'p') {
		$sql .= " AND `id` < '{$vars['id']}' ORDER BY id DESC LIMIT 1";
	} else if ($vars['order'] == 'n') {
		$sql .= " AND `id` > '{$vars['id']}' ORDER BY id ASC LIMIT 1";
	}
	$hash = md5($sql);
	if ($vars['cache']) {
		$cache = iPHP_DEVICE . '/article/' . $hash;
		$array = iCache::get($cache);
	}
	if (empty($array)) {
		$rs = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `status`='1' {$sql}");
		if ($rs) {
			$category = iCache::get('iCMS/category/' . $rs->cid);
			$array = array(
				'title' => $rs->title,
				'pic' => get_pic($rs->pic),
				'url' => iURL::get('article', array((array) $rs, $category))->href,
			);
		}
		iCache::set($cache, $array, $cache_time);
	}
	return $array;
}
function article_array($vars, $variable) {
	$resource = array();
	if ($variable) {
		$articleApp = iPHP::app("article");
		$vars['category_lite'] = true;
        if($vars['data']||$vars['pics']){
            $aidArray = iPHP::values($variable,'id','array',null);
            $aidArray && $article_data = (array) $articleApp->data($aidArray);
            unset($aidArray);
        }
        if($vars['tags']){
            $tagArray = iPHP::values($variable,'tags','array',null,'id');
            if($tagArray){
				$tagApp    = iPHP::app("tag.app");
				$tags_data = (array)$tagApp->multi_tag($tagArray);
            }
            unset($tagArray);
        }
		foreach ($variable as $key => $value) {
			$value = $articleApp->value($value, false, $vars);
			if ($value === false) {
				continue;
			}
            if(($vars['data']||$vars['pics']) && $article_data){
                $value['data']  = (array)$article_data[$value['id']];
                if($vars['pics']){
					$value['pics'] = $articleApp->body_pics($value['data']['body']);
					if(!$value['data']){
						unset($value['data']);
					}
                }
            }

            if($vars['tags'] && $tags_data){
                $value+= (array)$tags_data[$value['id']];
            }

			if ($vars['page']) {
				$value['page'] = $GLOBALS['page'] ? $GLOBALS['page'] : 1;
				$value['total'] = $total;
			}
			if ($vars['archive'] == "date") {
				$_date = archive_date($value['postime']);
				unset($resource[$key]);
				$resource[$_date][$key] = $value;
			} else {
				$resource[$key] = $value;
			}

		}
	}
	return $resource;
}
