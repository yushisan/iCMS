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
        <input type="hidden" name="do" value="manage" />
        <div class="input-prepend input-append">
          <span class="add-on">栏目</span>
          <select name="cid" id="cid" class="chosen-select" style="width: 230px;">
            <option value="0">所有栏目</option>
            <?php echo $category_select = category::priv('cs')->select() ; ?>
          </select>
          <span class="add-on">
            <input type="checkbox" name="sub" id="sub"/> 子栏目
          </span>
        </div>
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
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th style="width:20px;"><?php echo strtoupper($primary); ?></th>
              <?php foreach ($list_fields as $fi => $fkey) {?>
                <th><?php echo $fields[$fkey]['label'] ; ?></th>
              <?php }?>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
          <?php

            former::$callback = array(
              'category'       => array('category','get'),
              'multi_category' => array('category','get'),
              // 'prop'           => array('prop','get'),
              // 'multi_prop'     => array('prop','get'),
            );
            former::multi_value($rs,$fields);

            $categoryArray = former::$variable['cid'];

            foreach ((array)$rs as $key => $value) {
              $id            = $value[$primary];
              $category      = (array)$categoryArray[$value['cid']];
              $iurl          = iURL::get($this->app['app'],array($value,$category));
              $value['url']  = $iurl->href;
          ?>
            <tr id="tr<?php echo $id; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $id ; ?>" /></td>
              <td> <a href="<?php echo $value['url'] ; ?>" target="_blank"/> <?php echo $id ; ?></a></td>
              <?php foreach ($list_fields as $fi => $fkey) {?>
              <td><?php echo former::de_value($value[$fkey],$fields[$fkey]); ?></td>
              <?php }?>
              <td>
                <a href="<?php echo APP_URI; ?>&do=add&id=<?php echo $id ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $id ; ?>" target="iPHP_FRAME" class="del btn btn-small btn-danger" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a>
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
                    <li><a data-toggle="batch" data-action="dels"><i class="fa fa-trash-o"></i> 删除</a></li>
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
