<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class messageFunc{
    public static function message_list($vars=null){
        $maxperpage = 30;
        $where_sql  = "WHERE `status` ='1'";
        if($_GET['user']){
            if($_GET['user']=="10000"){
                $where_sql.= " AND `userid`='10000' AND `friend` IN ('".user::$userid."','0')";
            }else{
                $friend = (int)$_GET['user'];
                $where_sql.= " AND `userid`='".user::$userid."' AND `friend`='".$friend."'";
            }
            $group_sql = '';
            $p_fields  = 'COUNT(*)';
            $s_fields  = '*';
            iView::assign("msg_count",false);
        }else{
    //      $where_sql.= " AND (`userid`='".user::$userid."' OR (`userid`='10000' AND `friend`='0'))";
            $where_sql.= " AND `userid`='".user::$userid."'";
            $group_sql = ' GROUP BY `friend` DESC';
            $p_fields  = 'COUNT(DISTINCT id)';
            $s_fields  = 'max(id) AS id ,COUNT(id) AS msg_count,`userid`, `friend`, `send_uid`, `send_name`, `receiv_uid`, `receiv_name`, `content`, `type`, `sendtime`, `readtime`';
            iView::assign("msg_count",true);
        }

        $offset = 0;
        $total  = iCMS::page_total_cache("SELECT {$p_fields} FROM `#iCMS@__message` {$where_sql} {$group_sql}",'nocache');
        iView::assign("message_list_total",$total);
        $multi  = iUI::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iUI::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset = $multi->offset;
        $resource = iDB::all("SELECT {$s_fields} FROM `#iCMS@__message` {$where_sql} {$group_sql} ORDER BY `id` DESC LIMIT {$offset},{$maxperpage}");
        if($resource)foreach ($resource as $key => $value) {
            $value['sender']   = user::info($value['send_uid'],$value['send_name']);
            $value['receiver'] = user::info($value['receiv_uid'],$value['receiv_name']);
            $value['label']    = message::$type_map[$value['type']];

            if($value['userid']==$value['send_uid']){
                $value['is_sender'] = true;
                $value['user']      = $value['receiver'];
            }
            if($value['userid']==$value['receiv_uid']){
                $value['is_sender'] = false;
                $value['user']      = $value['sender'];
            }
            $value['url']   = iURL::router(array('user:inbox:uid',$value['user']['uid']));
            $resource[$key] = $value;
        }
        return $resource;
    }
}
