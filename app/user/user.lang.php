<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author icmsdev <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
*/
defined('iPHP') OR exit('What are you doing?');

return array(
	'empty_id'=>'用户ID不能为空！',
	'profile'=>array(
		'success'     =>'修改完成！',
		'avatar'      =>'头像上传成功！',
		'custom'      =>'更新封面成功！',
		'personstyle' =>'多个标签之间请用逗号隔开',
		'slogan'      =>"随便写点什么，让大家了解你吧。",
		'pskin'       =>"请选择",
		'phair'       =>"请选择",
		'unickEdit'	  =>"你已经修改过昵称了！",
		'nickname'	  =>"昵称已存在,请换个再试试。",
	),
	'follow'=>array(
		'success' =>'已关注！',
		'failure' =>'关注失败！',
		'self'    =>'不能关注自己！',
	),
	'login'=>array(
		'def_uname' =>'邮箱 或 昵称',
		'interval'  =>'您的账号已经连续5次登陆错误，该账号已经被锁定！',
		'error'     =>'用户名或者密码错误！',
		'forbidden' =>'系统已关闭登陆功能！',
	),
	'category'=>array(
		'empty'   =>'请输入分类名称！',
		'filter'  =>'分类名称包含被系统屏蔽的字符，请重新填写!',
		'max'     =>'最多只能创建10个分类!',
		'success' =>'添加成功！',
		'update'  =>'更新成功！',
	),
	'article'=>array(
		'add_success'    =>'文章发表完成！',
		'add_examine'    =>'文章发表完成！<br />本栏目需要审核后才能正常显示。',
		'update_success' =>'文章更新完成！',
		'update_examine' =>'文章更新完成！<br />本栏目需要审核后才能正常显示。',
	),
	'publish'=>array(
		'filter_title' =>'标题中包含被系统屏蔽的字符，请重新填写。',
		'filter_desc'  =>'简介中包含被系统屏蔽的字符，请重新填写。',
		'filter_body'  =>'内容中包含被系统屏蔽的字符，请重新填写。',
		'interval'     =>'您发贴的速度太快了，请休息下吧。',
	),
	'findpwd'=>array(
		'success' =>'您的密码已经修改成功！请重新登陆。',
		'error'   =>'您的链接已经过时，请重新申请找回密码。',
		'same'    =>'您的新密码与旧密码一样。请重新设置新密码。',
		'send'    => array(
			'success' =>'重设密码的邮件发送成功！请登陆您的邮箱查收相关邮件。',
			'failure' =>'重设密码的邮件发送失败请稍后在重试。',
		),
		'username'=> array(
			'empty'   =>'请填写电子邮箱！',
			'noexist' =>'邮件地址不存在,请换个邮件再试试。',
		),
	),
	'register' => array(
		'forbidden'=>'系统已经关闭注册功能！',
		'interval' =>'同一个IP在{time}时间内只能注册一个账号！',
		'nickname'=> array(
			'filter'=>'昵称中包含被系统屏蔽的字符，请重新填写。',
			'empty'=>'请填写昵称！',
			'error'=>'昵称只能4~20位，每个中文字算2位字符。',
			'exist'=>'昵称已经被注册了,请换个再试试。',
		),
		'username'=> array(
			'empty'=>'请填写电子邮箱！',
			'error'=>'电子邮箱格式不正确！',
			'exist'=>'邮件地址已经注册过了,请直接登陆或者换个邮件再试试。',
		),
	),
	'password'=> array(
		'original'  =>'原密码错误！',
		'modified'  =>'修改完成！',
		'empty'     =>'请填写密码！',
		'new'       =>'请填写新的密码！',
		'rst_empty' =>'请重复输入一次密码！',
		'error'     =>'密码太短啦，至少要6位哦',
		'unequal'   =>'密码与确认密码不一致！',
	),
);
