<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/25
 * Time: 10:46
 */

/*
 * array(2) {
 * ["code"]=> string(32) "0617EC2l1Zwg0j0DPI4l1ErJ2l17EC2Q"
 * ["state"]=> string(5) "STATE"
 * }
 * */

/*
 * https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
 * */
session_start();

$config      =  require_once './config.php';
$data        =  $_GET;
$code        =  $data['code'];
$auth_url    =  'https://api.weixin.qq.com/sns/oauth2/access_token';
$appid       =  $config['appid'];
$appsecret   =  $config['appsecret'];
$auth_url    .= "?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";

$result      =  phpPostSec(null, $auth_url);
$result      =  json_decode($result, true);


/*
 * string(332) "
 * {
 * "access_token":"tTkDwG23vvR4901CFu91hJo1ATwyhX_iSrORDYG56QM9ERRVsCQXlRP4SBxISisXTiBx7_YEbe7HMgU5gtJFeg",
 * "expires_in":7200,"refresh_token":"1JNsin-E6oTZEaAjFfADv-Ge33R3gpKSg1Nwbb2p2WJmwaZYYrGoF7jLc5U-u3ugAdVjClC2brvpZYpQmll0ew",
 * "openid":"oEh2zwE01y5RclOrFqSqJlCQCdII",
 * "scope":"snsapi_login",
 * "unionid":"oK8LF0tB1foo2OgTF1UgPkAuZQVQ"
 * }"
 * */

/*
 * https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID
 * */

$user_info_url    =  'https://api.weixin.qq.com/sns/userinfo';
$access_token     =  $result['access_token'];
$openid           =  $result['openid'];
$user_info_url    .= "?access_token=$access_token&openid=$openid";
$user_info        =  phpPostSec(null, $user_info_url);

//$user_info        =  json_decode($user_info, true);
/*
 * string(342) "{
 * "openid":"oEh2zwE01y5RclOrFqSqJlCQCdII",
 * "nickname":"Yz","sex":1,
 * "language":"zh_CN",
 * "city":"Yueyang",
 * "province":"Hunan",
 * "country":"CN",
 * "headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/9L7tv8LIFcUZKTSHPqNiapG9gP05btH2icF6Zic9cicu0XwHXplPozek02dfHsJ2RFQA5jbfz4ZHvteMhibw30J0Zks0icV1TYumJg\/0",
 * "privilege":[],
 * "unionid":"oK8LF0tB1foo2OgTF1UgPkAuZQVQ"
 * }"
 * */

// 关于 access_token 的更新什么鬼的问题，暂时都先不考考虑
// 去接口拿到信息，存到数据库，以后就都用这个信息
// 存的时候，先看表里面有没有对应 openid 的用户，有就不用再存了
// 每次都去请求接口，但不一定存数据库

$_SESSION['zl_weixin_login']    =   $user_info;
$home_url    =  home_url();
header("Location: http://$home_url/wp-login.php");

function phpPostSec($data, $url) {
//     php 进行 http post 模拟，这个方法可用
    $ch = curl_init();
    $timeout = 5;
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
    $file_contents = curl_exec($ch);
    curl_close($ch);

    return $file_contents;
}