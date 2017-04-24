<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) 2012 iiiphp.com. All rights reserved.
 *
 * @author coolmoo <iiiphp@qq.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.0.0
 * iFormer 表单生成器
 */

class former {
    public static $html     = array();
    public static $validate = null;
    public static $script   = null;

    public static $prefix   = 'iDATA';
    public static $config   = array();

    public static $callback   = array();
    public static $variable   = array();
    public static $template   = array(
        'widget'=>array(
            'text' => 'form-control',
        ),
        'class'=>array(
            'group'    => 'input-group',
            'input'    => 'form-control',
            'label'    => 'input-group-addon',
            'label2'   => 'input-group-addon',
            'help'     => 'help-inline',
            'radio'    => 'form-control',
            'checkbox' => 'form-control',
        ),
        'group'=>'
            <div class="{{class_group}} {{class}}">
                {{label}}
                {{input}}
                {{label2}}
            </div>
            {{help}}
            {{script}}
        ',
        'input'    => '{{content}}',
        'label'    => '<span class="{{class_label}}">{{content}}</span>',
        'label2'   => '<span class="{{class_label2}}">{{content}}</span>',
        'help'     => '<span class="{{class_help}}">{{content}}</span>',
        'radio'    => '<span class="{{class_radio}}">{{content}}</span>',
        'checkbox' => '<span class="{{class_checkbox}}">{{content}}</span>',
    );
    /**
     * [创建表单表单]
     * @param  [type]  $app        [app数据]
     * @param  [type]  $rs         [数据]
     * @return [type]              [description]
     */
    public static function create($app,$rs=null){
        self::$config['app'] = $app;
        self::render($app,$rs);
    }
    public static function multi_value($rs,$fieldArray) {
        foreach ($fieldArray as $key => $field) {
            if(in_array($field['type'], array('category','multi_category','prop','multi_prop'))){
                $value = iSQL::values($rs,$field['name'],'array',null);
                $value = iSQL::explode_var($value);
                $call  = self::$callback[$field['type']];
                if($call && is_callable($call)){
                    self::$variable[$field['name']] = call_user_func_array($call, array($value));
                }
            }
        }
        return self::$variable;
    }
    /**
     * 将由查询字符串(query string)组成的数组转换成二维数组
     * @param  [type]  $data [查询字符串 数组]
     * @param  boolean $ui   [是否把UI标识返回数组]
     * @return [type]        [description]
     */
    public static function fields($data,$ui=false) {
        $array = array();
        foreach ($data as $key => $value) {
          $output = array();
          if($value=='UI:BR'){
              $ui && $output = array('type'=>'br');
          }else{
              parse_str($value,$output);
          }
          $output && $array[$key] = $output;
        }
        return $array;
    }

    public static function widget($name,$attr=null) {
        $widget = new iQuery($name);
        $attr && $widget->attr($attr);
        return $widget;
    }

