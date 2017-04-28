<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class articleApp {
	public $methods = array('iCMS', 'article', 'clink', 'hits','vote', 'good', 'bad', 'like_comment', 'comment');
	public static $pregimg = "/<img.*?src\s*=[\"|'|\s]*((http|https):\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is";
	public static $config  = null;
	public function __construct() {
		self::$config = iCMS::$config['article'];
	}

	public function do_iCMS($a = null) {
		$v = (int) $_GET['id'];
		$p = isset($_GET['p']) ? (int) $_GET['p'] : 1;
		$f = 'id';
		if(isset($_GET['clink'])){
			$v = iSecurity::escapeStr($_GET['clink']);
			$f = 'clink';
		}
		return $this->article($v,$p,$f);
	}

	public function do_clink($a = null) {
		return $this->do_iCMS($a);
	}
	public function API_iCMS() {
		return $this->do_iCMS();
	}
	public function API_clink() {
		return $this->do_clink();
	}
	public function API_hits($id = null) {
		$id === null && $id = (int) $_GET['id'];
		if ($id) {
			$sql = iSQL::update_hits();
			iDB::query("UPDATE `#iCMS@__article` SET {$sql} WHERE `id` ='$id'");
		}
	}
	public function ACTION_vote() {
		$type = $_POST['type'];
		$this->__vote($type);
		// $type=='up' && $this->vote('good');
		// $type=='down' && $this->vote('bad');
	}
	public function API_comment() {
		$appid = (int) $_GET['appid'];
		$cid = (int) $_GET['cid'];
		$iid = (int) $_GET['iid'];
		$this->article($iid,1,'id','{iTPL}/article.comment.htm');
	}
	private function __vote($type) {
		// user::get_cookie() OR iUI::code(0,'iCMS:!login',0,'json');

		$aid = (int) $_POST['iid'];
		$aid OR iUI::code(0, 'iCMS:article:empty_id', 0, 'json');

		$ackey = 'article_' . $type . '_' . $aid;
		$vote = iPHP::get_cookie($ackey);
		$vote && iUI::code(0, 'iCMS:article:!' . $type, 0, 'json');

		if ($type == 'good') {
			$sql = '`good`=good+1';
		} else {
			$sql = '`bad`=bad+1';
		}
		iDB::query("UPDATE `#iCMS@__article` SET {$sql} WHERE `id` ='{$aid}' limit 1");
		iPHP::set_cookie($ackey, time(), 86400);
		iUI::code(1, 'iCMS:article:' . $type, 0, 'json');
	}
	/**
	 * [hooked 钩子]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
    public static function hooked($data){
        return iPHP::hook('article',$data,iCMS::$config['hooks']['article']);
    }
	public function article($fvar,$page = 1,$field='id', $tpl = true) {
		$article = iDB::row("
			SELECT * FROM `#iCMS@__article`
			WHERE `".$field."`='".$fvar. "'
			AND `status` ='1' LIMIT 1;",
		ARRAY_A);

		$article OR iPHP::error_404('找不到相关文章<b>'.$field.':' . $fvar . '</b>', 10001);
		$id = $article['id'];

		if ($article['url']) {
			if (iView::$gateway == "html") {
				return false;
			} else {
				$this->API_hits($id);
				iPHP::redirect($article['url']);
			}
		}

		if ($article['chapter']) {
			$all = iDB::all("SELECT id,subtitle FROM `#iCMS@__article_data` WHERE aid='" . (int) $id . "';", ARRAY_A);
			foreach ($all as $akey => $value) {
				$article_data[] = $value;
			}
			unset($all);
			ksort($article_data);
		} else {
			$article_data = iDB::row("SELECT body,subtitle FROM `#iCMS@__article_data` WHERE aid='" . (int) $id . "' LIMIT 1;", ARRAY_A);
		}

		$vars = array(
			'tag'  => true,
			'user' => true,
		);
		$article = $this->value($article, $article_data, $vars, $page, $tpl);
		unset($article_data);
		if ($article === false) {
			return false;
		}

		$article = $this->hooked($article);

		if ($tpl) {
			$article_tpl = empty($article['tpl']) ? $article['category']['template']['article'] : $article['tpl'];
			strstr($tpl, '.htm') && $article_tpl = $tpl;
			iView::assign('category', $article['category']);unset($article['category']);
			iView::assign('article', $article);
			$view = iView::render($article_tpl, 'article');
			if($view) return array($view,$article);

		} else {
			return $article;
		}
	}
	public static function value($article, $art_data = "", $vars = array(), $page = 1, $tpl = false) {

		$article['appid'] = iCMS_APP_ARTICLE;

		$category = categoryApp::category($article['cid'], false);

		if ($tpl) {
			$category OR iPHP::error_404('找不到该文章的栏目缓存<b>cid:' . $article['cid'] . '</b> 请更新栏目缓存或者确认栏目是否存在', 10002);
		} else {
			if (empty($category)) {
				return false;
			}

		}

		if ($category['status'] == 0) {
			return false;
		}

		if (iView::$gateway == "html" && $tpl && (strstr($category['rule']['article'], '{PHP}') || $category['outurl'] || $category['mode'] == "0")) {
			return false;
		}

		$article['iurl'] = (array)iURL::get('article', array($article, $category), $page);
		$article['url'] = $article['iurl']['href'];
		$article['link'] = "<a href='{$article['url']}'>{$article['title']}</a>";

		($tpl && $category['mode'] == '1') && iCMS::redirect_html($article['iurl']['path'], $article['iurl']['href']);

		$article['category'] = categoryApp::get_lite($category);

		if ($art_data) {
			$pkey = intval($page - 1);
			if ($article['chapter']) {
				$chapterArray = $art_data;
				$count = count($chapterArray);
				$adid = $chapterArray[$pkey]['id'];
				$art_data = iDB::row("SELECT body,subtitle FROM `#iCMS@__article_data` WHERE aid='" . (int) $article['id'] . "' AND id='" . (int) $adid . "' LIMIT 1;", ARRAY_A);
			}


			$article['pics'] = self::body_pics($art_data['body'],$pic_array);

			if ($article['chapter']) {
				$article['body'] = $art_data['body'];
			} else {
				$body = explode('#--iCMS.PageBreak--#', $art_data['body']);
				$count = count($body);
				$article['body'] = $body[$pkey];
				unset($body);
			}

			$article['subtitle'] = $art_data['subtitle'];
			unset($art_data);
			$total = $count + intval(self::$config['pageno_incr']);
			$article['page'] = self::page($article,$page,$total,$count,$category['mode']);
			$article['PAGES'] = $article['page']['PAGES'];unset($article['page']['PAGES']);
			is_array($article['page']['next'])&& $next_url = $article['page']['next']['url'];
			$pic_array[0] && $article['body'] = self::body_pics_page($pic_array,$article,$page,$total,$next_url);
		}

		if ($vars['tag']) {
			$article['tags_fname'] = $category['name'];
			if ($article['tags']) {
				$multi_tag = tagApp::multi_tag(array($article['id']=>$article['tags']));
				$article+=(array)$multi_tag[$article['id']];
			}
			if(is_array($article['tags_array'])){
				$tags_fname            = array_slice ($article['tags_array'],0,1);
				$article['tags_fname'] = $tags_fname[0]['name'];
			}
			unset($multi_tag, $tags_fname);
		}

		if ($vars['user']) {
			if ($article['postype']) {
				$article['user'] = user::empty_info($article['userid'], '#' . $article['editor']);
			} else {
				$article['user'] = user::info($article['userid'], $article['author']);
			}
		}
		$article['source'] = self::text2link($article['source']);
		$article['author'] = self::text2link($article['author']);

		$article['hits'] = array(
			'script' => iCMS_API . '?app=article&do=hits&cid=' . $article['cid'] . '&id=' . $article['id'],
			'count' => $article['hits'],
			'today' => $article['hits_today'],
			'yday' => $article['hits_yday'],
			'week' => $article['hits_week'],
			'month' => $article['hits_month'],
		);
		$article['comment'] = array(
			'url' => iCMS_API . "?app=article&do=comment&appid={$article['appid']}&iid={$article['id']}&cid={$article['cid']}",
			'count' => $article['comments'],
		);

		if ($article['picdata']) {
			$picdata = unserialize($article['picdata']);
		}
		unset($article['picdata']);

		$article['pic']  = filesApp::get_pic($article['pic'], $picdata['b'], filesApp::get_twh($vars['btw'], $vars['bth']));
		$article['mpic'] = filesApp::get_pic($article['mpic'], $picdata['m'], filesApp::get_twh($vars['mtw'], $vars['mth']));
		$article['spic'] = filesApp::get_pic($article['spic'], $picdata['s'], filesApp::get_twh($vars['stw'], $vars['sth']));
		$article['param'] = array(
			"appid" => $article['appid'],
			"iid"   => $article['id'],
			"cid"   => $article['cid'],
			"suid"  => $article['userid'],
			"title" => $article['title'],
			"url"   => $article['url'],
		);
		return $article;
	}
	public function data($aids=0){
		if(empty($aids)) return array();

		list($aids,$is_multi)  = iSQL::multi_var($aids);
		$sql  = iSQL::in($aids,'aid',false,true);
		$data = array();
		$rs   = iDB::all("SELECT * FROM `#iCMS@__article_data` where {$sql}",OBJECT);
		if($rs){
			if($is_multi){
				$_count = count($rs);
		        for ($i=0; $i < $_count; $i++) {
		        	$data[$rs[$i]->aid]= $rs[$i];
		        }
			}else{
				$data = $rs[0];
			}
		}
        if(empty($data)){
            return;
        }
	   	return $data;
	}
	public static function page($article,$page,$total,$count,$mode=null){
		$pageArray = array();
		$pageurl = $article['iurl']['pageurl'];
		if ($total > 1) {
			$_GLOBALS_iPage = $GLOBALS['iPage'];
			$mode && iURL::page_url($article['iurl']);
			$pageconf = array(
				'page_name' => 'p',
				'url' => $pageurl,
				'total' => $total,
				'perpage' => 1,
				'nowindex' => (int) $_GET['p'],
				'lang' => iUI::lang(iPHP_APP . ':page'),
			);
			if ($article['chapter']) {
				foreach ((array) $chapterArray as $key => $value) {
					$pageconf['titles'][$key + 1] = $value['subtitle'];
				}
			}
			$iPages = new iPages($pageconf);
			unset($GLOBALS['iPage']);
			$GLOBALS['iPage'] = $_GLOBALS_iPage;
			unset($_GLOBALS_iPage);

			$pageArray['list']  = $iPages->list_page();
			$pageArray['index'] = $iPages->first_page('array');
			$pageArray['prev']  = $iPages->prev_page('array');
			$pageArray['next']  = $iPages->next_page('array');
			$pageArray['endof'] = $iPages->last_page('array');
			$pagenav = $iPages->show(0);
			$pagetext = $iPages->show(10);
		}
		$article_page = array(
			'pn'      => $page,
			'total'   => $total, //总页数
			'count'   => $count, //实际页数
			'current' => $page,
			'nav'     => $pagenav,
			'pageurl' => $pageurl,
			'text'    => $pagetext,
			'PAGES'   => $iPages,
			'args'    => iSecurity::escapeStr($_GET['pageargs']),
			'first'   => ($page == "1" ? true : false),
			'last'    => ($page == $count ? true : false), //实际最后一页
			'end'     => ($page == $total ? true : false)
		) + $pageArray;
		unset($pagenav, $pagetext, $iPages, $pageArray);
		return $article_page;
	}
	public static function text2link($text=null){
		if (strpos($text, '||') !== false) {
			list($title, $url) = explode('||', $text);
			return '<a href="' . $url . '" target="_blank">' . $title . '</a>';
		}else{
			return $text;
		}
	}

	public function pnTitle($pn, $chapterArray, $chapter) {
		$title = $pn;
		$chapter && $title = $chapterArray[$pn - 1]['subtitle'];
		return $title;
	}
	public static function body_pics($body,&$pic_array=array()){
        preg_match_all(self::$pregimg,$body,$pic_array);
		$array = array_unique($pic_array[1]);
		$pics =  array();
		foreach ((array)$array as $key => $_pic) {
				$pics[$key] = trim($_pic);
		}
		return $pics;
	}
	public static function body_pics_page($pic_array,$article,$page,$total,$next_url){
		$img_array = array_unique($pic_array[0]);
		foreach ($img_array as $key => $img) {
			$img = str_replace('<img', '<img title="' . $article['title'] . '" alt="' . $article['title'] . '"', $img);
			if (self::$config['pic_center']) {
                $img_replace[$key] = '<p class="article_pic">'.$img.'</p>';
			} else {
				$img_replace[$key] = $img;
			}
            if(self::$config['pic_next'] && $total>1){
                $clicknext = '<a href="'.$next_url.'"><b>'.iUI::lang('iCMS:article:clicknext').' ('.$page.'/'.$total.')</b></a>';
				$clickimg = '<a href="' . $next_url . '" title="' . $article['title'] . '" class="img">' . $img . '</a>';
				if (self::$config['pic_center']) {
                    $img_replace[$key] = '<p class="click2next">'.$clicknext.'</p>';
                    $img_replace[$key].= '<p class="article_pic">'.$clickimg.'</p>';
				} else {
					$img_replace[$key] = '<p>' . $clicknext . '</p>';
					$img_replace[$key] .= '<p>' . $clickimg . '</p>';
				}
			}
		}
		return str_replace($img_array, $img_replace, $article['body']);
	}

}
