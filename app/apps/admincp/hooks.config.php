<?php /**
* @package iCMS
* @copyright 2007-2017, iDreamSoft
* @license http://www.idreamsoft.com iDreamSoft
* @author coolmoo <idreamsoft@qq.com>
*/
defined('iPHP') OR exit('What are you doing?');
admincp::head();
?>
<div class="hide hooks_item">
  <div class="input-prepend input-append">
    <span class="add-on">应用</span>
    <select name="hooks[app][]" class="H_app span3">
      <option value="">.....请先选择应用.....</option>
      <?php echo apps_hook::app_select();?>
    </select>
    <span class="add-on">字段</span>
    <select name="hooks[field][]" class="H_field span3">
    </select>
    <span class="add-on">方法</span>
    <select name="hooks[method][]" class="H_method span3">
    <?php
      $hook_method = apps_hook::app_method();
      echo $hook_method?$hook_method:'<option value="">暂无可用方法</option>';
    ?>
    </select>
    <span class="add-on"><a class="del_hooks" href="javascript:;"><i class="fa fa-times"></i></a></span>
  </div>
  <div class="clearfloat mb10"></div>
</div>

<script type="text/javascript">
$(function(){
$("#<?php echo APP_FORMID;?>").batch();
});
</script>

<script>
function hooks_item_clone(a,f,m) {
  var set_field = function  (hooks_item,f,html) {
      $(".H_field",hooks_item).html(html);
      if(f){
        $(".H_field",hooks_item).val(f)
      }
      $(".H_field",hooks_item).trigger("chosen:updated");
  }
  var hooks_item = $(".hooks_item").clone(true);
  hooks_item.removeClass('hide hooks_item');
  hooks_item.on('chosen:updated change', '.H_app', function(event) {
    event.preventDefault();
    var me = this;
    $(".H_field",hooks_item).html('<option value="">加载中....请稍候!</option>');
      $.get("<?php echo APP_URI; ?>&do=hook_app_field_opt&_app="+this.value,
        function(html){
          set_field(hooks_item,f,html);
        }
      );
  }).on('click', '.del_hooks', function(event) {
    event.preventDefault();
    var ppp = $(this).parent().parent().parent();
    ppp.remove();
  });
  if(a){
    $(".H_app",hooks_item).val(a).trigger("chosen:updated");
  }
  if(m){
    $(".H_method",hooks_item).val(m).trigger("chosen:updated");
  }
  $(".hooks_container").append(hooks_item)
  $("select",hooks_item).chosen(chosen_config);
}
</script>

<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title">
      <span class="icon">
        <i class="fa fa-bank"></i> <span>应用钩子管理</span>
      </span>
      <span class="icon">
        <a class="add_hooks" href="javascript:;" title="添加钩子"><i class="fa fa-plus-square"></i></a>
      </span>
    </div>
    <div class="widget-content">
      <form action="<?php echo APP_FURI; ?>&do=save_hooks" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
            <div class="hooks_container"></div>
<script>
<?php
  foreach ((array)$config as $H_app => $hooks) {
    foreach ((array)$hooks as $H_field => $H_method) {
      foreach ($H_method as $key => $_method) {
        echo 'hooks_item_clone("'.$H_app.'","'.$H_field.'","'.implode('::', $_method).'");';
      }
    }
  }
?>
$(function(){
  $(".add_hooks").click(function(event) {
      event.preventDefault();
      hooks_item_clone();
  });
})
</script>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php admincp::foot();?>
