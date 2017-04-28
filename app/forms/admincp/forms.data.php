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
$("#<?php echo APP_FORMID;?>").batch();
});
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title">
      <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <input type="hidden" name="do" value="form_manage" />
        <input type="hidden" name="form_id" value="<?php echo $this->form_id;?>" />
        <div class="input-prepend input-append">
          <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span>
        </div>
        <div class="input-prepend input-append">
          <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title">
      <span class="icon">
        <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <input type="hidden" name="form_id" value="<?php echo $this->form_id;?>" />
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th style="width:20px;"><?php echo strtoupper($primary); ?></th>
              <th>表单内容</th>
            </tr>
          </thead>
          <tbody>
          <?php
            foreach ((array)$rs as $key => $value) {
              $id = $value[$primary];
          ?>
            <tr id="tr<?php echo $id; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $id ; ?>" /></td>
              <td><?php echo $id ; ?></td>
              <td>
                <table class="table table-bordered">
                  <tbody>
                    <?php foreach ($fields as $fi => $field) {?>
                    <tr>
                      <td class="span3"><?php echo $field['label'] ; ?></td>
                      <td><?php echo $value[$field['id']] ; ?></td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
                <div class="clearfloat mb5"></div>
                <a href="<?php echo APP_URI; ?>&do=submit&form_id=<?php echo $this->form_id ; ?>&id=<?php echo $id ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                <a href="<?php echo APP_FURI; ?>&do=delete&form_id=<?php echo $this->form_id ; ?>&id=<?php echo $id ; ?>" target="iPHP_FRAME" class="del btn btn-small btn-danger" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a>
              </td>
            </tr>
            <?php }  ?>
          </tbody>
          <tr>
            <td colspan="<?php echo count($list_fields)+3;?>">
              <div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
              <div class="input-prepend input-append mt20">
                <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                </span>
                <div class="btn-group dropup" id="iCMS-batch">
                  <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a>
                  <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                    <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu">
                    <li class="divider"></li>
                    <li><a data-toggle="batch" data-action="data-dels"><i class="fa fa-trash-o"></i> 删除</a></li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
