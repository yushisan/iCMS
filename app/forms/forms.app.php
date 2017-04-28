<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

class formsApp {
    public $methods = array('iCMS','save');
    public function do_iCMS(){
        $formid = (int) $_GET['id'];
        $this->forms($formid);
    }
    public function API_iCMS(){
        $this->do_iCMS();
    }
    public function ACTION_save(){
        $formid = (int) $_POST['form_id'];
        $time   = iPHP::get_cookie('token_time');
        $token  = $_POST['token'];
        list($_formid,$_time) = explode("#", authcode($token));
        if($_formid==$formid && $_time==$time){
            $formsAdmincp = new formsAdmincp();
            $ret   = $formsAdmincp->do_savedata(false);
            $forms = $formsAdmincp->form;
            iPHP::set_cookie('token_time','',-31536000);
            $array = array('code'=>1,'msg'=>$forms['config']['success']);
        }else{
            $array = array('code'=>0,'msg'=>'提交出错!');
        }
        if(iPHP::is_ajax()){
            echo json_encode($array);
        }else{
            if ($array['code']){
                iUI::success($array['msg']);
            }else{
                iUI::alert($array['msg']);
            }
        }
    }

    public function forms($formid,$tpl = true){
        $forms = forms::get($formid);

        if(empty($forms)||empty($forms['status'])){
            iPHP::error_404('找不到相关表单<b>ID:' . $formid . '</b>', 10001);
        }

        $forms['fieldArray'] = former::fields($forms['fields']);
        $forms['action'] = iURL::router(array('forms'));
        $forms['url']    = iURL::router(array('forms:id',$forms['id']));
        $forms['result'] = iURL::router(array('forms:result',$forms['id']));
        $forms['link']   = '<a href="'.$forms['url'].'" class="forms" target="_blank">'.$forms['title'].'</a>';
        $forms['pic']    = filesApp::get_pic($forms['pic']);
        $forms['time']   = time();
        $forms['token']  = authcode($formid.'#'.$forms['time'],'decode');
        $forms['layout_id']  = "former_".$forms['id'];

        iPHP::set_cookie('token_time', $forms['time'], 600);

        if ($tpl) {
            $forms_tpl = $forms['tpl'];
            strstr($tpl, '.htm') && $forms_tpl = $tpl;
            iView::assign('forms', $forms);
            $view = iView::render($forms_tpl, 'article');
            if($view) return array($view,$forms);
        } else {
            return $forms;
        }
    }
}
