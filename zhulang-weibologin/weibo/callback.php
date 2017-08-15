<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/15
 * Time: 11:25
 */

require_once 'config.php';
require_once 'saetv2.ex.class.php';

$code    =  $_GET['code'];

$keys['code']    =  $code;
$keys['redirect_uri']    =  WB_CALLBACK;

$object  =  new SaeTOAuthV2(WB_KEY, WB_SEC);
$oauth   =  $object->getAccessToken('code', $keys);

/* Oauth：
 * Array
(
    [access_token] => 2.00S9M2iGvSOj_Daae152a79a5EuiuC
    [remind_in] => 137189
    [expires_in] => 137189
    [uid] => 6152008546
)
 */

// setcookie('weibo_accesstoken', $oauth['access_token'], time()+$oauth['expires_in']);
// setcookie('weibo_uid', $oauth['uid'], time()+$oauth['expires_in']);

$_SESSION['zl_weibo_accesstoken']    =   $oauth['access_token'];
$_SESSION['zl_weibo_uid']    =  $oauth['uid'];

$client_obj   =  new SaeTClientV2(WB_KEY, WB_SEC, $oauth['access_token']);
$user    =  $client_obj->show_user_by_id($oauth['uid']);

header('Location: http://test.wp.yz/wp-login.php');