<?php
/*
 * Template Lite plugin
 */
function tpl_output_json(&$output,&$tpl){
    $output = preg_replace('/\n+/is', '', $output);
}
