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
	iCMS.select('tcid',"<?php echo $rs['tcid'] ; ?>");
	iCMS.select('pid',"<?php echo $rs['pid']?$rs['pid']:0 ; ?>");
	iCMS.select('status',"<?php echo $rs['status'] ; ?>");
	$("#<?php echo APP_FORMID;?>").submit(function(){
		// if($("#cid option:selected").val()=="0"){
		// 	iCMS.alert("请选择所属栏目");
		// 	$("#cid").focus();
		// 	return false;
		// }
		if($("#name").val()==''){
			iCMS.alert("标签名称不能为空!");
			$("#name").focus();
			return false;
		}
	});
  $(document).on("click",".delprop",function(){
      $(this).parent().parent().remove();
  });
  $(".addprop").click(function(){
    var href = $(this).attr("href");
    var tb  = $(href),tbody=$("tbody",tb);
    var ntr=$(".aclone",tb).clone(true).removeClass("hide aclone");
    $('input',ntr).removeAttr("disabled");
    ntr.appendTo(tbody);
    return false;
  });
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-pencil"></i> </span>
      <h5 class="brs"><?php echo ($id?'添加':'修改'); ?>标签</h5>
      <ul class="nav nav-tabs" id="tag-add-tab">
        <li class="active"><a href="#tag-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <li><a href="#tag-add-custom" data-toggle="tab"><i class="fa fa-wrench"></i> 自定义</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->id ; ?>" />
        <input name="uid" type="hidden" value="<?php echo $rs['uid'] ; ?>" />

        <input name="_cid" type="hidden" value="<?php echo $rs['cid'] ; ?>" />
        <input name="_tcid" type="hidden" value="<?php echo $rs['tcid'] ; ?>" />
        <input name="_pid" type="hidden" value="<?php echo $rs['pid'] ; ?>" />

        <div id="tags-add" class="tab-content">
          <div id="tag-add-base" class="tab-pane active">
            <div class="input-prepend"> <span class="add-on">所属栏目</span>
              <select name="cid" id="cid" class="chosen-select span6" multiple="multiple" data-placeholder="请选择栏目(可多选)...">
                <option value="0"> ==== 默认栏目 ==== </option>
                <?php echo category::priv('ca')->select($rs['cid'],0,1,true);?>
              </select>
            </div>
            <span class="help-inline">本标签所属的栏目</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签分类</span>
              <select name="tcid[]" id="tcid" class="chosen-select span6" multiple="multiple" data-placeholder="请选择标签分类(可多选)...">
                <option value="0"> ==== 默认分类 ==== </option>
                <?php echo category::appid($this->appid,'ca')->select($rs['tcid'],0,1,true);?>
              </select>
            </div>
            <span class="help-inline">本标签所属的标签分类</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签属性</span>
              <select name="pid[]" id="pid" class="chosen-select span6" multiple="multiple" data-placeholder="请选择标签属性(可多选)...">
                <option value="0">普通标签[pid='0']</option>
                <?php echo propAdmincp::get("pid") ; ?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">父标签ID</span>
              <input type="text" name="rootid" class="span6" id="rootid" value="<?php echo $rs['rootid'] ; ?>"/>
            </div>
            <span class="help-inline">本标签所属的标签的ID,请自行填写ID</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签名称</span>
              <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">唯一标识</span>
              <input type="text" name="tkey" class="span6" id="tkey" value="<?php echo $rs['tkey'] ; ?>"/>
            </div>
            <span class="help-inline">用于伪静态或者静态生成 唯一性<br />
            留空则系统按名称拼音生成</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">SEO 标题</span>
              <input type="text" name="seotitle" class="span6" id="seotitle" value="<?php echo $rs['seotitle'] ; ?>" />
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">副 标 题</span>
              <input type="text" name="subtitle" class="span6" id="subtitle" value="<?php echo $rs['subtitle'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">关 键 字</span>
              <input type="text" name="keywords" class="span6" id="keywords" value="<?php echo $rs['keywords'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩 略 图</span>
              <input type="text" name="pic" class="span6" id="pic" value="<?php echo $rs['pic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("pic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">大图</span>
              <input type="text" name="bpic" class="span6" id="bpic" value="<?php echo $rs['bpic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("bpic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">中图</span>
              <input type="text" name="mpic" class="span6" id="mpic" value="<?php echo $rs['mpic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("mpic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">小图</span>
              <input type="text" name="spic" class="span6" id="spic" value="<?php echo $rs['spic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("spic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签描述</span>
              <textarea name="description" id="description" class="span6" style="height: 150px;width:600;"><?php echo $rs['description'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">自定义链接</span>
              <input type="text" name="url" class="span6" id="url" value="<?php echo $rs['url'] ; ?>"/>
            </div>
            <span class="help-inline">填写自定义链接后,标签唯一标识将会由系统生成</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">相关标签</span>
              <input type="text" name="related" class="span6" id="related" value="<?php echo $rs['related'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">标签模板</span>
              <input type="text" name="tpl" class="span3" id="tpl" value="<?php echo $rs['tpl'] ; ?>"/>
              <?php echo filesAdmincp::modal_btn('模板','tpl');?>
          </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签权重</span>
              <input type="text" name="weight" class="span3" id="weight" value="<?php echo $rs['weight']?$rs['weight']:time() ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签排序</span>
              <input id="sortnum" class="span3" value="<?php echo $rs['sortnum']?$rs['sortnum']:time() ; ?>" name="sortnum" type="text"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签状态</span>
              <div class="switch" data-on-label="启用" data-off-label="禁用">
                <input type="checkbox" data-type="switch" name="status" id="status" <?php echo $rs['status']?'checked':''; ?>/>
              </div>
            </div>
          </div>
          <div id="tag-add-custom" class="tab-pane hide">
          <?php echo former::layout();?>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
