<?php

function tpl_function_wxml(&$array, &$tpl){
    $array['body'] = preg_replace(array('/<script.+?<\/script>/is'),'',$array['body']);
    $array['source'] = html2text($array['source']);

    // $array['body'] = html2text($array['body']);
    // $array['body'] = str_replace('<p>', '<text>', $array['body']);
    // $array['body'] = str_replace('</p>', '</text>', $array['body']);
}