    public static function render($app,$rs=null) {
        $fields = self::fields($app['fields'],true);
        foreach ($fields as $fkey => $field) {
            $value = $rs[$field['name']];
            $rs===null && $value = null;
            self::html($field,$value,$fkey);
            self::validate($field);
        }
    }
    public static function html($field,$value=null,$fkey=null) {
        if($field['type']=='br'){
            $id  = $fkey;
            $div = self::widget("div")->addClass("clearfloat mt10");
        }else{
            $id      = $field['id'];
            $name    = $field['name'];
            $class   = $field['class'];
            $default = $field['default'];

            list($type,$_type) = explode(':', $field['type']);
            if($value!==null){
                isset($value) OR $value = $default;
                if(!isset($value)){
                    if(in_array($field['field'], array('BIGINT','INT','MEDIUMINT','SMALLINT','TINYINT'))){
                        $value ='0';
                    }else{
                        $value ='';
                    }
                }
            }

            $field['label']      && $label  = self::display($field['label'],'label');
            $field['help']       && $help   = self::display($field['help'],'help');
            $field['label-after']&& $label2 = self::display($field['label-after'],'label2');

            $attr = compact(array('id','name','type','class','value'));
            $attr['id']   = self::$prefix.'_'.$id.'';
            $attr['name'] = self::$prefix.'['.$name.']';
            $field['holder'] && $attr['placeholder'] = $field['holder'];
            self::$template['class']['input'] && $attr['class'].=' '.self::$template['class']['input'];

            $input = self::widget('input',$attr);
            $input->val($value);

            switch ($type) {
                case 'multi_image':
                case 'multi_file':
                    unset($attr['type']);
                    // $form_group.=' input-append';
                    $input  = self::widget('textarea',$attr)->css('height','150px');
                    if(self::$config['gateway']=='admincp'){
                        $picbtn = filesAdmincp::pic_btn($attr['id'],null,($type=='multi_file'?'文件':'图片'),true);
                        $script = self::script('$("#'.$attr['id'].'").autoTextarea({maxHeight:300});',true);
                    }
                    $input.= $picbtn;
                break;
                case 'image':
                case 'file':
                    // $form_group.=' input-append';
                    $input->attr('type','text');
                    if(self::$config['gateway']=='admincp'){
                        $picbtn = filesAdmincp::pic_btn($attr['id'],null,($type=='file'?'文件':'图片'),true);
                    }
                    $input.= $picbtn;
                break;
                case 'tpldir':
                case 'tplfile':
                    // $form_group.=' input-append';
                    $input->attr('type','text');
                    if(self::$config['gateway']=='admincp'){
                        $click ='file';
                        $type=='tpldir' && $click = 'dir';
                        $modal = filesAdmincp::modal_btn($name,$attr['id'],$click);
                    }
                    $input.= $modal;
                break;
                case 'txt_prop':
                    // $form_group.=' input-append';
                    $input->attr('type','text');
                    if(self::$config['gateway']=='admincp'){
                        $prop = propAdmincp::btn_group($name,self::$config['app']['app'],$attr['id']);
                    }
                    $input.= $prop;
                break;
                case 'prop':
                case 'multi_prop':
                    unset($attr['type']);
                    $attr['data-placeholder']= '请选择'.$field['label'].'...';
                    if(strpos($type,'multi')!==false){
                        $attr['name']     = $attr['name'].'[]';
                        $attr['multiple'] = 'true';
                        $attr['data-placeholder']= '请选择'.$field['label'].'(可多选)...';
                        $orig = self::widget('input',array('type'=>'hidden','name'=>self::$prefix.'[_orig_'.$name.']','value'=>$value));
                    }
                    $select = self::widget('select',$attr)->addClass('chosen-select');
                    $option='<option value="0">默认'.$field['label'].'['.$name.'=\'0\']</option>';
                    if(self::$config['gateway']=='admincp'){
                        $option.= propAdmincp::get($name,null,'option',null,self::$config['app']['app']);
                    }
                    $value===null OR $script = self::script('iCMS.FORMER.select("'.$attr['id'].'","'.$value.'");',true);
                    $input = $select->html($option).$orig;
                break;
                case 'date':
                case 'datetime':
                    $attr['class'].= ' ui-datepicker';
                    $attr['type'] = 'text';
                    $input = self::widget('input',$attr);
                    if($type=='date'){
                        $value = get_date($value,'Y-m-d');
                    }else{
                        $value = get_date($value,'Y-m-d H:i:s');
                    }
                    $input->val($value);
                break;
                case 'user_category':
                    if(self::$config['gateway']=='admincp'){
                        $form_group=' former_hide';
                        $input->attr('type','hidden');
                    }
                break;
                case 'PRIMARY':
                case 'union':
                case 'hidden':
                    $form_group=' former_hide';
                    $input->attr('type','hidden');
                break;
                case 'userid':
                    $form_group=' former_hide';
                    $value OR $value = self::$config['value']['userid'];
                    $input->attr('type','text');
                    $input->val($value);
                break;
                case 'nickname':
                case 'username':
                    $value OR $value = self::$config['value'][$type];
                    $input->attr('type','text');
                    $input->val($value);
                break;
                case 'tag':
                    $input = $input->attr('type','text')->attr('onkeyup',"javascript:this.value=this.value.replace(/，/ig,',');");
                    $orig = self::widget('input',array('type'=>'hidden','name'=>self::$prefix.'[_orig_'.$name.']','value'=>$value));
                    $input.= $orig;
                break;
                case 'number':
                    $input->attr('type','text');
                break;
                case 'text':break;
                case 'seccode':
                    $input->addClass('seccode')->attr('maxlength',"4")->attr('type','text');
                    $seccode = publicApp::seccode();
                    $input.= $seccode;
                break;
                case 'editor':
                    if(self::$config['gateway']=='admincp'){
                        $label         = null;
                        $attr['class'] = 'editor-body';
                        $attr['type']  = 'text/plain';
                        $attr['id']    = 'editor-body-'.$attr['id'];
                        $form_group    = 'editor-container';
                        $script        = editorAdmincp::ueditor_script($attr['id']);
                    }
                    $input = self::widget('textarea',$attr);
                break;
                case 'multitext':
                    unset($attr['type']);
                    $input = self::widget('textarea',$attr);
                    $input->css('height','300px');
                break;
                case 'textarea':
                    unset($attr['type']);
                    $input = self::widget('textarea',$attr);
                    $input->css('height','150px');
                break;
                case 'switch':
                case 'radio':
                case 'checkbox':
                    if($type=='checkbox'){
                        $attr['name'] = $attr['name'].'[]';
                    }
                    if($field['option']){
                        $optionArray = explode(";", $field['option']);
                        $option = '';
                        foreach ($optionArray as $optk => $val) {
                            $val = trim($val,"\r\n");
                            if($val){
                                list($opt_text,$opt_value) = explode("=", $val);
                                $opt_value===null && $opt_value = $opt_text;
                                $attr2 = $attr;
                                $attr2['value'] = $opt_value;
                                $attr2['class'].= ' '.$attr2['id'];
                                $attr2['id'].='_'.$optk;
                                $option.= self::widget('label',array('for'=>$attr2['id'],'class'=>$type.'-inline'))->html($opt_text);
                                $_input = self::widget('input',$attr2);
                                if(self::$template['class']['input']){
                                    $_input->removeClass(self::$template['class']['input']);
                                }
                                $option.= $_input;
                            }
                        }
                        $input = self::display($option,$type);
                    }else{
                        $input = self::display($input,$type);
                    }

                    $value===null OR $script= self::script('iCMS.FORMER.checked(".'.$attr['id'].'","'.$value.'");',true);

                    if($type=='switch'){
                        $attr['type'] = 'checkbox';
                        $input = self::widget('input',$attr);
                        $input ='<div class="switch">'.$input.'</div>';
                    }
                break;
                case 'multi_category':
                case 'category':
                    unset($attr['type']);
                    $attr['data-placeholder']= '请选择所属'.$field['label'].'...';
                    if(strpos($type,'multi')!==false){
                        $attr['name']     = $attr['name'].'[]';
                        $attr['multiple'] = 'true';
                        $attr['data-placeholder']= '请选择'.$field['label'].'(可多选)...';
                        $orig = self::widget('input',array('type'=>'hidden','name'=>self::$prefix.'[_orig_'.$name.']','value'=>$value));
                    }
                    $select = self::widget('select',$attr)->addClass('chosen-select');
                    $option = category::appid(self::$config['app']['id'],'cs')->select();
                    $value===null OR $script = self::script('iCMS.FORMER.select("'.$attr['id'].'","'.$value.'");',true);
                    $input = $select->html($option).$orig;
                break;
                case 'multiple':
                case 'select':
                    // $attr['class'] = $field['class'];
                    $attr['data-placeholder']= '请选择'.$field['label'].'...';
                    if(strpos($type,'multi')!==false){
                        unset($attr['type']);
                        $attr['multiple'] = 'true';
                        $attr['name']     = $attr['name'].'[]';
                        $attr['data-placeholder']= '请选择'.$field['label'].'(可多选)...';
                        $_input = self::widget('input',array('type'=>'hidden','name'=>self::$prefix.'[_orig_'.$name.']','value'=>$value));
                    }
                    $input  = self::widget('select',$attr)->addClass('chosen-select');
                    $option = '';
                    if(self::$config['gateway']=='admincp'){
                        $option.='<option value=""></option>';
                    }
                    if($field['option']){
                        $optionArray = explode(";", $field['option']);
                        foreach ($optionArray as $ok => $val) {
                            $val = trim($val,"\r\n");
                            if($val){
                                list($opt_text,$opt_value) = explode("=", $val);
                                $opt_value===null && $opt_value = $opt_text;
                                $option.='<option value="'.$opt_value.'">'.$opt_text.' ['.$name.'="'.$opt_value.'"]</option>';
                            }
                        }
                        $input->html($option);
                        $value===null OR $script = self::script('iCMS.FORMER.select("'.$attr['id'].'","'.$value.'");',true);
                    }
                    $input.= $_input;
                break;
                case 'device':
                    $value = iPHP::$mobile?'1':'0';
                    $input->val($value);
                break;
                case 'postype':
                    $value = self::$config['gateway']=='admincp'?'1':'0';
                    $input->val($value);
                break;
                default:
                    $input->attr('type','text');
                break;
            }
            if($_type=='hidden'){
                $form_group =' former_hide';
                $input->attr('type','hidden');
            }

            $div = self::display(array(
                'class'  => 'former_'.$type.' '.$form_group,
                'label'  => $label,
                'label2' => $label2,
                'input'  => self::display($input,'input'),
                'help'   => $help,
                'script' => $script,
            ));
        }
        self::$html[$id]= $div;
    }
    public static function display($html,$key="group") {
        $output = $html;
        if(self::$template[$key]){
            $output = self::template_class(self::$template[$key]);
            if(is_array($html)){
                foreach ($html as $k => $value) {
                    $output = str_replace('{{'.$k.'}}', $value, $output);
                }
            }else{
                $output = str_replace('{{content}}', $html, $output);
            }
        }
        return $output;
    }
    public static function template_class($output) {
        if(self::$template['class'])foreach (self::$template['class'] as $k => $value) {
            $output = str_replace('{{class_'.$k.'}}', $value, $output);
        }
        return $output;
    }

