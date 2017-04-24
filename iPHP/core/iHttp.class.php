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
class iHttp{
    public static $PROXY_URL = null;

    public static $CURL_COUNT = 3;
    public static $CURL_HTTP_CODE = null;
    public static $CURL_CONTENT_TYPE = null;
    public static $CURL_PROXY = null;
    public static $CURL_PROXY_ARRAY = array();
    public static $CURLOPT_ENCODING = '';
    public static $CURLOPT_REFERER = null;
    public static $CURLOPT_TIMEOUT = 10; //数据传输的最大允许时间
    public static $CURLOPT_CONNECTTIMEOUT = 3; //连接超时时间
    public static $CURLOPT_USERAGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.122 Safari/537.36';

    public static function proxy_test() {
        $options = array(
            CURLOPT_URL => 'http://www.baidu.com',
            CURLOPT_REFERER => 'http://www.baidu.com',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_NOSIGNAL => true,
            // CURLOPT_DNS_USE_GLOBAL_CACHE => true,
            // CURLOPT_DNS_CACHE_TIMEOUT => 86400,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            // CURLOPT_FOLLOWLOCATION => 1,// 使用自动跳转
            // CURLOPT_MAXREDIRS => 7,//查找次数，防止查找太深
        );
        if (empty(self::$CURL_PROXY_ARRAY)) {
            if (empty(self::$CURL_PROXY)) {
                return false;
            }
            self::$CURL_PROXY_ARRAY = explode("\n", self::$CURL_PROXY); // socks5://127.0.0.1:1080@username:password
        }
        if (empty(self::$CURL_PROXY_ARRAY)) {
            return false;
        }
        $rand_keys = array_rand(self::$CURL_PROXY_ARRAY, 1);
        $proxy = self::$CURL_PROXY_ARRAY[$rand_keys];
        $proxy = trim($proxy);
        $options = self::proxy($options, $proxy);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] == 200) {
            return $proxy;
        } else {
            unset(self::$CURL_PROXY_ARRAY[$rand_keys]);
            return self::proxy_test();
        }
    }
    public static function proxy($options = array(), $proxy) {
        if ($proxy) {
            $proxy = trim($proxy);
            $matches = strpos($proxy, 'socks5://');
            if ($matches === false) {
                // $options[CURLOPT_HTTPPROXYTUNNEL] = true;//HTTP代理开关
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP; //使用http代理模式
            } else {
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
            }
            list($url, $auth) = explode('@', $proxy);
            $url = str_replace(array('http://', 'socks5://'), '', $url);
            $options[CURLOPT_PROXY] = $url;
            $auth && $options[CURLOPT_PROXYUSERPWD] = $auth; //代理验证格式  username:password
            $options[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC; //代理认证模式
        }
        return $options;
    }
    //获取远程页面的内容
    public static function remote($url, $_count = 0) {
        $url = str_replace(array(' ','&amp;'), array('%20','&'), $url);

        if (function_exists('curl_init')) {
            if (empty($url)) {
                echo 'remote:(' . $_count . ')' . $url . "\n";
                echo "url:empty\n";
                return false;
            }
            if (self::$CURLOPT_REFERER === null) {
                $uri = parse_url($url);
                self::$CURLOPT_REFERER = $uri['scheme'] . '://' . $uri['host'];
            }
            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_REFERER => self::$CURLOPT_REFERER,
                CURLOPT_USERAGENT => self::$CURLOPT_USERAGENT,
                CURLOPT_ENCODING => self::$CURLOPT_ENCODING,
                CURLOPT_TIMEOUT => self::$CURLOPT_TIMEOUT, //数据传输的最大允许时间
                CURLOPT_CONNECTTIMEOUT => self::$CURLOPT_CONNECTTIMEOUT, //连接超时时间
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FAILONERROR => 0,
                CURLOPT_HEADER => 0,
                CURLOPT_NOSIGNAL => true,
                // CURLOPT_DNS_USE_GLOBAL_CACHE => true,
                // CURLOPT_DNS_CACHE_TIMEOUT => 86400,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                // CURLOPT_FOLLOWLOCATION => 1,// 使用自动跳转
                // CURLOPT_MAXREDIRS => 7,//查找次数，防止查找太深
            );
            if (self::$PROXY_URL) {
                $options[CURLOPT_URL] = self::$PROXY_URL.urlencode($url);
            }

            if (self::$CURL_PROXY) {
                $proxy = self::proxy_test();
                $proxy && $options = self::proxy($options, $proxy);
            }
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $responses = curl_exec($ch);
            $info = curl_getinfo($ch);
            $errno = curl_errno($ch);
            if (self::$CURL_HTTP_CODE !== null) {
                if (self::$CURL_HTTP_CODE == $info['http_code']) {
                    return $responses;
                }
            }

            if ($info['http_code'] == 404 || $info['http_code'] == 500) {
                curl_close($ch);
                echo $url . "\n";
                echo "http_code:" . $info['http_code'] . "\n";
                unset($responses, $info);
                return false;
            }
            if (($info['http_code'] == 301 || $info['http_code'] == 302) && $_count < self::$CURL_COUNT) {
                $newurl = $info['redirect_url'];
                if (empty($newurl)) {
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    $header = curl_exec($ch);
                    preg_match('|Location: (.*)|i', $header, $matches);
                    $newurl = ltrim($matches[1], '/');
                    if (empty($newurl)) {
                        return false;
                    }

                    if (!strstr($newurl, 'http://')) {
                        $host = $uri['scheme'] . '://' . $uri['host'];
                        $newurl = $host . '/' . $newurl;
                    }
                }
                $newurl = trim($newurl);
                curl_close($ch);
                unset($responses, $info);
                $_count++;
                return self::remote($newurl, $_count);
            }

            if (self::$CURL_CONTENT_TYPE !== null && $info['content_type']) {
                if (stripos($info['content_type'], self::$CURL_CONTENT_TYPE) === false) {
                    curl_close($ch);
                    echo $url . "\n";
                    echo "content_type:" . $info['content_type'] . "\n";
                    unset($responses, $info);
                    return false;
                }
            }

            if ($errno > 0 || empty($responses) || empty($info['http_code'])) {
                if ($_count < self::$CURL_COUNT) {
                    $_count++;
                    curl_close($ch);
                    unset($responses, $info);
                    return self::remote($url, $_count);
                } else {
                    $curl_error = curl_error($ch);
                    curl_close($ch);
                    unset($responses, $info);
                    echo $url . " remote:{$_count}\n";
                    echo "cURL Error ($errno): $curl_error\n";
                    return false;
                }
            }
            curl_close($ch);
        } elseif (ini_get('allow_url_fopen') && ($handle = fopen($url, 'rb'))) {
            if (function_exists('stream_get_contents')) {
                $responses = stream_get_contents($handle);
            } else {
                while (!feof($handle) && connection_status() == 0) {
                    $responses .= fread($handle, 8192);
                }
            }
            fclose($handle);
        } else {
            $responses = file_get_contents(urlencode($url));
        }
        return $responses;
    }
}
