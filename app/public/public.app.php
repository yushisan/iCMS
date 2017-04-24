<?php
/**
 * @package iCMS
 * @copyright 2007-2017, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class publicApp {
	public $methods = array('sitemapindex', 'sitemap', 'seccode', 'agreement', 'crontab', 'time', 'qrcode');
	public function API_agreement() {
		iView::render('{iTPL}/user/agreement.htm');
	}
	public function API_sitemapindex() {
		header("Content-type:text/xml");
		iView::render('/tools/sitemap.index.htm');
	}
	public function API_sitemap() {
		header("Content-type:text/xml");
		iView::assign('cid', (int) $_GET['cid']);
		iView::render('/tools/sitemap.baidu.htm');
	}
	public function API_crontab() {
		$sql = iSQL::update_hits(false,0);
		if ($sql) {
			//点击初始化
			iDB::query("UPDATE `#iCMS@__article` SET {$sql}");
			iDB::query("UPDATE `#iCMS@__user` SET {$sql}");
		}
	}
	public function API_seccode() {
		$_GET['pre'] && $pre = iSecurity::escapeStr($_GET['pre']);
		iSeccode::run($pre);
	}

	public function API_qrcode($url=null) {
		$url===null && $url = iSecurity::escapeStr($_GET['url']);
		echo iPHP::callback(array("plugin_QRcode","HOOK"),$url);
	}
	public static function seccode() {
		return iView::fetch('iCMS://public.seccode.htm');
	}
}
