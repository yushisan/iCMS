<?php
/**
 * template_lite {ref}{/ref} block plugin
 *
 */
function tpl_block_ref(&$params, $content, &$tpl){
    if($content===null) return false;
    $params = $content;
    return true;
}

