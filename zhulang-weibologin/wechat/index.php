<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/25
 * Time: 10:43
 */

$wx_config   =  require_once './config.php';
$redirect_uri=  urlencode($wx_config['redirect']);
//'https://open.weixin.qq.com/connect/qrconnect?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect';
$auth_url    =  $wx_config['basicurl'] . '?appid='.$wx_config['appid'] . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>微信测试</title>
</head>
<body>
<?php

echo "<a href='$auth_url'>微信登录</a>";

//if (!$is_login) {
//    ?>


</body>
</html>