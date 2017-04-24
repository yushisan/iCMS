<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) 2012 iiiphp.com. All rights reserved.
 *
 * @author coolmoo <iiiphp@qq.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.0.0
 */
defined('iPHP') OR exit('What are you doing?');

class iUI {

	public static $pagenav    = NULL;
	public static $offset     = NULL;
	public static $break      = true;
	public static $dialog     = array();

	public static function lang($string = '', $throw = true) {
		if (empty($string)) {
			return false;
		}

		$keyArray = explode(':', $string);
		$count = count($keyArray);
		list($app, $do, $key, $msg) = $keyArray;

		$fname = $app . '.lang.php';
		$path = iPHP_APP_DIR.'/'.$app.'/'.$fname;
		$app=="iCMS" && $path = iPHP_APP_CORE.'/'.$fname;

		if (!is_file($path)) {
			if ($throw) {
				iPHP::error_throw($fname . ' not exist', 0015);
			} else {
				return $string;
			}
		}

		$langArray = iPHP::import($path, true);

		switch ($count) {
		case 1:return $langArray;
		case 2:return $langArray[$do];
		case 3:return $langArray[$do][$key];
		case 4:return $langArray[$do][$key][$msg];
		}
	}
	public static function json($a, $break = true, $ret = false) {
		$json = json_encode($a);
		$_GET['callback'] && $json = $_GET['callback'] . '(' . $json . ')';
		$_GET['script'] && exit("<script>{$json};</script>");
		if ($ret) {
			return $json;
		}
		echo $json;
		$break && exit();
	}
	public static function js_callback($a, $callback = null, $node = 'parent') {
		$callback === null && $callback = $_GET['callback'];
		empty($callback) && $callback = 'callback';
		$json = json_encode($a);
		echo "<script>window.{$node}.{$callback}($json);</script>";
		exit;
	}
	public static function code($code = 0, $msg = '', $forward = '', $format = '') {
		strstr($msg, ':') && $msg = iUI::lang($msg, false);
		$a = array('code' => $code, 'msg' => $msg, 'forward' => $forward);
		if ($format == 'json') {
			iUI::json($a);
		}
		return $a;
	}
	public static function msg($info, $ret = false) {
        if(strpos($info,':#:')===false){
            $msg = $info;
        }else{
			list($label, $icon, $content) = explode(':#:', $info);
	        if(iPHP_SHELL){
	        	if($label=="success"){
	        		$msg ="\033[32m {$content} \033[0m";//green
	        	}else{
	        		$msg ="\033[31m {$content} \033[0m";//red
	        	}
	        }else{
	            $msg = '<div class="iPHP-msg"><span class="label label-'.$label.'">';
				$icon && $msg .= '<i class="fa fa-' . $icon . '"></i> ';
				if (strpos($content, ':') !== false) {
					$lang = iUI::lang($content, false);
					$lang && $content = $lang;
				}
	        }
            $msg.= $content.'</span></div>';
		}
    	if($ret) return $msg;
		echo $msg;
	}
	public static function js($str = "js:", $ret = false) {
		$type = substr($str, 0, strpos($str, ':'));
		$act = substr($str, strpos($str, ':') + 1);
		switch ($type) {
			case 'js':
				$act && $code = $act;
				$act == "0" && $code = 'iTOP.history.go(-1);';
				$act == "1" && $code = 'iTOP.location.href=iTOP.location.href;';
			break;
			case 'url':
				$act == "1" && $act = iPHP_REFERER;
				$code = "iTOP.location.href='" . $act . "';";
			break;
			case 'src':
				$code = "iTOP.$('#iPHP_FRAME').attr('src','" . $act . "');";
			break;
			default:$code = '';
		}

		if ($ret) {
			return $code;
		}

		echo '<script type="text/javascript">' . $code . '</script>';
		self::$break && exit();
	}
	public static function warning($info) {
		self::msg('warning:#:warning:#:' . $info);
	}
	public static function alert($msg, $js = null, $s = 3) {
		if (iUI::$dialog['alert'] === 'window') {
			iUI::js("js:window.alert('{$msg}')");
		}
		self::$dialog = array(
			'id'         => iPHP_APP.'-DIALOG-ALERT',
			'skin'       => iPHP_APP.'_dialog_alert',
			'lock'       => true,
			'quickClose' => false,
			'width'      => 360,
			'height'     => 120,
		);
		self::dialog('warning:#:warning:#:' . $msg, $js, $s);
	}
	public static function success($msg, $js = null, $s = 3) {
		self::$dialog = array(
			'id'         => iPHP_APP.'-DIALOG-ALERT',
			'skin'       => iPHP_APP.'_dialog_alert',
			'lock'       => true,
			'quickClose' => false,
			'width'      => 360,
			'height'     => 120,
		);
		self::dialog('success:#:check:#:' . $msg, $js, $s);
	}
	public static function dialog($info = array(), $js = 'js:', $s = 3, $buttons = null, $update = false) {
		$info = (array) $info;
		$title = $info[1] ? $info[1] : '提示信息';
        $content = self::msg($info[0],true);
        if(iPHP_SHELL){
        	echo $content;
        	return;
        }
		$content =
			'<table class="ui-dialog-table" align="center">'.
				'<tr>'.
					'<td valign="middle">' . $content . '</td>'.
				'</tr>'.
			'</table>';
		$content = str_replace(array("\n","\r","\\"), array('','',"\\\\"), $content);
		$content = addslashes($content);

		$options = array(
			"time:null","api:'iPHP'",
			"id:'" . (self::$dialog['id'] ? self::$dialog['id'] : 'iPHP-DIALOG'). "'",
			"title:'" . (self::$dialog['title'] ? self::$dialog['title'] : iPHP_APP) . " - {$title}'",
			"lock:" . (self::$dialog['lock'] ? 'true' : 'false'),
			"width:'" . (self::$dialog['width'] ? self::$dialog['width'] : 'auto') . "'",
			"height:'" . (self::$dialog['height'] ? self::$dialog['height'] : 'auto') . "'",
		);
		if(isset(self::$dialog['quickClose'])){
			$options[] = "quickClose:" . (self::$dialog['quickClose'] ? 'true' : 'false');
		}
		if(isset(self::$dialog['skin'])){
			$options[] = "skin:'" . self::$dialog['skin']. "'";
		}

		//$content && $options[]="content:'{$content}'";
		$auto_func = 'd.close().remove();';
		$func = iUI::js($js, true);
		if ($func) {
			$ok = 'okValue: "确 定",ok: function(){' . $func . '}';
			// $buttons OR $options[] = $ok
			$auto_func = $func . 'd.close().remove();';
		}
		if (is_array($buttons)) {
			$okbtn = "{value:'确 定',callback:function(){" . $func . "},autofocus: true}";
			foreach ($buttons as $key => $val) {
				$val['id'] && $id = "id:'" . $val['id'] . "',";
				$val['js'] && $func = $val['js'] . ';';
				$val['url'] && $func = "iTOP.location.href='{$val['url']}';";
				$val['src'] && $func = "iTOP.$('#iPHP_FRAME').attr('src','{$val['src']}');return false;";
				$val['target'] && $func = "iTOP.window.open('{$val['url']}','_blank');";
                if($val['close']===false){
                    $func.= "return false;";
                }
                $val['time'] && $s = $val['time'];

                if($func){
                    $buttonA[]="{".$id."value:'".$val['text']."',callback:function(){".$func."}}";
                    $val['next'] && $auto_func = $func;
                }
            }
			$button = implode(",", $buttonA);
		}else{
			self::$dialog['ok'] OR $options[] = $ok;
		}
		self::$dialog['ok'] && $options[] = 'okValue: "确 定",ok: function(){'.self::$dialog['ok:js'].'}';
		self::$dialog['cancel'] && $options[] = 'cancelValue: "取 消",cancel: function(){'.self::$dialog['cancel:js'].'}';

		$dialog = 'var iTOP = window.top,';
		if ($update) {
			$dialog .= "d = iTOP.dialog.get('iPHP-DIALOG');";
			$auto_func = $func;
		} else {
			$dialog .= 'options = {' . implode(',', $options) . '},d = iTOP.' . iPHP_APP . '.UI.dialog(options);';
			// if(self::$dialog_lock){
			// 	$dialog.='d.showModal();';
			// }else{
			// 	$dialog.='d.show();';
			// }
		}
		$button && $dialog .= "d.button([$button]);";
		$content && $dialog .= "d.content('$content');";

		$s <= 30 && $timeout = $s * 1000;
		$s > 30 && $timeout = $s;
		$s === false && $timeout = false;
		if ($timeout) {
			$dialog .= 'window.setTimeout(function(){' . $auto_func . '},' . $timeout . ');';
		} else {
			$update && $dialog .= $auto_func;
		}
		echo self::$dialog['code'] ? $dialog : '<script>' . $dialog . '</script>';
		self::$break && exit();
	}
	//动态翻页函数
	public static function pagenav($total, $displaypg = 20, $unit = "条记录", $url = '', $target = '') {
		$pageconf = array(
			'url'        => $url,
			'target'     => $target,
			'total'      => $total,
			'perpage'    => $displaypg,
			'total_type' => 'G',
			'lang'       => iUI::lang(iPHP_APP . ':page'),
		);
		$pageconf['lang']['format_left'] = '<li>';
		$pageconf['lang']['format_right'] = '</li>';

		$iPages = new iPages($pageconf);
		self::$offset = $iPages->offset;
		self::$pagenav = '<ul>' .
		self::$pagenav .= $iPages->show(3);
		self::$pagenav .= "<li> <span class=\"muted\">{$total}{$unit} {$displaypg}{$unit}/页 共{$iPages->totalpage}页</span></li>";
		if ($iPages->totalpage > 200) {
			$url = $iPages->get_url(1);
			self::$pagenav .= "<li> <span class=\"muted\">跳到 <input type=\"text\" id=\"pageselect\" style=\"width:24px;height:12px;margin-bottom: 0px;line-height: 12px;\" /> 页 <input class=\"btn btn-small\" type=\"button\" onClick=\"window.location='{$url}&page='+$('#pageselect').val();\" value=\"跳转\" style=\"height: 22px;line-height: 18px;\"/></span></li>";
		} else {
			self::$pagenav .= "<li> <span class=\"muted\">跳到" . $iPages->select() . "页</span></li>";
		}
		self::$pagenav .= '</ul>';
	}
	//模板翻页函数
	public static function page($conf) {
		$conf['lang'] = iUI::lang(iPHP_APP . ':page');
		$iPages = new iPages($conf);
		if ($iPages->totalpage > 1) {
			$pagenav = $conf['pagenav'] ? strtoupper($conf['pagenav']) : 'NAV';
			$pnstyle = $conf['pnstyle'] ? $conf['pnstyle'] : 0;
			iView::$handle->_iVARS['PAGE'] = array(
				$pagenav  => $iPages->show($pnstyle),
				'COUNT'   => $conf['total'],
				'TOTAL'   => $iPages->totalpage,
				'CURRENT' => $iPages->nowindex,
				'PN'      => $iPages->nowindex,
				'PREV'    => $iPages->prev_page(),
				'NEXT'    => $iPages->next_page(),
				'LAST'    => ($iPages->nowindex>=$iPages->totalpage),
			);
			iView::$handle->_iVARS['PAGES'] = $iPages;
		}
		return $iPages;
	}
    public static function permission($p = '', $ret = 'alert') {
    	$msg = "您没有[$p]的访问权限!";
    	if(iPHP_SHELL){
    		echo $msg."\n";
	        exit;
    	}
    	if (isset($_GET['frame'])) {
    		iUI::alert($msg);
    		exit;
    	}
        if(iPHP::is_ajax()){
            $array = array('code'=>0,'msg'=>$msg);
            echo json_encode($array);
            exit;
        }
		if ($_POST) {
	        echo '<script>top.alert("' . $msg . '")</script>';
	        exit;
	    }
        if ($ret == 'alert') {
            iUI::alert($msg);
            exit;
        } elseif ($ret == 'page') {
            exit($msg);
        }
    }
    public static function check($o) {
        return $o?'<font color="green"><i class="fa fa-check"></i></font>':'<font color="red"><i class="fa fa-times"></i></font>';
    }
}
