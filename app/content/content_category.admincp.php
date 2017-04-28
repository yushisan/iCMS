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

class content_categoryAdmincp extends categoryAdmincp {
    public function __construct($app) {
        $table = apps::get_table($app);
        parent::__construct($app['id'],'category');

        $this->category_name   = "栏目";
        $this->_app            = $app['app'];
        $this->_app_name       = $app['title'];
        $this->_app_table      = $table['name'];
        $this->_app_cid        = 'cid';
        /**
         *  模板
         */
        $this->category_template = array(
            'index'     => array('首页','{iTPL}/content.index.htm'),
            'list'      => array('列表','{iTPL}/content.list.htm'),
            $app['app'] => array($app['title'],'{iTPL}/content.htm')
        );

        /**
         *  URL规则
         */
        $this->category_rule+= array(
            $app['app'] => array($app['title'],'/{CDIR}/{YYYY}/{MM}{DD}/{ID}{EXT}','{ID},{0xID},{LINK}')
        );
        /**
         *  URL规则选项
         */
        $this->category_rule_list+= array(
            $app['app'] => array(
                array('----'),
                array('{ID}',$app['title'].'ID'),
                array('{0xID}','8位ID'),
                array('{0x3ID}','8位ID(前3位)',fasle),
                array('{0x3,2ID}','8位ID',fasle),
                array('{TITLE}','标题',fasle),
            )
        );
    }
    // public function do_add(){
    //     $this->_view_tpl_dir = $this->_app;
    //     parent::do_add();
    // }
}