    public static function script($code=null,$script=false,$ready=true) {
        if($code){
            $code = '+function(){'.$code.'}();';
            $ready && $code = '$(function(){'.$code.'});';
            return $script?'<script>'.$code.'</script>':$code;
        }
    }
    public static function js_test($id,$label,$msg,$pattern) {
        $script = '
            var '.$id.'_msg = "'.$msg.'",pattern = '.$pattern.';
            if(!pattern.test('.$id.'_value)){
                iCMS.UI.alert('.$id.'_error||"['.$label.'],"+'.$id.'_msg+",请重新填写!");
                '.$id.'.focus();
                return false;
            }';
        return $script;
    }
    public static function validate($field_array,$lang='js',$value='') {
        if(empty($field_array['validate'])) return;

        $id    = self::$prefix.'_'.$field_array['id'].'';
        $name  = self::$prefix.'['.$field_array['name'].']';
        $label = $field_array['label'];
        $type  = $field_array['type'];
        $error = $field_array['error'];

        if($lang=='js'){
            $javascript = 'var '.$id.' = $("#'.$id.'"),'.$id.'_value = '.$id.'.val(),'.$id.'_error="'.$error.'";';
            if($type=='editor'){
                // $script = 'var '.$id.' = iCMS.editor.get("editor-body-'.$id.'"),'.$id.'_value = '.$id.'.hasContents()';
                $javascript = 'var '.$id.' = iCMS.editor.get("editor-body-'.$id.'"),'.$id.'_value = '.$id.'.getContent()';
            }
        }

        foreach ($field_array['validate'] as $key => $vd) {
            $code = null;
            switch ($vd) {
                case 'zipcode':
                    $msg = "邮政编码有误";
                    $pattern = "/^[1-9]{1}(\d+){5}$/";
                break;
                case 'idcard':
                    $msg = "身份证有误";
                    $pattern = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
                break;
                case 'telphone':
                    $msg = "固定电话号码有误";
                    $pattern = "/^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,14}$/";
                break;
                case 'phone':
                    $msg = "手机号码有误";
                    $pattern = "/^1[34578]\d{9}$/";
                break;
                case 'url':
                    $msg = "网址有误";
                    $pattern = "/^[a-zA-z]+:\/\/[^\s]*/";
                break;
                case 'email':
                    $msg = "邮箱地址有误";
                    $pattern = "/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
                break;
                case 'number':
                    $msg = "只能输入数字";
                    $pattern = "/^\d+(\.\d+)?$/";
                break;
                case 'hanzi':
                    $msg = "只能输入汉字";
                    $pattern = "/^[\u4e00-\u9fa5]*$/";
                    if($lang=='php'){
                        $pattern = "/^[\x{4e00}-\x{9fa5}]*$/u";
                    }
                break;
                case 'character':
                    $msg = "只能输入字母";
                    $pattern = "/^[A-Za-z]+$/";
                break;
                case 'empty':
                    $msg = $label.'不能为空!';
                    if($lang=='php'){
                        empty($value) && iUI::alert($msg);
                    }else{
                        $code = '
                            if(!'.$id.'_value){
                                iCMS.UI.alert('.$id.'_error||"'.$msg.'");
                                '.$id.'.focus();
                                return false;
                            }
                        ';
                    }
                break;
                case 'minmax':
                    $min  = $field_array['minmax'][0];
                    $max  = $field_array['minmax'][1];
                    $msg_min = '您填写的'.$label.'小于'.$min.',请重新填写!';
                    $msg_max = '您填写的'.$label.'大于'.$max.',请重新填写!';
                    if($lang=='php'){
                        ($value<$min) && iUI::alert($msg_min);
                        ($value>$max) && iUI::alert($msg_max);
                    }else{
                        $code = '
                            var value = parseInt('.$id.'_value)||0;
                            // console.log(value);
                            if (value < '.$min.') {
                                iCMS.UI.alert('.$id.'_error||"'.$msg_min.'");
                                '.$id.'.focus();
                                return false;
                            }
                            if (value > '.$max.') {
                                iCMS.UI.alert('.$id.'_error||"'.$msg_max.'");
                                '.$id.'.focus();
                                return false;
                            }
                        ';
                    }

                break;
                case 'count':
                    $min  = $field_array['count'][0];
                    $max  = $field_array['count'][1];
                    $msg_min = '您填写的'.$label.'小于'.$min.'字符,请重新填写!';
                    $msg_max = '您填写的'.$label.'大于'.$max.'字符,请重新填写!';
                    if($lang=='php'){
                        (strlen($value)<$min) && iUI::alert($msg_min);
                        (strlen($value)>$max) && iUI::alert($msg_max);
                    }else{
                        $code = '
                            var value = '.$id.'_value.replace(/[^\x00-\xff]/g, \'xx\').length;
                            // console.log(value);
                            if (value < '.$min.') {
                                iCMS.UI.alert('.$id.'_error||"'.$msg_min.'");
                                '.$id.'.focus();
                                return false;
                            }
                            if (value > '.$max.') {
                                iCMS.UI.alert('.$id.'_error||"'.$msg_max.'");
                                '.$id.'.focus();
                                return false;
                            }
                        ';
                    }

                break;
                case 'defined':
                    $field_array['defined'] && $code = $field_array['defined'];
                break;
                default:
                    # code...
                    break;
            }
            if($lang=='php'){
                if($pattern && $msg){
                    preg_match($pattern, $value) OR iUI::alert($msg);
                }
            }else{
                if(empty($code)){
                    $code = self::js_test($id,$label,$msg,$pattern);
                }
                $javascript.= $code;
            }
        }
        self::$validate.= $javascript;
        self::$script.= self::script($field_array['javascript']);

        return $javascript;
    }
    public static function de_value($value,$fields,$vArray=null) {
        //字段数据类型
        $field = $fields['field'];

        //字段类型
        list($type,$_type) = explode(':', $fields['type']);

        //时间转换
        if(in_array($type, array('date','datetime'))){
          $value = get_date($value,'Y-m-d H:i:s');
        }
        if(in_array($type, array('category','multi_category'))){
            $variable = self::$variable[$fields['name']];
            $valArray = explode(",", $value);
            $value = '';
            foreach ($valArray as $i => $val) {
                $array = $variable[$val];
                $value.= '<a href="'.APP_DOURI.'&cid='.$val.'&'.$uri.'">'.$array->name.'</a>';
            }
        }
        //多选字段转换
        if(isset($fields['multiple'])){
          $value = explode(',',$value);
        }

        return $value;
    }
    public static function en_value($value,$fields) {
        //字段数据类型
        $field = $fields['field'];

        //字段类型
        list($type,$_type) = explode(':', $fields['type']);

        //时间转换
        if(in_array($type, array('date','datetime'))){
          $value = str2time($value);
          if($_type=='hidden'){
            $value = time();
          }
        }
        if(in_array($type, array('ip'))){
          $value = iPHP::get_ip();
        }
        if(in_array($type, array('referer'))){
          $value = $_SERVER['HTTP_REFERER'];
        }
        //多选字段转换
        if(isset($fields['multiple'])){
          is_array($value) && $value = implode(',',$value);
        }

        //数字转换
        if(in_array($field, array('BIGINT','INT','MEDIUMINT','SMALLINT','TINYINT'))){
          $value = (int)$value;
        }
        //编辑器不处理
        if($type=='editor'){
          // $post[$key] = $value;
        }else{
          $value = iSecurity::escapeStr($value);
        }
        return $value;
    }
    /**
     * 处理表单数据
     * @param  [type] $app [app数据]
     * @param  [type] $post[表单POST数组]
     * @return [array]     [description]
     */
    public static function post($app,$post=null) {
        if($post===null) $post = $_POST[self::$prefix];

        if(empty($post)) return array(false,false,false);

        $orig_post   = array();
        $data_post = array();

        $field_array = self::fields($app['fields']);
        $data_table  = next($app['table']);
        $imap_array  = array();

        foreach ($post as $key => $value) {
            $fields = $field_array[$key];
            //字段绑定的函数处理
            $fields['func'] && $value = self::func($fields['func'],$value);
            //字段数据处理
            $value = self::en_value($value,$fields);
            //数据验证
            self::validate($fields,'php',$value);

            $post[$key] = $value;

            if(in_array($fields['type'], array('category','multi_category'))){
                $imap_array[$key] = array('category',$value);
            }
            if(in_array($fields['type'], array('prop','multi_prop'))){
                $imap_array[$key] = array('prop',$value);
            }
            if(in_array($fields['type'], array('tag'))){
                $tag_array[$key] = array($value,$post['cid']);
            }
            //找查原始数据 并移除当前POST
            if(strpos($key,'_orig_')!==false){
              $orig_post[$key] = $value;
              unset($post[$key]);
            }
            //找查MEDIUMTEXT字段 并移除当前POST
            if($fields['field']=='MEDIUMTEXT'){
              $data_post[$key] = $value;
              unset($post[$key]);
            }
            if($data_table){
                if($data_table['primary']==$key||$data_table['union']==$key){
                  $data_post[$key] = $value;
                  unset($post[$key]);
                }
            }
        }
        if($data_table){
            $values = compact('post','data_post'); //将表单数据存入数组
        }else{
            $values = compact('post'); //将表单数据存入数组
        }
        $tables = array_keys($app['table']);//返回所有表名
        //创建一个数组，用一个表名数组的值作为其键名，表单数据的值作为其值
        $variable = array_combine($tables,$values);

        /**
         * array(表单数据,表名,_orig_字段数据用于比较);
         */
        return array($variable,$tables,$orig_post,$imap_array,$tag_array);
    }
    /**
     * 表单数据处理
     * @param  [type] $func  [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public static function func($func,$value) {
        return $value;
    }
    public static function layout($id=null,$func='submit') {
        $pieces[] = implode('',self::$html);
        if(self::$validate||self::$script){
            $pieces[]= '<script type="text/javascript">';
            $pieces[]= '$(function(){';
            if(self::$validate){
                if($is===null && defined('APP_FORMID')){
                    $id = '#'.APP_FORMID;
                }
                $pieces[]= '$("'.$id.'").'.$func.'(function(){';
                $pieces[]= self::$validate;
                $pieces[]= '});';
            }
            $pieces[]= self::$script;
            $pieces[]= '});';
            $pieces[]= '</script>';
        }
        return implode(PHP_EOL, $pieces);
    }
}
