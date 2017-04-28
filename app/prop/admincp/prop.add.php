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
iCMS.select('cid',"<?php echo $rs['cid'] ; ?>");
iCMS.select('prop-app',"<?php echo $rs['app'] ; ?>");
});
</script>
<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->pid)?'添加':'修改' ; ?>属性</b></h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-prop" target="iPHP_FRAME">
        <input name="pid" type="hidden" value="<?php echo $this->pid ; ?>" />
        <input name="appid" type="hidden" id="appid" value="<?php echo $rs['appid'];?>"/>
        <div id="<?php echo APP_BOXID;?>" class="tab-content">
          <div class="input-prepend"> <span class="add-on">所属栏目</span>
            <select name="cid" id="cid" class="span3 chosen-select">
              <option value="0"> ==== 暂无所属栏目 ==== </option>
              <?php echo category::priv('ca')->select($rs['cid'],0,1,true);?>
            </select>
          </div>
          <span class="help-inline">本属性所属的栏目</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">所属应用</span>
            <select name="app" id="prop-app" class="span3 chosen-select">
              <option value=""> ==== 暂无所属应用 ==== </option>
              <?php foreach (apps::get_array(array("!table"=>0)) as $key => $value) {?>
              <option value="<?php echo $value['app'];?>" appid="<?php echo $key;?>"><?php echo $value['app'];?>:<?php echo $value['name'];?></option>
              <?php }?>
            </select>
          </div>
          <script>
            $("#prop-app").on('change', function(evt, params) {
              var appid = $(this).find('option[value="' + params['selected'] + '"]').attr('appid');
              $("#appid","#iCMS-prop").val(appid);
            });
          </script>
          <span class="help-inline">article:文章 push:推送 category:栏目</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend input-append"> <span class="add-on">属性字段</span>
            <input type="text" name="field" class="span4" id="field" value="<?php echo $rs['field'];?>"/>
            <div class="btn-group">
              <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span> 选择</a>
              <ul class="dropdown-menu">
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">文章:属性:pid</a></li>
                <li><a href="javascript:;" data-value='author' data-toggle="insert" data-target="#field">文章:作者:author</a></li>
                <li><a href="javascript:;" data-value='source' data-toggle="insert" data-target="#field">文章:出处:source</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">推送:属性:pid</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">栏目:属性:pid</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">标签:属性:pid</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">用户:属性:pid</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">推送:属性:pid</a></li>
              </ul>
            </div>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属性名称</span>
            <textarea name="name" id="name" class="span6" style="height: 150px;width:600;"><?php echo $rs['name'] ; ?></textarea>
          </div>
          <span class="help-inline">可填写中文 <span class="label label-important">可批量添加,每行一个</span></span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属 性 值</span>
            <input type="text" name="val" class="span4" id="val" value="<?php echo $rs['val'];?>"/>
          </div>
          <span class="help-inline">pid:只能填写数字,批量添加时将按序号填写</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">排序</span>
            <input type="text" name="sortnum" class="span2" id="sortnum" value="<?php echo $rs['sortnum'];?>"/>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 添加</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
