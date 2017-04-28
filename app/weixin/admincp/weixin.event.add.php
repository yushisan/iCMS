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
admincp::head();
?>
<script type="text/javascript">
$(function(){
  var msgArray = <?php echo $rs['msg']?$rs['msg']:'null'; ?>;

  $("#eventype").bind('chosen:updated change',function(event) {
    eventype_change (this);
  });
  $("#msgtype").bind('chosen:updated change',function(event) {
    msgtype_change (this);
  });
  function eventype_change (a) {
    var data_type = $('option:selected',a).parent().attr('data-type');
    var select = $('option:selected',a).val();
    var text = $('option:selected',a).text();
    $("#operator_input").hide();
    if(data_type=="keyword"){
      $("#eventkey_input .add-on").text('关键词');
      $("#eventkey_input .help-inline").text('用户输入的关键词');
      $("#operator_input").show();
    }else if(data_type=="system"){
      $("#eventkey_input .help-inline").text('关键词');
      $("#eventkey_input .help-inline").text('此类型事件可不添写');
      $("#name").val(text);
      $("#eventkey").val(select);
    }else{
      if(select=='view'){
        $("#eventkey_input .add-on").text('跳转URL');
        $("#eventkey_input .help-inline").text('设置跳转URL');
      }else{
        $("#eventkey_input .add-on").text('事件KEY值');
        $("#eventkey_input .help-inline").text('事件KEY值，与自定义菜单接口中KEY值对应');
      }
    }
  }
  function msg(msgArray,keyArray,name) {
    $.each(msgArray, function(index, value) {
        if(typeof(value)==='object'){
          keyArray.push('['+index+']');
          msg(value,keyArray,name);
          keyArray = [];
        }else if(typeof(value)==='string'){
          var key  = keyArray.join('')+'['+index+']';
          if(name){
            var kname = '[name="msg['+name+']'+key+'"]';
          }else{
            var kname = '[name="msg'+key+'"]';
          }
          console.log(kname);
          $(kname).val(value);
        }
    });
    // console.log(keyArray);
  }
  function msgtype_change (a) {
    var select = $('option:selected',a).val();
    var clone = $('#msg-'+select).clone(true).show();
    $('#msg').html(clone);
    if(select=='news'){
      $('#msg').html('<a name="additem" class="btn btn-inverse">添加一条</a>');
      if(msgArray){
        $.each(msgArray['Articles'], function(index, value) {
          articles_item();
        });
        msg(msgArray['Articles'],[],'Articles');
      }else{
        articles_item();
      }
    }else{
        if(msgArray){
          msg(msgArray,[]);
        }
    }
  }
  $("#msg").on("click",'a[name="additem"]',function(event) {
    event.preventDefault();
    articles_item();
  });
  $("#msg").on("click",'a[name="delitem"]',function(event) {
    event.preventDefault();
    $(this).parent().remove();
  });
  $("#iCMS-event").submit(function(){
      var eventype = $("#eventype option:selected").attr("value");
      if(eventype=="0"){
        iCMS.alert("请选择事件类型");
        $("#eventype").focus();
        return false;
      }
      if($("#name").val()==''){
        iCMS.alert("请填写事件名称!");
        $("#name").focus();
        return false;
      }
      if($("#eventkey").val()==''){
        iCMS.alert("请填写事件KEY值!");
        $("#eventkey").focus();
        return false;
      }
      if(eventype=="keyword"){
        if($("#operator option:selected").attr("value")=="0"){
          iCMS.alert("请选择关键词匹配模式");
          $("#operator").focus();
          return false;
        }
      }
      if($("#msgtype option:selected").attr("value")=="0"){
        iCMS.alert("请选择回复消息的类型");
        $("#msgtype").focus();
        return false;
      }
  });
  iCMS.select('eventype',"<?php echo $rs['eventype'] ; ?>");
  iCMS.select('msgtype',"<?php echo $rs['msgtype'] ; ?>");
  iCMS.select('operator',"<?php echo $rs['operator'] ; ?>");
});
function articles_item(){
  var length = $('#msg .articles_item').length+1;
  var clone  = $('#msg-news .articles_item').clone(true);
  var dkey   = $(".articles_item:last",'#msg').attr('data-key');
  if(length>10){
    iCMS.alert("图文信息最多只能添加10条");
    return false;
  }
  if(typeof dkey=="undefined"){
    key = 0;
  }else{
    key = parseInt(dkey)+1;
  }
  var html = clone[0].outerHTML.replace(/\{KEY\}/g,key);
  $('#msg').append(html);
}
function modal_media (e,a) {
  if(!e) return;
  if(!a.checked) return;

  $('#'+e,$('#msg')).val(a.value);
  return 'off';
}
function modal_tplfile(e,a){
  if(!e) return;
  if(!a.checked) return;

  var val = a.value.replace(iCMS.config.DEFTPL+'/', "{iTPL}/");
  $('#'+e,$('#msg')).val(val);
  return 'off';
}
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title">
      <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($id)?'添加':'修改' ; ?>事件</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=event_save" method="post" class="form-inline" id="iCMS-event" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $id ; ?>" />
        <div id="event-add" class="tab-content">
          <div class="input-prepend">
            <span class="add-on">属性</span>
            <select name="pid" id="pid" class="chosen-select span3">
              <option value="0">普通事件[pid='0']</option>
              <?php echo propAdmincp::get("pid",$rs['pid']) ; ?>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend">
            <span class="add-on">状态</span>
            <select name="status" id="status" class="chosen-select span3">
              <option value="1">正常[status='1']</option>
              <option value="0">草稿[status='0']</option>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend">
            <span class="add-on">事件类型</span>
            <select name="eventype" id="eventype" class="span5 chosen-select" data-placeholder="请选择事件类型...">
              <option value='0'></option>
              <optgroup label="用户消息" data-type="keyword">
                <option value='keyword'>关键词</option>
              </optgroup>
              <optgroup label="菜单事件" data-type="event">
                <option value='click'>点击事件</option>
                <option value='view'>跳转链接</option>
                <option value='scancode_push'>扫码推事件</option>
                <option value='scancode_waitmsg'>扫码带提示</option>
                <option value='pic_sysphoto'>系统拍照发图</option>
                <option value='pic_photo_or_album'>拍照或者相册发图</option>
                <option value='pic_weixin'>微信相册发图器</option>
                <option value='location_select'>地理位置选择器</option>
              </optgroup>
              <optgroup label="系统事件" data-type="system">
                <option value='subscribe'>关注</option>
                <option value='unsubscribe'>取消关注</option>
                <option value='location'>上报地理位置</option>
              </optgroup>
            </select>
          </div>
          <span class="help-inline">如无法使用,请确认是否需要公众号认证</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend">
            <span class="add-on">事件名称</span>
            <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>"/>
          </div>
          <span class="help-inline">事件中文名称</span>
          <div class="clearfloat mb10"></div>
          <div id="eventkey_input">
            <div class="input-prepend">
              <span class="add-on">事件KEY值</span>
              <input type="text" name="eventkey" class="span6" id="eventkey" value="<?php echo $rs['eventkey'] ; ?>"/>
            </div>
            <span class="help-inline">事件KEY值，与自定义菜单接口中KEY值或回复的关键词对应</span>
            <div class="clearfloat mb10"></div>
          </div>
          <div id="operator_input" class="hide">
            <div class="input-prepend">
              <span class="add-on">匹配模式</span>
              <select name="operator" id="operator" class="span3 chosen-select" data-placeholder="请选择关键词匹配模式...">
                <option value='0'></option>
                <option value='eq'>完全匹配</option>
                <option value='in'>包含关键词</option>
                <option value='re'>正则</option>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
          </div>
          <div class="input-prepend">
            <span class="add-on">回复类型</span>
            <select name="msgtype" id="msgtype" class="span3 chosen-select" data-placeholder="请选择回复消息的类型...">
              <option value='0'></option>
              <option value='text'>文本消息</option>
              <option value='image'>图片消息</option>
              <option value='voice'>语音消息</option>
              <option value='video'>视频消息</option>
              <option value='music'>音乐消息</option>
              <option value='news'>图文消息</option>
              <option value='tpl'>调用模板</option>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div id="msg"></div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div style="display:none;" id="msg-text">
  <div class="input-prepend">
    <span class="add-on">消息内容</span>
    <textarea name="msg[Text]" id="msg_Text" class="span6" style="height: 150px;"></textarea>
  </div>
  <span class="help-inline">回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）</span>
