<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
defined('iPHP') OR exit('What are you doing?');

class weixinAdmincp{
    public function __construct() {
        $this->appid  = iCMS_APP_WEIXIN;
        $this->config = configAdmincp::get($this->appid,admincp::$APP_NAME);
        $this->config['component']==="1" && weixin::$component = true;
        weixin::$config = $this->config;
    }
    public function do_config(){
        configAdmincp::app($this->appid);
    }
    public function do_save_config(){
        $_POST['config'] = array_merge((array)$this->config,(array)$_POST['config']);
        configAdmincp::save($this->appid);
    }
    /**
     * [自定义菜单]
     * @return [type] [description]
     */
    public function do_save_menu(){
        $this->config['menu'] = $_POST['wx_button'];
        $this->do_save_config();
    }
    /**
     * [同步菜单]
     * @return [type] [description]
     */
    public function do_rsync_menu(){
        weixin::init();
        $response = weixin::setMenu();
        if(empty($response->errcode)){
            iUI::success('同步成功');
        }else{
            iUI::alert('同步出错 <br />errcode:"'.$response->errcode.'" errmsg:"'.$response->errmsg.'"');
        }
    }
    /**
     * [菜单管理]
     * @return [type] [description]
     */
    public function do_menu(){
        include admincp::view("weixin.menu");
    }
    /**
     * [第三方平台]
     * @return [type] [description]
     */
    public function do_component_login(){
        $token = iSecurity::escapeStr($_GET['token']);
        if($token!=$this->config['token']){
            iUI::alert("Token(令牌)出错！请先保存Token(令牌)配置！",'js:window.iCMS_MODAL.destroy();');
        }
        $url = iCMS_WEIXIN_COMPONENT.'/iCMS/login?'.
        'token='.$token.
        '&url='.urlencode(iCMS::$config['router']['public']);
        iPHP::redirect($url);
    }
    public function do_media(){
        weixin::init();
        $from     = $_GET['from'];
        $type     = $_GET['type'];
        $callback = $_GET['callback'];
        $target   = $_GET['target'];

        $rs   = weixin::mediaList($type);
        $navbar = false;
        include admincp::view("weixin.media");
    }
    /**
     * [事件管理]
     * @return [type] [description]
     */
    public function do_event(){
        $sql = " where ";
        switch($doType){ //status:[0:草稿][1:正常][2:回收]
            case 'inbox'://草稿
                $sql.="`status` ='0'";
            break;
            case 'trash'://回收站
                $sql.="`status` ='2'";
            break;
            default:
                $sql.=" `status` ='1'";
        }

        if($_GET['keywords']) {
            $sql.=" AND `keyword` REGEXP '{$_GET['keywords']}'";
        }

        $_GET['starttime']   && $sql.=" and `addtime`>=UNIX_TIMESTAMP('".$_GET['starttime']." 00:00:00')";
        $_GET['endtime']     && $sql.=" and `addtime`<=UNIX_TIMESTAMP('".$_GET['endtime']." 23:59:59')";

        $orderby    =$_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__weixin_event` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个事件");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__weixin_event` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        // var_dump(iDB::$last_query);
        $_count = count($rs);

        include admincp::view("weixin.event");
    }
    /**
     * [添加事件]
     * @return [type] [description]
     */
    public function do_event_add(){
        $id = (int)$_GET['id'];
        if($id) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__weixin_event` WHERE `id`='$id' LIMIT 1;",ARRAY_A);
            // if($rs['msgtype']!='text'){
            //   $rs['msg'] = json_decode($rs['msg'],true);
            // }
        }
        include admincp::view("weixin.event.add");
    }
    /**
     * [保存事件]
     * @return [type] [description]
     */
    public function do_event_save(){
        $id       = (int)$_POST['id'];
        $pid      = $_POST['pid'];
        $eventype = $_POST['eventype'];
        $name     = $_POST['name'];
        $eventkey = $_POST['eventkey'];
        $operator = $_POST['operator'];
        $msgtype  = $_POST['msgtype'];
        $msg      = $_POST['msg'];
        $status   = $_POST['status'];

        $eventype OR iUI::alert("请选择事件类型");
        $name OR iUI::alert("请填写事件名称");
        $eventkey OR iUI::alert("请填写事件KEY值");
        if($eventype=="keyword"){
            $operator OR iUI::alert("请选择关键词匹配模式");
        }
        $msgtype OR iUI::alert("请选择回复消息的类型");
        $msg OR iUI::alert("请填写回复内容");

        $msg     = stripslashes_deep($msg);
        $msg     = addslashes(json_encode($msg));
        $addtime = time();
        $fields  = array('pid', 'name', 'eventype', 'eventkey', 'msgtype', 'operator', 'msg', 'addtime', 'status');
        $data    = compact ($fields);
        if(empty($id)) {
            iDB::value("SELECT `id` FROM `#iCMS@__weixin_event` where `eventkey` ='$eventkey'") && iUI::alert('该事件已经存在!');
            iDB::insert('weixin_event',$data);
            iUI::success('添加完成','url:'.APP_URI.'&do=event');
        }else{
            iDB::update('weixin_event', $data, array('id'=>$id));
            iUI::success('更新完成','url:'.APP_URI.'&do=event');
        }
    }
    public function do_event_del($id = null,$dialog=true){
      $id===null && $id=$_GET['id'];
      $id OR iUI::alert('请选择要删除的事件!');
      iDB::query("DELETE FROM `#iCMS@__weixin_event` WHERE `id` = '$id';");
      $dialog && iUI::success("已经删除!",'url:'.APP_URI.'&do=event');
    }
    public function menu_get_type($type,$out='value'){
      $type_map = array(
        'click'              =>array('key','点击事件'),
        'view'               =>array('url','跳转URL'),
        'miniprogram'        =>array('url','小程序'),
        'scancode_push'      =>array('key','扫码推事件'),
        'scancode_waitmsg'   =>array('key','扫码带提示'),
        'pic_sysphoto'       =>array('key','系统拍照发图'),
        'pic_photo_or_album' =>array('key','拍照或者相册发图'),
        'pic_weixin'         =>array('key','微信相册发图器'),
        'location_select'    =>array('key','地理位置选择器'),
        'media_id'           =>array('media_id','素材(第三方)'),
        'view_limited'       =>array('media_id','图文(第三方)')
      );

      if($out=='value'){
        empty($type) && $type='click';
        return $type_map[$type][0];
      }
      if($out=='opt'){
        $option = '';
        foreach ($type_map as $key => $value) {
          $seltext = '';
          if($type==$key){
            $seltext =' selected="selected"';
          }
          $option.='<option value="'.$key.'"'.$seltext.'>'.$value[1].'</option>';
        }
        return $option;
      }
    }
    public function menu_button_li($key='~KEY~',$i='~i~',$a=array()){
      $keyname = $this->menu_get_type($a['type']);
      $html = '<li>'.
        '<div class="input-prepend input-append">'.
          '<span class="add-on">类型</span>'.
          '<select name="wx_button['.$key.'][sub_button]['.$i.'][type]">'.
            $this->menu_get_type($a['type'],'opt').
          '</select>'.
          '<span class="add-on">名称</span>'.
          '<input type="text" class="span2" name="wx_button['.$key.'][sub_button]['.$i.'][name]" value="'.$a['name'].'">'.
          '<span class="button_key">'.
            '<span class="add-on">'.strtoupper($keyname).'</span>'.
            '<input type="text" name="wx_button['.$key.'][sub_button]['.$i.']['.$keyname.']" value="'.$a[$keyname].'">';
      if($a['appid']){
        $html.= '<span class="add-on">APPID</span>'.
                '<input type="text" name="wx_button['.$key.'][sub_button]['.$i.'][appid]" value="'.$a['appid'].'">';
      }
      if($a['pagepath']){
        $html.= '<span class="add-on">PAGEPATH</span>'.
                '<input type="text" name="wx_button['.$key.'][sub_button]['.$i.'][pagepath]" value="'.$a['pagepath'].'">';
      }

      $html.= '</span>'.
          '<a href="javascript:void(0);" class="btn wx_del_sub_button"><i class="fa fa-del"></i>删除</a>'.
        '</div>'.
      '</li>';
      return $html;
    }
    public static function modal_btn($title='',$target='MediaId',$type='news',$callback='media',$do='media',$from='modal'){
        $href   = APP_URI."&do={$do}&type={$type}&from={$from}&target={$target}&callback={$callback}";
        $_title = $title.'文件';
        return '<a href="'.$href.'" class="btn media_modal" data-toggle="modal" title="选择'.$_title.'"><i class="fa fa-search"></i> 选择</a>';
    }
    public function do_save(){
        iUI::success('更新完成');
    }
    public function cache(){
    }
}
