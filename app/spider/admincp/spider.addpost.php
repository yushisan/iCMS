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
  iCMS.select('app',"<?php echo $rs['app'] ; ?>");
});

</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->poid)?'添加':'修改' ; ?>发布模块</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=savepost" method="post" class="form-inline" id="iCMS-spider" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->poid ; ?>" />
        <div id="addpost" class="tab-content">
          <div class="input-prepend"><span class="add-on">应用</span>
            <select name="app" id="app" class="chosen-select span3">
              <option value="0"></option>
              <option value="article"> 文章系统 </option>
              <option value="tags"> 标签系统 </option>
              <option value="category"> 栏目系统 </option>
              <option value="pushcategory"> 推荐分类系统 </option>
              <option value="tagscategory"> 标签分类系统 </option>
              <option value="push"> 推荐系统 </option>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">名称</span>
            <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name']; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">接口</span>
            <input type="text" name="fun" class="span6" id="fun" value="<?php echo $rs['fun']?$rs['fun']:'do_save'; ?>"/>
          </div>
          <span class="help-inline">可使用URL 远程发布</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">发布项</span>
            <textarea name="post" id="post" class="span6" style="height: 90px;"><?php echo $rs['post'] ; ?></textarea>
          </div>
          <div class="clearfloat mb10"></div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
