<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class cacheAdmincp{
    public $acp = array('configAdmincp','propAdmincp','filterAdmincp','keywordsAdmincp');
    public function __construct() {
        $this->do_app();
    }
    /**
     * [更新所有缓存]
     * @return [type] [description]
     */
    public function do_all(){
        foreach ($this->acp as $key => $acp) {
            iPHP::callback(array($acp,'cache'));
        }
        $this->do_menu(false);
        $this->do_category(false);
        $this->do_tpl(false);
        iUI::success('全部缓存更新完成');
    }
    /**
     * [执行更新缓存]
     * @return [type] [description]
     */
    public function do_iCMS($dialog=true){
		if (in_array($_GET['acp'], $this->acp)) {
	    	$acp = $_GET['acp'];
	    	$acp::cache();
	    	$dialog && iUI::success('更新完成');
		}
    }
    /**
     * [更新菜单缓存]
     * @return [type] [description]
     */
    public function do_menu($dialog=true){
    	menu::cache();
    	$dialog && iUI::success('更新完成','js:1');
    }
    /**
     * [更新所有分类缓存]
     * @return [type] [description]
     */
    public function do_category($dialog=true){
    	category::cache();
    	$dialog && iUI::success('更新完成');
    }
    /**
     * [更新文章分类缓存]
     * @return [type] [description]
     */
    public function do_article_category($dialog=true){
        $categoryAdmincp = new article_categoryAdmincp();
        $categoryAdmincp->do_cache($dialog);
    }
    /**
     * [更新标签分类缓存]
     * @return [type] [description]
     */
    public function do_tag_category($dialog=true){
        $categoryAdmincp = new tag_categoryAdmincp();
        $categoryAdmincp->do_cache($dialog);
    }
    /**
     * [更新模板缓存]
     * @return [type] [description]
     */
    public function do_tpl($dialog=true){
    	iView::clear_tpl();
    	$dialog && iUI::success('清理完成');
    }
    /**
     * [重计文章数]
     * @return [type] [description]
     */
    public function do_article_count($dialog=true){
        $categoryAdmincp = new article_categoryAdmincp();
    	$categoryAdmincp->re_app_count();
    	$dialog && iUI::success('更新完成');
    }
    /**
     * [更新应用缓存]
     * @return [type] [description]
     */
    public function do_app($dialog=true){
        apps::cache();
    }
}
