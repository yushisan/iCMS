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

class tagAdmincp{
    public $appid = null;
    public $callback = array();
    public function __construct() {
        $this->appid = iCMS_APP_TAG;
        $this->id = (int)$_GET['id'];
    }
    public function do_config(){
        configAdmincp::app($this->appid);
    }
    public function do_save_config(){
        $_POST['config']['url'] = trim($_POST['config']['url'],'/');
        $_POST['config']['dir'] = rtrim($_POST['config']['dir'],'/').'/';
        configAdmincp::save($this->appid);
    }

    public function do_add(){
        $this->id && $rs = iDB::row("SELECT * FROM `#iCMS@__tags` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        iPHP::callback(array("formerApp","add"),array($this->appid,$rs,true));
        include admincp::view('tag.add');
    }
    public function do_update(){
        if($this->id){
            $data = admincp::update_args($_GET['_args']);
            $data && iDB::update("tags",$data,array('id'=>$this->id));
            tag::cache($this->id,'id');
            iUI::success('操作成功!','js:1');
        }
    }
    public function do_iCMS(){
    	admincp::$APP_METHOD="domanage";
    	$this->do_manage();
    }
    public function do_manage(){
        $sql  = " where 1=1";
        $cid    = (int)$_GET['cid'];
        $tcid   = (int)$_GET['tcid'];
        $pid    = (int)$_GET['pid'];
        $rootid = (int)$_GET['rootid'];

        $_GET['keywords'] && $sql.=" AND CONCAT(name,seotitle,subtitle,keywords,description) REGEXP '{$_GET['keywords']}'";

        $sql.= category::search_sql($cid);
        $sql.= category::search_sql($tcid,'tcid');
        $_GET['starttime'] && $sql.=" AND `pubdate`>='".str2time($_GET['starttime']." 00:00:00")."'";
        $_GET['endtime']   && $sql.=" AND `pubdate`<='".str2time($_GET['endtime']." 23:59:59")."'";
        $_GET['post_starttime'] && $sql.=" AND `postime`>='".str2time($_GET['post_starttime']." 00:00:00")."'";
        $_GET['post_endtime']   && $sql.=" AND `postime`<='".str2time($_GET['post_endtime']." 23:59:59")."'";

        isset($_GET['pic']) && $sql.=" AND `haspic` ='".($_GET['pic']?1:0)."'";
        if(isset($_GET['pid']) && $pid!='-1'){
            $uri_array['pid'] = $pid;
            if($_GET['pid']==0){
                $sql.= " AND `pid`=''";
            }else{
                iMap::init('prop',$this->appid,'pid');
                $map_where = iMap::where($pid);
            }
        }
        if($map_where){
            $map_sql = iSQL::select_map($map_where);
            $sql     = ",({$map_sql}) map {$sql} AND `id` = map.`iid`";
        }

        $orderby	= $_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total		= iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__tags` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个标签");
        $limit  = 'LIMIT '.iUI::$offset.','.$maxperpage;
        if($map_sql||iUI::$offset){
            if(iUI::$offset > 1000 && $total > 2000 && iUI::$offset >= $total/2) {
                $_offset = $total-iUI::$offset-$maxperpage;
                if($_offset < 0) {
                    $_offset = 0;
                }
                $orderby = "id ASC";
                $limit = 'LIMIT '.$_offset.','.$maxperpage;
            }
            $ids_array = iDB::all("
                SELECT `id` FROM `#iCMS@__tags` {$sql}
                ORDER BY {$orderby} {$limit}
            ");
            if(isset($_offset)){
                $ids_array = array_reverse($ids_array, TRUE);
                $orderby   = "id DESC";
            }
            $ids   = iSQL::values($ids_array);
            $ids   = $ids?$ids:'0';
            $sql   = "WHERE `id` IN({$ids})";
            $limit = '';
        }
        $rs     = iDB::all("SELECT * FROM `#iCMS@__tags` {$sql} ORDER BY {$orderby} {$limit}");
        $_count = count($rs);
        $propArray = propAdmincp::get("pid",null,'array');
    	include admincp::view("tag.manage");
    }
    /**
     * [导入标签]
     * @return [type] [description]
     */
    public function do_import(){
        $_POST['cid'] OR iUI::alert('请选择标签所属栏目！');
        files::$check_data        = false;
        files::$cloud_enable      = false;
        iFS::$config['allow_ext'] = 'txt';
        $F    = iFS::upload('upfile');
        $path = $F['RootPath'];
        if($path){
            $contents = file_get_contents($path);
            $contents = iSecurity::encoding($contents);
            if($contents){
                $fields   = array('uid', 'cid', 'tcid', 'pid', 'tkey',
                    'name', 'seotitle', 'subtitle', 'keywords', 'description',
                    'haspic', 'pic', 'url', 'related', 'count', 'weight', 'tpl',
                    'sortnum', 'pubdate', 'status'
                );
                $cid      = implode(',', (array)$_POST['cid']);
                $tcid     = implode(',', (array)$_POST['tcid']);
                $pid      = implode(',', (array)$_POST['pid']);
                $variable = explode("\n", $contents);
                $msg = array();
                foreach ($variable as $key => $name) {
                    $name = trim($name);
                    if(empty($name)){
                        $msg['empty']++;
                        continue;
                    }
                    $name = preg_replace('/<[\/\!]*?[^<>]*?>/is','',$name);
                    $name = addslashes($name);
                    if(iDB::value("SELECT `id` FROM `#iCMS@__tags` where `name` = '$name'")){
                        $msg['has']++;
                        continue;
                    }
                    $tkey    = strtolower(iPinyin::get($name));
                    $uid     = members::$userid;
                    $haspic  = '0';
                    $status  = '1';
                    $pubdate = time();
                    $data    = compact ($fields);
                    $id = iDB::insert('tags',$data);

                    $pid && iMap::init('prop',$this->appid,'pid')->add($pid,$id);
                    iMap::init('category',$this->appid,'cid');
                    iMap::add($cid,$id);
                    $tcid && iMap::add($tcid,$id);
                    $msg['success']++;
                }
            }
            @unlink($path);
            iUI::success('标签导入完成<br />空标签:'.(int)$msg['empty'].'个<br />已经存在标签:'.(int)$msg['has'].'个<br />成功导入标签:'.(int)$msg['success'].'个');
        }
    }
    public function do_save(){
        $id          = (int)$_POST['id'];
        $uid         = (int)$_POST['uid'];
        $rootid      = (int)$_POST['rootid'];
        $cid         = implode(',', (array)$_POST['cid']);
        $tcid        = implode(',', (array)$_POST['tcid']);
        $pid         = implode(',', (array)$_POST['pid']);
        $_cid        = iSecurity::escapeStr($_POST['_cid']);
        $_tcid       = iSecurity::escapeStr($_POST['_tcid']);
        $_pid        = iSecurity::escapeStr($_POST['_pid']);
        $name        = iSecurity::escapeStr($_POST['name']);
        $subtitle    = iSecurity::escapeStr($_POST['subtitle']);
        $tkey        = iSecurity::escapeStr($_POST['tkey']);
        $seotitle    = iSecurity::escapeStr($_POST['seotitle']);
        $keywords    = iSecurity::escapeStr($_POST['keywords']);
        $pic         = iSecurity::escapeStr($_POST['pic']);
        $bpic        = iSecurity::escapeStr($_POST['bpic']);
        $mpic        = iSecurity::escapeStr($_POST['mpic']);
        $spic        = iSecurity::escapeStr($_POST['spic']);
        $description = iSecurity::escapeStr($_POST['description']);
        $url         = iSecurity::escapeStr($_POST['url']);
        $related     = iSecurity::escapeStr($_POST['related']);
        $tpl         = iSecurity::escapeStr($_POST['tpl']);
        $weight      = (int)$_POST['weight'];
        $sortnum    = (int)$_POST['sortnum'];
        $status      = (int)$_POST['status'];
        $haspic      = $pic?'1':'0';
        $pubdate     = time();

        $uid OR $uid= members::$userid;

        if($callback){
            if(empty($name)){
                echo '标签名称不能为空！';
                return false;
            }
        }
        $name OR iUI::alert('标签名称不能为空！');
        // $cid OR iUI::alert('请选择标签所属栏目！');

		if(empty($id)) {
            $hasNameId = iDB::value("SELECT `id` FROM `#iCMS@__tags` where `name` = '$name'");
            if($hasNameId){
                if(isset($_POST['spider_update'])){
                    $id = $hasNameId;
                }else{
                    iUI::alert('该标签已经存在!请检查是否重复');
                }
            }
		}
		if(empty($tkey) && $url){
			$tkey = substr(md5($url),8,16);
			$hasTkey = iDB::value("SELECT `id` FROM `#iCMS@__tags` where `tkey` = '$tkey'");
            if($hasTkey){
                if(isset($_POST['spider_check_tkey'])){
                    echo '该自定义链接已经存在!请检查是否重复';
                    return false;
                }else{
                    iUI::alert('该自定义链接已经存在!请检查是否重复');
                }
            }
		}

		$tkey OR $tkey = strtolower(iPinyin::get($name));

        iFS::$force_ext = "jpg";
        iFS::checkHttp($pic) && $pic = iFS::http($pic);
        iFS::checkHttp($bpic)&& $bpic = iFS::http($bpic);
        iFS::checkHttp($mpic)&& $mpic = iFS::http($mpic);
        iFS::checkHttp($spic)&& $spic = iFS::http($spic);

        $fields = array('uid','rootid', 'cid', 'tcid', 'pid',
            'tkey', 'name', 'seotitle', 'subtitle', 'keywords',
            'description', 'haspic', 'pic','bpic','mpic','spic', 'url',
            'related', 'count', 'weight', 'tpl',
            'sortnum', 'pubdate', 'status'
        );
        $data   = compact ($fields);

		if(empty($id)){
            $this->check_tkey($tkey);
            $data['tkey']     = $tkey;
            $data['postime']  = $pubdate;
            $data['count']    = '0';
            $data['comments'] = '0';
            $id = iDB::insert('tags',$data);
			tag::cache($id,'id');

            iMap::init('prop',$this->appid,'pid');
            $pid && iMap::add($pid,$id);

            iMap::init('category',$this->appid,'cid');
            iMap::add($cid,$id);
            $tcid && iMap::add($tcid,$id);

            $msg ='标签添加完成';
		}else{

            $this->check_tkey($tkey,$id);
            $data['tkey'] = $tkey;

            unset($data['count'],$data['comments']);
            iDB::update('tags', $data, array('id'=>$id));
			tag::cache($id,'id');

            iMap::init('prop',$this->appid,'pid');
            iMap::diff($pid,$_pid,$id);

            iMap::init('category',$this->appid,'cid');
            iMap::diff($cid,$_cid,$id);
            iMap::diff($tcid,$_tcid,$id);
            $msg = '标签更新完成';
		}
        iPHP::callback(array("formerApp","save"),array($this->appid,$id));
        admincp::callback($id,$this);
        if($this->callback['code']){
            return array(
                "code"    => $this->callback['code'],
                'indexid' => $id
            );
        }
        iUI::success($msg,"url:".APP_URI);
    }
    public function check_tkey(&$tkey,$id=0){
        $sql = "SELECT count(`id`) FROM `#iCMS@__tags` where `tkey` ='$tkey' ";
        $id && $sql.=" AND `id` !='$id'";
        $hasTkey = iDB::value($sql);
        if($hasTkey){
            $count = iDB::value("SELECT count(`id`) FROM `#iCMS@__tags` where `tkey` LIKE '{$tkey}-%'");
            $tkey = $tkey.'-'.($count+1);
        }
    }
    public function __callback($id){
        if ($this->callback['primary']) {
            $PCB = $this->callback['primary'];
            $handler = $PCB[0];
            $params  = (array)$PCB[1]+array('indexid'=>$id);
            if (is_callable($handler)){
                call_user_func_array($handler,$params);
            }
        }
        if ($this->callback['data']) {
            $DCB     = $this->callback['data'];
            $handler = $DCB[0];
            $params  = (array)$DCB[1];
            if (is_callable($handler)){
                call_user_func_array($handler,$params);
            }
        }
    }
    public function check_spider_data(&$data,$old,$key,$value){
        if($old[$key]){
            if($value){
                $data[$key] = $value;
            }else{
                unset($data[$key]);
            }
        }
    }
    public function do_cache(){
    	tag::cache($this->id,'id');
    	iUI::success("标签缓存更新成功");
    }
    public function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
        iMap::del_data($id,$this->appid,'category');
        iMap::del_data($id,$this->appid,'prop');
    	tag::del($id,'id');
    	$dialog && iUI::success("标签删除成功",'js:parent.$("#tr'.$id.'").remove();');
    }
    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要操作的标签");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
            case 'keywords':
                $rs = iDB::all("SELECT * FROM `#iCMS@__tags` where `id` IN ($ids)");
                $categoryArray  = category::multi_get($rs,'cid');
                $tcategoryArray = category::multi_get($rs,'tcid',$this->appid);
                foreach($rs AS $tag){
                    $C          = (array)$categoryArray[$tag['cid']];
                    $TC         = (array)$tcategoryArray[$tag['tcid']];
                    $iurl       = iURL::get('tag',array($tag,$C,$TC));
                    $tag['url'] = $iurl->href;
                    $data = array();
                    $data['keyword'] = $tag['name'];
                    $data['replace'] = '<a href="'.$tag['url'].'" target="_blank" class="keywords"/>'.$tag['name'].'</a>';
                    $data['replace'] = htmlspecialchars($data['replace']);
                    array_map('addslashes', $data);
                    iPHP::callback(array('keywordsAdmincp','insert'),array($data));
                }
                iUI::success('内链添加完成!','js:1');
            break;
    		case 'dels':
				iUI::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iUI::$break	= true;
				iUI::success('标签全部删除完成!','js:1');
    		break;
    		case 'move':
		        $_POST['cid'] OR iUI::alert("请选择目标栏目!");
                iMap::init('category',$this->appid,'cid');
		        $cid = (int)$_POST['cid'];
		        foreach($idArray AS $id) {
                    $_cid = iDB::value("SELECT `cid` FROM `#iCMS@__tags` where `id` ='$id'");
                    iDB::update("tags",compact('cid'),compact('id'));
		            if($_cid!=$cid) {
                        iMap::diff($cid,$_cid,$id);
                        categoryAdmincp::update_count($_cid,'-');
                        categoryAdmincp::update_count($cid);
		            }
		        }
		        iUI::success('成功移动到目标栏目!','js:1');
    		break;
    		case 'mvtcid':
		        $_POST['tcid'] OR iUI::alert("请选择目标分类!");
                iMap::init('category',$this->appid,'cid');
		        $tcid = (int)$_POST['tcid'];
		        foreach($idArray AS $id) {
                    $_tcid = iDB::value("SELECT `tcid` FROM `#iCMS@__tags` where `id` ='$id'");
                    iDB::update("tags",compact('tcid'),compact('id'));
		            if($_tcid!=$tcid) {
                        iMap::diff($tcid,$_tcid,$id);
                        categoryAdmincp::update_count($_tcid,'-');
                        categoryAdmincp::update_count($tcid);
		            }
		        }
		        iUI::success('成功移动到目标分类!','js:1');
    		break;
    		case 'prop':
                iMap::init('prop',$this->appid,'pid');
                $pid = implode(',', (array)$_POST['pid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_pid = iDB::value("SELECT pid FROM `#iCMS@__tags` WHERE `id`='$id'");;
                    iDB::update("tags",compact('pid'),compact('id'));
                    iMap::diff($pid,$_pid,$id);
                }
                iUI::success('属性设置完成!','js:1');
    		break;
    		case 'weight':
		        $weight	=(int)$_POST['mweight'];
		        $sql	="`weight` = '$weight'";
    		break;
            case 'tpl':
                $tpl = iSecurity::escapeStr($_POST['mtpl']);
                $sql = "`tpl` = '$tpl'";
            break;
    		case 'keyword':
    			if($_POST['pattern']=='replace') {
    				$sql	="`keywords` = '".iSecurity::escapeStr($_POST['mkeyword'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($idArray AS $id){
                        $keywords = iDB::value("SELECT keywords FROM `#iCMS@__tags` WHERE `id`='$id'");
                        $sql      ="`keywords` = '".($keywords?$keywords.','.iSecurity::escapeStr($_POST['mkeyword']):iSecurity::escapeStr($_POST['mkeyword']))."'";
				        iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iUI::success('关键字更改完成!','js:1');
    			}
    		break;
    		case 'tag':
    			if($_POST['pattern']=='replace') {
    				$sql	="`related` = '".iSecurity::escapeStr($_POST['mtag'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($idArray AS $id){
		        		$keywords	= iDB::value("SELECT related FROM `#iCMS@__tags` WHERE `id`='$id'");
		        		$sql		="`related` = '".($keywords?$keywords.','.iSecurity::escapeStr($_POST['mtag']):iSecurity::escapeStr($_POST['mtag']))."'";
				        iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iUI::success('相关标签更改完成!','js:1');
    			}
    		break;
    		default:
                if(strpos($batch, ':')){
                    $data = admincp::update_args($batch);
                    foreach($idArray AS $id) {
                        $data && iDB::update("tags",$data,array('id'=>$id));
                    }
                    iUI::success('操作成功!','js:1');
                }else{
                    iUI::alert('请选择要操作项!','js:1');
                }

		}
        $sql && iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id` IN ($ids)");
		iUI::success('操作成功!','js:1');
	}
}
