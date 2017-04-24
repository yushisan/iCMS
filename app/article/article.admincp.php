<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
*/
defined('iPHP') OR exit('What are you doing?');

class articleAdmincp{
    public $callback = array();
    public $chapter  = false;
    public static $config   = null;
    public static $appid = null;

    public function __construct() {
        self::$appid     = iCMS_APP_ARTICLE;
        $this->id        = (int)$_GET['id'];
        $this->dataid    = (int)$_GET['dataid'];
        $this->_postype  = '1';
        $this->_status   = '1';
        self::$config    = iCMS::$config['article'];
        tag::$appid      = self::$appid;
        category::$appid = self::$appid;
    }

    public function do_config(){
        configAdmincp::app(self::$appid);
    }
    public function do_save_config(){
        configAdmincp::save(self::$appid);
    }
    /**
     * [添加文章]
     */
    public function do_add(){
        $_GET['cid'] && category::check_priv($_GET['cid'],'ca','page');//添加权限
        $rs      = array();
        if($this->id){
            list($rs,$adRs) = article::data($this->id,$this->dataid);
            category::check_priv($rs['cid'],'ce','page');//编辑权限
            if($adRs){
                if($rs['chapter']){
                    foreach ($adRs as $key => $value) {
                        $adIdArray[$key] = $value['id'];
                        $cTitArray[$key] = $value['subtitle'];
                        $bodyArray[$key] = $value['body'];
                    }
                }else{
                    $adRs['body'] = htmlspecialchars($adRs['body']);
                    self::$config['editor'] = $rs['markdown']?true:false;
                    $adIdArray = array($adRs['id']);
                    $bodyArray = explode('#--iCMS.PageBreak--#',$adRs['body']);
                }
            }
        }

        $bodyCount = count($bodyArray);
        $bodyCount OR $bodyCount = 1;
        $cid         = empty($rs['cid'])?(int)$_GET['cid']:$rs['cid'];
        $cata_option = category::priv('ca')->select($cid);

        $rs['pubdate']       = get_date($rs['pubdate'],'Y-m-d H:i:s');
        $rs['markdown'] &&  self::$config['markdown'] = "1";
        if(empty($this->id)){
            $rs['status']  = "1";
            $rs['postype'] = "1";
            $rs['editor']  = empty(members::$data->nickname)?members::$data->username:members::$data->nickname;
            $rs['userid']  = members::$userid;
		}

        iPHP::callback(array("formerApp","add"),array(self::$appid,$rs,false));

        if(self::$config['markdown']){
            include admincp::view("article.markdown");
        }else{
            include admincp::view("article.add");
        }
    }
    public function do_update(){
    	$data = admincp::update_args($_GET['_args']);
        if($data){
            if(isset($data['pid'])){
                iMap::init('prop',self::$appid,'pid');
                $_pid = article::value('pid',$this->id);
                iMap::diff($data['pid'],$_pid,$this->id);
            }
            article::update($data,array('id'=>$this->id));
        }
    	iUI::success('操作成功!','js:1');
    }
    public function do_updateorder(){
        foreach((array)$_POST['sortnum'] as $sortnum=>$id){
            article::update(compact('sortnum'),compact('id'));
        }
    }
    public function do_batch(){
    	$_POST['id'] OR iUI::alert("请选择要操作的文章");
    	$ids	= implode(',',(array)$_POST['id']);
    	$batch	= $_POST['batch'];
    	switch($batch){
    		case 'order':
		        foreach((array)$_POST['sortnum'] AS $id=>$sortnum) {
                    article::update(compact('sortnum'),compact('id'));
		        }
		        iUI::success('排序已更新!','js:1');
    		break;
            case 'baiduping':
                foreach((array)$_POST['id'] AS $id) {
                    $this->do_baiduping($id,false);
                }
                iUI::success('推送完成!','js:1');
            break;
    		case 'move':
		        $_POST['cid'] OR iUI::alert("请选择目标栏目!");
                iMap::init('category',self::$appid,'cid');
                $cid = (int)$_POST['cid'];
                category::check_priv($cid,'ca','alert');
		        foreach((array)$_POST['id'] AS $id) {
                    $_cid = article::value('cid',$id);
                    article::update(compact('cid'),compact('id'));
		            if($_cid!=$cid) {
                        iMap::diff($cid,$_cid,$id);
                        categoryAdmincp::update_count($_cid,'-');
                        categoryAdmincp::update_count($cid);
		            }
		        }
		        iUI::success('成功移动到目标栏目!','js:1');
            break;
            case 'scid':
                //$_POST['scid'] OR iUI::alert("请选择目标栏目!");
                iMap::init('category',self::$appid,'cid');
                $scid = implode(',', (array)$_POST['scid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_scid = article::value('scid',$id);
                    article::update(compact('scid'),compact('id'));
                    iMap::diff($scid,$_scid,$id);
                }
                iUI::success('文章副栏目设置完成!','js:1');
            break;
            case 'prop':
                iMap::init('prop',self::$appid,'pid');
                $pid = implode(',', (array)$_POST['pid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_pid = article::value('pid',$id);
                    article::update(compact('pid'),compact('id'));
                    iMap::diff($pid,$_pid,$id);
                }
                iUI::success('文章属性设置完成!','js:1');
    		break;
    		case 'weight':
                $data = array('weight'=>$_POST['mweight']);
    		break;
    		case 'keyword':
    			if($_POST['pattern']=='replace') {
                    $data = array('keywords'=>iSecurity::escapeStr($_POST['mkeyword']));
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($_POST['id'] AS $id){
                        $keywords = article::value('keywords',$id);
                        $keywords = $keywords?$keywords.','.iSecurity::escapeStr($_POST['mkeyword']):iSecurity::escapeStr($_POST['mkeyword']);
                        article::update(compact('keywords'),compact('id'));
		        	}
		        	iUI::success('文章关键字更改完成!','js:1');
    			}
    		break;
    		case 'tag':
		     	foreach($_POST['id'] AS $id){
                    $art  = article::row($id,'tags,cid');
                    $mtag = iSecurity::escapeStr($_POST['mtag']);
			        if($_POST['pattern']=='replace') {
			        }elseif($_POST['pattern']=='addto') {
			        	$art['tags'] && $mtag = $art['tags'].','.$mtag;
			        }
			        $tags = tag::diff($mtag,$art['tags'],members::$userid,$id,$art['cid']);
                    $tags = addslashes($tags);
                    article::update(compact('tags'),compact('id'));
		    	}
		    	iUI::success('文章标签更改完成!','js:1');
    		break;
    		case 'thumb':
		        foreach((array)$_POST['id'] AS $id) {
		            $body	= article::body($id);
                    $picurl = $this->remotepic($body,'autopic',$id);
                    $this->set_pic($picurl,$id);
		        }
		        iUI::success('成功提取缩略图!','js:1');
    		break;
    		case 'dels':
    			iUI::$break	= false;
    			ob_implicit_flush();
    			$_count	= count($_POST['id']);
				foreach((array)$_POST['id'] AS $i=>$id) {
			     	$msg= $this->del($id);
			        $msg.= $this->del_msg('文章删除完成!');
					$updateMsg	= $i?true:false;
					$timeout	= ($i++)==$_count?'3':false;
					iUI::dialog($msg,'js:parent.$("#id'.$id.'").remove();',$timeout,0,$updateMsg);
		        	ob_end_flush();
	   			}
	   			iUI::$break	= true;
				iUI::success('文章全部删除完成!','js:1',3,0,true);
    		break;
    		default:
				$data = admincp::update_args($batch);
    	}
        $data && article::batch($data,$ids);
		iUI::success('操作成功!','js:1');
    }
    /**
     * [百度推送 ]
     * @param  [type]  $id     [description]
     * @param  boolean $dialog [description]
     * @return [type]          [description]
     */
    public function do_baiduping($id = null,$dialog=true){
        $id===null && $id=$this->id;
        $id OR iUI::alert('请选择要推送的文章!');
        $rs   = article::row($id);
        $C    = category::get($rs['cid']);
        $iurl = iURL::get('article',array($rs,$C));
        $url  = $iurl->href;
        $res  = baidu_ping($url);
        if($res===true){
            $dialog && iUI::success('推送完成','js:1');
        }else{
            iUI::alert('推送失败！['.$res->message.']','js:1');
        }
    }
    /**
     * [JSON数据]
     * @return [type] [description]
     */
    public function do_getjson(){
        $id = (int)$_GET['id'];
        $rs = article::row($id);
        iUI::json($rs);
    }
    /**
     * [简易编辑]
     * @return [type] [description]
     */
	public function do_updatetitle(){
        $id          = (int)$_POST['id'];
        $cid         = (int)$_POST['cid'];
        $pid         = (int)$_POST['pid'];
        $source      = iSecurity::escapeStr($_POST['source']);
        $title       = iSecurity::escapeStr($_POST['title']);
        $tags        = iSecurity::escapeStr($_POST['tags']);
        $description = iSecurity::escapeStr($_POST['description']);

		$art = article::row($id,'tags,cid');
		if($tags){
			$tags = tag::diff($tags,$art['tags'],members::$userid,$id,$art['cid']);
		    $tags = addslashes($tags);
        }
        $data = compact('cid','pid','title','tags','description');
		if($_POST['status']=="1"){
            $data['status'] = 1;
		}
		if($_POST['statustime']=="1"){
            $data['status']  = 1;
            $data['pubdate'] = time();
		}
        article::update($data ,compact('id'));
		exit('1');
	}
    /**
     * [查找正文图片]
     * @return [type] [description]
     */
    public function do_findpic(){
        $content = article::body($this->id);
        if($content){
            $content = stripslashes($content);
            preg_match_all("/<img.*?src\s*=[\"|'](.*?)[\"|']/is", $content, $match);
            $array  = array_unique($match[1]);
            $uri    = parse_url(iCMS_FS_URL);
            $fArray = array();
            foreach ($array as $key => $value) {
                $value = trim($value);
                // echo $value.PHP_EOL;
                if (stripos($value,$uri['host']) !== false){
                    $filepath = iFS::fp($value,'-http');
                    $rpath    = iFS::fp($value,'http2iPATH');
                   if($filepath){
                        $pf   = pathinfo($filepath);
                        $rs[] = array(
                            'id'       => 'path@'.$filepath,
                            'path'     => rtrim($pf['dirname'],'/').'/',
                            'filename' => $pf['filename'],
                            'size'     => @filesize($rpath),
                            'time'     => @filectime($rpath),
                            'ext'      => $pf['extension']
                        );
                    }
                }
                // echo "<hr />";
            }
            $_count = count($rs);
        }
        include admincp::view("files.manage","files");
    }
    /**
     * [正文预览]
     * @return [type] [description]
     */
    public function do_preview(){
		echo article::body($this->id);
    }
    public function do_iCMS(){
    	admincp::$APP_DO="manage";
    	$this->do_manage();
    }
    public function do_inbox(){
    	$this->do_manage("inbox");
    }
    public function do_trash(){
        $this->_postype = 'all';
    	$this->do_manage("trash");
    }
    public function do_user(){
        $this->_postype = 0;
        $this->do_manage();
    }
    public function do_examine(){
        $this->_postype = 0;
        $this->do_manage("examine");
    }
    public function do_off(){
        $this->_postype = 0;
        $this->do_manage("off");
    }

    public function do_manage($stype='normal') {
        $cid = (int)$_GET['cid'];
        $pid = $_GET['pid'];
        //$stype OR $stype = admincp::$app_do;
        $stype_map = array(
            'inbox'   =>'0',//草稿
            'normal'  =>'1',//正常
            'trash'   =>'2',//回收站
            'examine' =>'3',//待审核
            'off'     =>'4',//未通过
        );
        $map_where = array();
        //status:[0:草稿][1:正常][2:回收][3:待审核][4:不合格]
        //postype: [0:用户][1:管理员]
        $stype && $this->_status = $stype_map[$stype];
        if(isset($_GET['pt']) && $_GET['pt']!=''){
            $this->_postype = (int)$_GET['pt'];
        }
        if(isset($_GET['status'])){
            $this->_status = (int)$_GET['status'];
        }
        $sql = "WHERE `status`='{$this->_status}'";
        $this->_postype==='all' OR $sql.= " AND `postype`='{$this->_postype}'";

        if(members::check_priv("article.VIEW")){
            $_GET['userid'] && $sql.= iSQL::in($_GET['userid'],'userid');
        }else{
            $sql.= iSQL::in(members::$userid,'userid');
        }

        if(isset($_GET['pid']) && $pid!='-1'){
            $uri_array['pid'] = $pid;
            if(empty($_GET['pid'])){
                $sql.= " AND `pid`=''";
            }else{
                iMap::init('prop',self::$appid,'pid');
                $map_where+=iMap::where($pid);
            }
        }

        $cp_cids = category::check_priv('CIDS','cs');//取得所有有权限的栏目ID

        if($cp_cids) {
            if(is_array($cp_cids)){
                if($cid){
                    array_search($cid,$cp_cids)===false && admincp::permission_msg('栏目[cid:'.$cid.']',$ret);
                }else{
                    $cids = $cp_cids;
                }
            }else{
                $cids = $cid;
            }
            if($_GET['sub'] && $cid){
                $cids = categoryApp::get_cids($cid,true);
                array_push ($cids,$cid);
            }
            if($_GET['scid'] && $cid){
                iMap::init('category',self::$appid,'cid');
                $map_where+= iMap::where($cids);
            }else{
                $sql.= iSQL::in($cids,'cid');
            }
        }else{
            $sql.= iSQL::in('-1','cid');
        }

        if($_GET['keywords']) {
            $kws = $_GET['keywords'];
            switch ($_GET['st']) {
                case "title": $sql.=" AND `title` REGEXP '{$kws}'";break;
                case "tag":   $sql.=" AND `tags` REGEXP '{$kws}'";break;
                case "source":$sql.=" AND `source` REGEXP '{$kws}'";break;
                case "weight":$sql.=" AND `weight`='{$kws}'";break;
                case "id":
                $kws = str_replace(',', "','", $kws);
                $sql.=" AND `id` IN ('{$kws}')";
                break;
                case "tkd":   $sql.=" AND CONCAT(title,keywords,description) REGEXP '{$kws}'";break;
            }
        }
        $_GET['title']     && $sql.=" AND `title` like '%{$_GET['title']}%'";
        $_GET['tag']       && $sql.=" AND `tags` REGEXP '[[:<:]]".preg_quote(rawurldecode($_GET['tag']),'/')."[[:>:]]'";
        $_GET['starttime'] && $sql.=" AND `pubdate`>='".str2time($_GET['starttime']." 00:00:00")."'";
        $_GET['endtime']   && $sql.=" AND `pubdate`<='".str2time($_GET['endtime']." 23:59:59")."'";
        $_GET['post_starttime'] && $sql.=" AND `postime`>='".str2time($_GET['post_starttime']." 00:00:00")."'";
        $_GET['post_endtime']   && $sql.=" AND `postime`<='".str2time($_GET['post_endtime']." 23:59:59")."'";
        isset($_GET['pic'])&& $sql.=" AND `haspic` ='".($_GET['pic']?1:0)."'";

        isset($_GET['userid']) && $uri_array['userid']  = (int)$_GET['userid'];
        isset($_GET['keyword'])&& $uri_array['keyword'] = $_GET['keyword'];
        isset($_GET['tag'])    && $uri_array['tag']	    = $_GET['tag'];
        isset($_GET['pt'])     && $uri_array['pt']      = $_GET['pt'];
        isset($_GET['cid'])    && $uri_array['cid']     = $_GET['cid'];
		$uri_array	&& $uri = http_build_query($uri_array);

        $orderby    = $_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;

        if($map_where){
            $map_sql = iSQL::select_map($map_where);
            $sql     = ",({$map_sql}) map {$sql} AND `id` = map.`iid`";
        }

        $total = iCMS::page_total_cache(article::count_sql($sql),"G");
        iUI::pagenav($total,$maxperpage,"篇文章");

        $limit = 'LIMIT '.iUI::$offset.','.$maxperpage;

        if($map_sql||iUI::$offset){
            if(iUI::$offset > 1000 && $total > 2000 && iUI::$offset >= $total/2) {
                $_offset = $total-iUI::$offset-$maxperpage;
                if($_offset < 0) {
                    $_offset = 0;
                }
                $orderby = "id ASC";
                $limit = 'LIMIT '.$_offset.','.$maxperpage;
            }
        // if($map_sql){
            $ids_array = iDB::all("
                SELECT `id` FROM `#iCMS@__article` {$sql}
                ORDER BY {$orderby} {$limit}
            ");
            if(isset($_offset)){
                $ids_array = array_reverse($ids_array, TRUE);
                $orderby   = "id DESC";
            }

            $ids = iSQL::values($ids_array);
            $ids = $ids?$ids:'0';
            $sql = "WHERE `id` IN({$ids})";
            // }else{
                // $sql = ",(
                    // SELECT `id` AS aid FROM `#iCMS@__article` {$sql}
                    // ORDER BY {$orderby} {$limit}
                // ) AS art WHERE `id` = art.aid ";
            // }
            $limit = '';
        }
        $rs = iDB::all("SELECT * FROM `#iCMS@__article` {$sql} ORDER BY {$orderby} {$limit}");
        $_count = count($rs);
        $propArray = propAdmincp::get("pid",null,'array');
        include admincp::view("article.manage");
    }
    public function do_save(){
        $aid         = (int)$_POST['aid'];
        $cid         = (int)$_POST['cid'];
        category::check_priv($cid,($aid?'ce':'ca'),'alert');


        $userid      = (int)$_POST['userid'];
        $scid        = implode(',', (array)$_POST['scid']);
        $pid         = implode(',', (array)$_POST['pid']);
        $status      = (int)$_POST['status'];
        $chapter     = (int)$_POST['chapter'];
        $sortnum    = (int)$_POST['sortnum'];
        $weight      = (int)$_POST['weight'];

        $hits        = (int)$_POST['hits'];
        $hits_today  = (int)$_POST['hits_today'];
        $hits_yday   = (int)$_POST['hits_yday'];
        $hits_week   = (int)$_POST['hits_week'];
        $hits_month  = (int)$_POST['hits_month'];
        $favorite    = (int)$_POST['favorite'];
        $comments    = (int)$_POST['comments'];
        $good        = (int)$_POST['good'];
        $bad         = (int)$_POST['bad'];

        $_cid        = iSecurity::escapeStr($_POST['_cid']);
        $_pid        = iSecurity::escapeStr($_POST['_pid']);
        $_scid       = iSecurity::escapeStr($_POST['_scid']);
        $_tags       = iSecurity::escapeStr($_POST['_tags']);
        $title       = iSecurity::escapeStr($_POST['title']);
        $stitle      = iSecurity::escapeStr($_POST['stitle']);
        $pic         = iSecurity::escapeStr($_POST['pic']);
        $mpic        = iSecurity::escapeStr($_POST['mpic']);
        $spic        = iSecurity::escapeStr($_POST['spic']);
        $source      = iSecurity::escapeStr($_POST['source']);
        $author      = iSecurity::escapeStr($_POST['author']);
        $editor      = iSecurity::escapeStr($_POST['editor']);
        $description = iSecurity::escapeStr($_POST['description']);
        $keywords    = iSecurity::escapeStr($_POST['keywords']);
        $tags        = str_replace('，', ',',iSecurity::escapeStr($_POST['tags']));
        $clink       = iSecurity::escapeStr($_POST['clink']);
        $url         = iSecurity::escapeStr($_POST['url']);
        $tpl         = iSecurity::escapeStr($_POST['tpl']);
        $body        = (array)$_POST['body'];
        $creative    = (int)$_POST['creative'];
        $markdown    = (int)$_POST['markdown'];

        empty($title)&& iUI::alert('标题不能为空！');
        empty($cid)  && iUI::alert('请选择所属栏目');
        empty($body) && empty($url) && iUI::alert('文章内容不能为空！');

        $pubdate   = str2time($_POST['pubdate']);
        $postype   = $_POST['postype']?$_POST['postype']:0;
        $userid OR $userid = members::$userid;
        $tags && $tags = preg_replace('/<[\/\!]*?[^<>]*?>/is','',$tags);

        if($this->callback['code']){
            $fwd = iPHP::callback(array("filterApp","run"),array(&$title),false);
            if($fwd){
                echo '标题中包含【'.$fwd.'】被系统屏蔽的字符，请重新填写。';
                return false;
            }
        }

        if(self::$config['filter']) {
            $fwd = iPHP::callback(array("filterApp","run"),array(&$title),false);
            // filterAdmincp::run($title);
            $fwd && iUI::alert('标题中包含【'.$fwd.'】被系统屏蔽的字符，请重新填写。');
            $fwd = iPHP::callback(array("filterApp","run"),array(&$description),false);
            $fwd && iUI::alert('简介中包含被【'.$fwd.'】系统屏蔽的字符，请重新填写。');
            // $fwd = filterAdmincp::run($body);
            // $fwd && iUI::alert('内容中包含被系统屏蔽的字符，请重新填写。');
        }

        if(empty($aid) && self::$config['repeatitle']) {
            article::check_title($title) && iUI::alert('该标题的文章已经存在!请检查是否重复');
        }
        $category = category::get($cid);
        if(strstr($category->rule->article,'{LINK}')!==false){
            if(empty($clink)){
                $clink = iPinyin::get($title);
            }
            if(empty($aid) && $clink) {
                article::check_clink($clink) && iUI::alert('该文章自定义链接已经存在!请检查是否重复');
            }
        }


        if(empty($description) && empty($url)) {
            $description = $this->autodesc($body);
        }

        (iFS::checkHttp($pic)  && !isset($_POST['pic_http']))  && $pic  = iFS::http($pic);
        (iFS::checkHttp($mpic) && !isset($_POST['mpic_http'])) && $mpic = iFS::http($mpic);
        (iFS::checkHttp($spic) && !isset($_POST['spic_http'])) && $spic = iFS::http($spic);


        $haspic   = empty($pic)?0:1;

        $REFERER_URL = $_POST['REFERER'];
        if(empty($REFERER_URL)||strstr($REFERER_URL, '=save')){
        	$REFERER_URL= APP_URI.'&do=manage';
        }

        $editor OR	$editor	= empty(members::$data->nickname)?members::$data->username:members::$data->nickname;

        $picdata = '';
        $ucid    = 0;

        $fields  = article::fields($aid);

        if(empty($aid)) {
            $postime = $pubdate;
            $chapter = 0;
            $mobile  = 0;

            $aid  = article::insert(compact($fields));
            admincp::callback($aid,$this,'primary');

            if($tags){
                if(isset($_POST['tag_status'])){
                    tag::$add_status = $_POST['tag_status'];
                }
                tag::add($tags,members::$userid,$aid,$cid);
            }

            iMap::init('prop',self::$appid,'pid');
            $pid && iMap::add($pid,$aid);

            iMap::init('category',self::$appid,'cid');
            iMap::add($cid,$aid);
            $scid && iMap::add($scid,$aid);

            $url OR $this->article_data($body,$aid,$haspic);
            categoryAdmincp::update_count($cid);

            iPHP::callback(array("formerApp","save"),array(self::$appid,$aid));

            $article_url = iURL::get('article',array(array(
                'id'      =>$aid,
                'url'     =>$url,
                'cid'     =>$cid,
                'pubdate' =>$pubdate
            ),(array)$category))->href;

            if($status && iCMS::$config['api']['baidu']['sitemap']['sync']){
                baidu_ping($article_url);
            }

            if($this->callback['code']){
                return array(
                    "code"    => $this->callback['code'],
                    'indexid' => $aid
                );
            }
            if($_GET['callback']=='json'){
                echo json_encode(array(
                    "code"    => '1001',
                    'indexid' => $aid
                ));
                return;
            }
            $moreBtn = array(
                    array("text" =>"查看该文章","target"=>'_blank',"url"=>$article_url,"close"=>false),
                    array("text" =>"编辑该文章","url"=>APP_URI."&do=add&id=".$aid),
                    array("text" =>"继续添加文章","url"=>APP_URI."&do=add&cid=".$cid),
                    array("text" =>"返回文章列表","url"=>$REFERER_URL),
                    array("text" =>"查看网站首页","url"=>iCMS_URL,"target"=>'_blank')
            );
            iUI::$dialog['lock']	= true;
            iUI::dialog('success:#:check:#:文章添加完成!<br />10秒后返回文章列表','url:'.$REFERER_URL,10,$moreBtn);
        }else{
            isset($_POST['ischapter']) OR $chapter = 0;

	        $tags && tag::diff($tags,$_tags,members::$userid,$aid,$cid);

            $picdata = $this->picdata($pic,$mpic,$spic);

            article::update(compact($fields),array('id'=>$aid));
            admincp::callback($aid,$this,'primary');

            iMap::init('prop',self::$appid,'pid');
            iMap::diff($pid,$_pid,$aid);
            iMap::init('category',self::$appid,'cid');
            iMap::diff($cid,$_cid,$aid);
            $scid && iMap::diff($scid,$_scid,$aid);

            $url OR $this->article_data($body,$aid,$haspic);

            if($_cid!=$cid) {
                categoryAdmincp::update_count($_cid,'-');
                categoryAdmincp::update_count($cid);
            }
            iPHP::callback(array("formerApp","save"),array(self::$appid,$aid));
            if($this->callback['code']){
                return array(
                    "code"    => $this->callback['code'],
                    'indexid' => $aid
                );
            }

            iUI::success('文章编辑完成!<br />3秒后返回文章列表','url:'.$REFERER_URL);
        }
    }
    public function do_del(){
        $msg = $this->del($this->id);
        $msg.= $this->del_msg('文章删除完成!');
        $msg.= $this->del_msg('10秒后返回文章列表!');
        iUI::$dialog['lock'] = true;
        iUI::dialog($msg,'js:1');
    }

    public static function del_msg($str){
        return iUI::msg('success:#:check:#:'.$str.'<hr />',true);
    }
    public function del_pic($pic){
        //$thumbfilepath    = gethumb($pic,'','',false,true,true);
        iFS::del(iFS::fp($pic,'+iPATH'));
        $msg    = $this->del_msg($pic.'删除');
//      if($thumbfilepath)foreach($thumbfilepath as $wh=>$fp) {
//              iFS::del(iFS::fp($fp,'+iPATH'));
//              $msg.= $this->del_msg('缩略图 '.$wh.' 文件删除');
//      }
        $filename   = iFS::info($pic)->filename;
        article::del_filedata($filename,'filename');
        $msg.= $this->del_msg($pic.'数据删除');
        return $msg;
    }
    public static function del($id,$uid='0',$postype='1') {
        $id = (int)$id;
        $id OR iUI::alert("请选择要删除的文章");
        $uid && $sql="and `userid`='$uid' and `postype`='$postype'";
        $art = article::row($id,'cid,pic,tags',$sql);
        category::check_priv($art['cid'],'cd','alert');

        $fids   = files::index_fileid($id,self::$appid);
        $pieces = files::delete_file($fids);
        files::delete_fdb($fids,$id,self::$appid);
        $msg.= self::del_msg(implode('<br />', $pieces).' 文件删除');
        $msg.= self::del_msg('相关文件数据删除');

        if($art['tags']){
            //只删除关联数据 不删除标签
            tag::$remove = false;
            $msg.= tag::del($art['tags'],'name',$id);
        }

        iMap::del_data($id,self::$appid,'category');
        iMap::del_data($id,self::$appid,'prop');

        article::del_comment($id);
        $msg.= self::del_msg('评论数据删除');
        article::del($id);
        article::del_data($id);
        $msg.= self::del_msg('文章数据删除');
        categoryAdmincp::update_count($art['cid'],'-');
        $msg.= self::del_msg('栏目数据更新');
        $msg.= self::del_msg('删除完成');
        return $msg;
    }
    public function chapter_count($aid){
        article::chapter_count($aid);
    }
    public function article_data($bodyArray,$aid=0,$haspic=0){
        if(isset($_POST['ischapter']) || is_array($_POST['adid'])){
            $adidArray    = $_POST['adid'];
            $chaptertitle = $_POST['chaptertitle'];
            $chapter      = count($bodyArray);
            foreach ($bodyArray as $key => $body) {
                $adid     = (int)$adidArray[$key];
                $subtitle = iSecurity::escapeStr($chaptertitle[$key]);
                $this->body($body,$subtitle,$aid,$adid,$haspic);
            }
            article::update(compact('chapter'),array('id'=>$aid));
        }else{
            $adid     = (int)$_POST['adid'];
            $subtitle = iSecurity::escapeStr($_POST['subtitle']);
            $body     = implode('#--iCMS.PageBreak--#',$bodyArray);
            $this->body($body,$subtitle,$aid,$adid,$haspic);
        }
        admincp::callback($aid,$this,'data');
    }
    public function body($body,$subtitle,$aid=0,$id=0,&$haspic=0){

        $body = preg_replace(array('/<script.+?<\/script>/is','/<form.+?<\/form>/is'),'',$body);
        isset($_POST['dellink']) && $body = preg_replace("/<a[^>].*?>(.*?)<\/a>/si", "\\1",$body);

        if($_POST['markdown']){
            $body = $body;
        }else{
            self::$config['autoformat'] && $body = addslashes(autoformat($body));
        }

        article::$ID = $aid;

        $fields = article::data_fields($id);
        $data   = compact ($fields);

        if($id){
            article::data_update($data,compact('id'));
        }else{
            $id = article::data_insert($data);
        }

        $_POST['iswatermark']&& files::$watermark = false;

        if(isset($_POST['remote'])){
            $body = $this->remotepic($body,true,$aid);
            $body = $this->remotepic($body,true,$aid);
            $body = $this->remotepic($body,true,$aid);
            if($body && $id){
                article::data_update(array('body'=>$body),compact('id'));
            }
        }

        if(isset($_POST['autopic']) && empty($haspic)){
            if($picurl = $this->remotepic($body,'autopic',$aid)){
                $this->set_pic($picurl,$aid);
                $haspic = true;
            }
        }
        $this->body_pic_indexid($body,$aid);
    }
    public static function autodesc($body){
        if(self::$config['autodesc'] && self::$config['descLen']) {
            is_array($body) && $bodyText   = implode("\n",$body);
            $bodyText   = str_replace('#--iCMS.PageBreak--#',"\n",$bodyText);
            $bodyText   = str_replace('</p><p>', "</p>\n<p>", $bodyText);

            $textArray = explode("\n", $bodyText);
            $pageNum   = 0;
            $resource  = array();
            foreach ($textArray as $key => $p) {
                $text      = preg_replace(array('/<[\/\!]*?[^<>]*?>/is','/\s*/is'),'',$p);
                // $pageLen   = strlen($resource);
                // $output    = implode('',array_slice($textArray,$key));
                // $outputLen = strlen($output);
                $output    = implode('',$resource);
                $outputLen = strlen($output);
                if($outputLen>self::$config['descLen']){
                    // $pageNum++;
                    // $resource[$pageNum] = $p;
                    break;
                }else{
                    $resource[]= $text;
                }
            }
            $description = implode("\n", $resource);
            $description = csubstr($description,self::$config['descLen']);
            $description = addslashes(trim($description));
            $description = str_replace('#--iCMS.PageBreak--#','',$description);
            unset($bodyText);
            return $description;
        }
    }
    public function set_pic($picurl,$aid){
        $uri = parse_url(iCMS_FS_URL);
        if (stripos($picurl,$uri['host']) !== false){
            $picdata = (array)article::value('picdata',$aid);
            $picdata && $picdata = @unserialize($picdata);
            $pic = iFS::fp($picurl,'-http');
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($pic,'+iPATH'));
            $picdata['b'] = array('w'=>$width,'h'=>$height);
            $picdata = addslashes(serialize($picdata));
            $haspic  = 1;
            article::update(compact('haspic','pic','picdata'),array('id'=>$aid));
            files::set_map(self::$appid,$aid,$pic,'path');
        }
    }
    public function remotepic($content,$remote = false) {
        if (!$remote) return $content;

        iFS::$force_ext = "jpg";
        $content = stripslashes($content);
        preg_match_all('@<img[^>]+src=(["\']?)(.*?)\\1[^>]*?>@is', $content, $match);
        $array  = array_unique($match[2]);
        $uri    = parse_url(iCMS_FS_URL);
        $fArray = array();
        foreach ($array as $key => $value) {
            $value = trim($value);
            if (stripos($value,$uri['host']) === false){
                $filepath = iFS::http($value);
                $rootfilpath = iFS::fp($filepath, '+iPATH');
                list($owidth, $oheight, $otype) = @getimagesize($rootfilpath);

                if($filepath && !iFS::checkHttp($filepath) && $otype){
                    $value = iFS::fp($filepath,'+http');
                }else{
                    if($this->DELETE_ERROR_PIC){
                        iFS::del($rootfilpath);
                        $array[$key]  = $match[0][$key];
                        $value = '';
                    }
                }
                $fArray[$key] = $value;
            }else{
                unset($array[$key]);
                $rootfilpath = iFS::fp($value, 'http2iPATH');
                list($owidth, $oheight, $otype) = @getimagesize($rootfilpath);
                if($this->DELETE_ERROR_PIC && empty($otype)){
                    iFS::del($rootfilpath);
                    $array[$key]  = $match[0][$key];
                    $fArray[$key] = '';
                }
            }
            if($remote==="autopic" && $key==0){
                return $value;
            }
        }
        if($remote==="autopic" && empty($array)){
            return;
        }
        if($array && $fArray){
            krsort($array);
            krsort($fArray);
            $content = str_replace($array, $fArray, $content);
        }
        return addslashes($content);
    }
    public function body_pic_indexid($content,$indexid) {
        if(empty($content)){
            return;
        }
        $content = stripslashes($content);
        preg_match_all("/<img.*?src\s*=[\"|'](.*?)[\"|']/is", $content, $match);
        $array  = array_unique($match[1]);
        foreach ($array as $key => $value) {
            files::set_map(self::$appid,$indexid,$value,'path');
        }
    }

    public function picdata($pic='',$mpic='',$spic=''){
        $picdata = array();
        if($pic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($pic,'+iPATH'));
            $picdata['b'] = array('w'=>$width,'h'=>$height);
        }
        if($mpic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($mpic,'+iPATH'));
            $picdata['m'] = array('w'=>$width,'h'=>$height);
        }
        if($spic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($spic,'+iPATH'));
            $picdata['s'] = array('w'=>$width,'h'=>$height);
        }
        return $picdata?addslashes(serialize($picdata)):'';
    }
    public function check_pic($body,$aid=0){
        // global $status;
        // if($status!='1'){
        //     return;
        // }
        $body = stripcslashes($body);
        preg_match_all('@<img[^>]+src=(["\']?)(.*?)\\1[^>]*?>@is',$body,$pic_array);
        $p_array = array_unique($pic_array[2]);

        foreach((array)$p_array as $key =>$url) {
            $url = trim($url);
            $filpath = iFS::fp($url, 'http2iPATH');
            // var_dump($filpath);
            list($owidth, $oheight, $otype) = @getimagesize($filpath);
            if(empty($otype)){
                // var_dump($filpath,$otype);
                if($aid){
                    iDB::update('article',array('status'=>'0'),array('id'=>$aid));
                    echo $aid." status:2\n";
                }
                return true;
            }
        }
        return false;
    }
}