</div>
<div style="display:none;" id="msg-image">
  <div class="input-prepend input-append">
    <span class="add-on">MediaId</span>
    <input type="text" name="msg[Image][MediaId]" class="span6" id="msg_Image_MediaId" value=""/>
    <?php echo self::modal_btn('素材','msg_Image_MediaId','image');?>
  </div>
  <span class="help-inline">通过上传多媒体文件，得到的id。</span>
</div>
<div style="display:none;" id="msg-voice">
  <div class="input-prepend input-append">
    <span class="add-on">MediaId</span>
    <input type="text" name="msg[Voice][MediaId]" class="span6" id="msg_Voice_MediaId" value=""/>
    <?php echo self::modal_btn('素材','msg_Voice_MediaId','voice');?>
  </div>
  <span class="help-inline">通过上传多媒体文件，得到的id。</span>
</div>
<div style="display:none;" id="msg-video">
  <div class="input-prepend input-append">
    <span class="add-on">MediaId</span>
    <input type="text" name="msg[Video][MediaId]" class="span6" id="msg_Video_MediaId" value=""/>
    <?php echo self::modal_btn('素材','msg_Video_MediaId','video');?>
  </div>
  <span class="help-inline">通过上传多媒体文件，得到的id。</span>
  <div class="clearfloat mt10"></div>
  <div class="input-prepend">
    <span class="add-on">视频标题</span>
    <input type="text" name="msg[Video][Title]" class="span6" id="msg_Video_Title" value=""/>
  </div>
  <span class="help-inline">视频消息的标题</span>
  <div class="clearfloat mt10"></div>
  <div class="input-prepend">
    <span class="add-on">视频描述</span>
    <textarea name="msg[Video][Description]" id="msg_Video_Description" class="span6" style="height: 150px;"></textarea>
  </div>
  <span class="help-inline">视频消息的描述</span>
