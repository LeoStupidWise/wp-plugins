<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/15
 * Time: 16:26
 */

require_once 'config.php';
require_once 'saetv2.ex.class.php';

$object    =  new SaeTOAuthV2(WB_KEY, WB_SEC);
$url       =  HOME_URL.'/'.WB_CALLBACK;
$oauth     =  $object->getAuthorizeURL($url);

var_dump($oauth);
// 得到了微博授权登录地址

header('Location:'.$oauth);