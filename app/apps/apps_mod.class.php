<?php
/**
 * @package iCMS
 * @copyright 2007-2017, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */

class apps_mod {
    public static function data_table_name($name){
      return $name.'_data';
    }
    public static function data_union_id($name){
      return $name.'_id';
    }
    public static function base_fields_array(){
      $sql = implode(",\n", self::base_fields_sql());
      preg_match_all("@`(.+)`\s(.+)\sDEFAULT\s'(.*?)'\sCOMMENT\s'(.+)'@", $sql, $matches);
      return $matches;
    }
    public static function base_fields_sql(){
        return array(
            'cid'        =>"`cid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '栏目id'",
            'ucid'       =>"`ucid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户分类'",
            'pid'        =>"`pid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '属性'",
            'title'      =>"`title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '标题'",
            'editor'     =>"`editor` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '编辑名或用户名'",
            'userid'     =>"`userid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID'",
            'pubdate'    =>"`pubdate` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间'",
            'postime'    =>"`postime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '提交时间'",
            'tpl'        =>"`tpl` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '模板'",
            'hits'       =>"`hits` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总点击数'",
            'hits_today' =>"`hits_today` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '当天点击数'",
            'hits_yday'  =>"`hits_yday` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '昨天点击数'",
            'hits_week'  =>"`hits_week` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '周点击'",
            'hits_month' =>"`hits_month` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '月点击'",
            'favorite'   =>"`favorite` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '收藏数'",
            'comments'   =>"`comments` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '评论数'",
            'good'       =>"`good` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '顶'",
            'bad'        =>"`bad` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '踩'",
            'sortnum'    =>"`sortnum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序'",
            'weight'     =>"`weight` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '权重'",
            'creative'   =>"`creative` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '内容类型 0:转载;1:原创'",
            'mobile'     =>"`mobile` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布设备 0:pc;1:手机'",
            'postype'    =>"`postype` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布类型 0:用户;1管理员'",
            'status'     =>"`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态 0:草稿;1:正常;2:回收;3:审核;4:不合格'",
        );
    }
    public static function base_fields_index(){
        return array(
            'index_id'         =>'KEY `id` (`status`,`id`)',
            'index_hits'       =>'KEY `hits` (`status`,`hits`)',
            'index_pubdate'    =>'KEY `pubdate` (`status`,`pubdate`)',
            'index_hits_week'  =>'KEY `hits_week` (`status`,`hits_week`)',
            'index_hits_month' =>'KEY `hits_month` (`status`,`hits_month`)',
            'index_cid_hits'   =>'KEY `cid_hits` (`status`,`cid`,`hits`)'
        );
    }
    public static function base_fields_key($key=null){
        $array = array('id','cid','ucid','pid','sortnum',
            'title','editor','userid','pubdate','postime','tpl','hits',
            'hits_today','hits_yday','hits_week','hits_month',
            'favorite','comments','good','bad','creative',
            'weight','mobile','postype','status'
        );
        if($key){
            return in_array($key, $array);
        }
        return $array;
    }
    public static function base_fields($name=null,$primary='data_') {
      $a[$primary.'id'] = "id=".$primary."id&label=附加表id&comment=主键%20自增ID&field=PRIMARY&name=".$primary."id&default=&type=PRIMARY&len=10&";
      if($name){
        $union_id = $name.'_id';
        $a[$union_id] = "id=".$union_id."&label=关联内容ID&comment=内容ID%20关联".$name."表&field=INT&name=".$union_id."&default=&type=union&len=10";
      }
      return $a;
    }
    public static function json_field($json=null){
        if(empty($json)) return array();

        $fieldata    = json_decode(stripcslashes($json),true);
        //QS转数组
        $field_array = apps_mod::get_field_array($fieldata);

        $json_array  = array();
        foreach ($field_array as $key => $value) {
            $a = array();
            foreach ($value as $k => $v) {
                if(in_array($k, array('field','label','name','default','len','comment','unsigned'))){
                    $a[$k] = $v;
                }
            }
            ksort($a);
            $json_array[$key] = json_encode($a);
        }

        // $json_array  = array_map('json_encode', $field_array);

        // $json_array = array();
        // foreach ($array as $key => $value) {
        //     $json_array[$key] = json_encode($value);
        // }

        return $json_array;
    }
    public static function drop_table($addons_fieldata,&$table_array,$addons_name) {
      if(empty($addons_fieldata) && $table_array[$addons_name] && apps_db::check_table(iDB::table($addons_name))){
        apps_db::drop_tables(array(iPHP_DB_PREFIX.$addons_name));
        unset($table_array[$addons_name]);
      }
    }
    public static function find_MEDIUMTEXT(&$json_field) {
        $addons_json_field = array();
        foreach ($json_field as $key => $value) {
            $a = json_decode($value,true);
            if(strtoupper($a['field'])=="MEDIUMTEXT"){
              $addons_json_field[$key] = $value;
              unset($json_field[$key]);//不参与基本表比较
            }
        }
        return $addons_json_field;
    }
    /**
     * 创建xxx_data附加表
     * @param  [type] $fieldata [description]
     * @param  [type] $name     [description]
     * @return [type]           [description]
     */
    // public static function data_create_table($fieldata,$name,$union_id) {
    //     $table = apps_db::create_table(
    //       $name,
    //       apps_mod::get_field_array($fieldata),//获取字段数组
    //       false,'data_id',true
    //     );
    //     array_push ($table,$union_id,'正文');
    //     return array($name=>$table);
    // }
    public static function data_create_table($fieldata,$name,$union_id) {
        $table = apps_db::create_table(
          $name,
          apps_mod::get_field_array($fieldata),//获取字段数组
          array(//索引
            'index_'.$union_id =>'KEY `'.$union_id.'` (`'.$union_id.'`)'
          )
        );
        array_push ($table,$union_id,'正文');
        return array($name=>$table);
    }
    /**
     * 将由查询字符串(query string)组成的数组转换成二维数组
     * @param  [type]  $data [查询字符串 数组]
     * @param  boolean $ui   [是否把UI标识返回数组]
     * @return [type]        [description]
     */
    public static function get_field_array($data,$ui=false) {
        $array = array();
        if($data)foreach ($data as $key => $value) {
          $output = array();
          if($value=='UI:BR'){
              $ui && $output = array('type'=>'br');
          }else{
              parse_str($value,$output);
              // if($keys){
              //   extract ($output);
              //   $output = compact ($keys);
              //   ksort($output);
              // }
          }
          $output && $array[$key] = $output;
        }
        return $array;
    }
    public static function get_data($app,$id) {
        $data  = array();
        if(empty($id) ){
            return $data;
        }

        $table = $app['table'];
        foreach ($table as $key => $value) {
            $primary_key = $value['primary'];
            $value['union'] && $primary_key = $value['union'];
            $udata = (array)iDB::row("SELECT * FROM `{$value['table']}` WHERE `{$primary_key}`='$id' LIMIT 1;",ARRAY_A);
            $udata && $data+=$udata;
        }
        return $data;
    }
    public static function template($rs,$ret='string'){
      //模板标签
      if($rs['app']){
        $_app = $rs['app'];
        if($rs['config']['iFormer'] && $rs['apptype']=="2"){
          $_app = 'content';
        }
        $template = (array)apps::get_func($_app,true);
        list($path,$obj_name)= apps::get_path($_app,'app',true);

        if(is_file($path)){
            //判断是否有APP同名方法存在 如果有 $appname 模板标签可用
            $class_methods = get_class_methods ($obj_name);
            if(array_search ($_app ,  $class_methods )!==FALSE){
              array_push ($template,'$'.$_app);
            }
        }
      }
      if($rs['config']['iFormer'] && $rs['apptype']=="2"){
        foreach ((array)$template as $key => $value) {
          $template[$key] = str_replace(array(':content:','$content'), array(':'.$rs['app'].':','$'.$rs['app']), $value);
        }
      }
      return $ret=='string'?implode("\n", (array)$template):(array)$template;
    }
    public static function iurl($rs){
      if($rs['table'] && $rs['apptype']=="2"){
        $table  = reset($rs['table']);
        $router = array('rule'=>'4','primary'=>$table['primary'],'page'=>'p');
      }else{
        $array = array(
            'http'     => array('rule'=>'0','primary'=>''),
            'index'    => array('rule'=>'0','primary'=>''),
            'category' => array('rule'=>'1','primary'=>'cid'),
            'article'  => array('rule'=>'2','primary'=>'id','page'=>'p'),
            'tag'      => array('rule'=>'3','primary'=>'id'),
        );
        $router = $array[$rs['app']];
      }
      return $router;
    }
}