</div>
<div style="display:none;" id="msg-music">
  <div class="input-prepend">
    <span class="add-on">音乐标题</span>
    <input type="text" name="msg[Music][Title]" class="span6" id="msg_Music_Title" value=""/>
  </div>
  <span class="help-inline">音乐标题</span>
  <div class="clearfloat mt10"></div>
  <div class="input-prepend">
    <span class="add-on">音乐描述</span>
    <textarea name="msg[Music][Description]" id="msg_Music_Description" class="span6" style="height: 150px;"></textarea>
  </div>
  <span class="help-inline">音乐描述</span>
  <div class="clearfloat mt10"></div>
  <div class="input-prepend">
    <span class="add-on">音乐链接</span>
    <input type="text" name="msg[Music][MusicUrl]" class="span6" id="msg_Music_MusicUrl" value=""/>
  </div>
  <span class="help-inline">音乐链接</span>
  <div class="clearfloat mt10"></div>
  <div class="input-prepend">
    <span class="add-on">高质量</span>
    <input type="text" name="msg[Music][HQMusicUrl]" class="span6" id="msg_Music_HQMusicUrl" value=""/>
  </div>
  <span class="help-inline">高质量音乐链接，WIFI环境优先使用该链接播放音乐</span>
  <div class="clearfloat mt10"></div>
  <div class="input-prepend input-append">
    <span class="add-on">缩略图</span>
    <input type="text" name="msg[Music][ThumbMediaId]" class="span6" id="msg_Music_ThumbMediaId" value=""/>
    <?php echo self::modal_btn('素材','msg_Music_ThumbMediaId','voice');?>
  </div>
  <span class="help-inline">缩略图的媒体id，通过上传多媒体文件，得到的id</span>
</div>
<div style="display:none;" id="msg-news">
  <div class="articles_item" data-key="{KEY}">
    <hr />
    <div class="input-prepend">
      <span class="add-on">图文标题</span>
      <input type="text" name="msg[Articles][{KEY}][item][Title]" class="span6" id="msg_Articles_{KEY}_item_Title" value=""/>
    </div>
    <span class="help-inline">图文标题</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
      <span class="add-on">图文描述</span>
      <textarea name="msg[Articles][{KEY}][item][Description]" id="msg_Articles_{KEY}_item_Description" class="span6" style="height: 150px;"></textarea>
    </div>
    <span class="help-inline">图文描述</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
      <span class="add-on">图片链接</span>
      <input type="text" name="msg[Articles][{KEY}][item][PicUrl]" class="span6" id="msg_Articles_{KEY}_item_PicUrl" value=""/>
    </div>
    <span class="help-inline">图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
      <span class="add-on">图文链接</span>
      <input type="text" name="msg[Articles][{KEY}][item][Url]" class="span6" id="msg_Articles_{KEY}_item_Url" value=""/>
    </div>
    <span class="help-inline">点击图文消息跳转链接</span>
    <div class="clearfloat mt10"></div>
    <a name="delitem" class="btn btn-danger">删除</a>
  </div>
</div>
<div style="display:none;" id="msg-tpl">
  <div class="input-prepend input-append"> <span class="add-on">API模板</span>
    <input type="text" name="msg[Tpl]" class="span3" id="msg_Tpl" />
    <?php echo filesAdmincp::modal_btn('模板','msg_Tpl','file','tplfile');?>
  </div>
  <span class="help-inline">可在模板中调用各应用数据</span>
</div>
<?php admincp::foot();?>
