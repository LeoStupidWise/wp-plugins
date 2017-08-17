<?php
/*
Plugin Name: 逐浪微博登录
Plugin URI:
Description: 支持微博登录站点。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

session_start();

class ZhulangWeiboLogin
{
    private $name_cn             =  '逐浪微博登录';
    private $name_en             =  'zhulangWeiboLogin';
    private $file_name           =  'zhulang-weibologin';
    private $download_prefix    =  'did_download_';
    private $wp_db               =  '';
    private $tb_social_suffix   =  'zoe_users_social';
    private $tb_connection_suffix    =  'zoe_users_local_social';
    private $tb_social    =  '';
    private $tb_connection    =  '';

    public function __construct()
    {
        global $wpdb;
        date_default_timezone_set('Asia/Shanghai');
        $this->wp_db    =  $wpdb;
        $this->tb_social    =  $this->wp_db->prefix.$this->tb_social_suffix;
        $this->tb_connection    =  $this->wp_db->prefix.$this->tb_connection_suffix;
    }

    public function doAction() {
        $download_option    =  $this->download_prefix.$this->name_en;
        if (!get_option($download_option)) {
            $this->doPluginDownload([
                'cn'    => $this->name_cn,
                'en'    => $this->name_en
            ]);
            update_option($download_option, 1);
        }

        if (!$this->haveTable($this->wp_db->prefix.$this->tb_social_suffix)) {
            $this->createTableSocial();
        }
        if (!$this->haveTable($this->wp_db->prefix.$this->tb_connection_suffix)) {
            $this->createTableSocialConnection();
        }

        add_action('login_form', [$this, 'zoe_login_message']);
        // 增加微博登录图标

        add_action('login_form', [$this, 'didWeiboLogin']);
        // 是否微博登录

        add_action('wp_logout', [$this, 'userLogout']);
        // 登出是释放 session

        add_action('admin_menu', [$this, 'adminMenu']);
    }

    public function getClientIP()
    {
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }

    public function doPluginDownload($plugin_name = null) {
        // 这里的方法属性不能是 private，如果是 private，就会出现错误，只能是 public
        // 上面说的不能是 private 的前提是在该方法被 didPluginDownload 中钩子 wp_ajax_zhulang_plugin_download 调用的情况下
        $data['name_cn']        =  $plugin_name ? $plugin_name['cn'] : $_POST['name_cn'];
        $data['name_en']        =  $plugin_name ? $plugin_name['en'] : $_POST['name_en'];
        $data['ip']              =  $this->getClientIP();
        $data['user_agent']     =  $_SERVER['HTTP_USER_AGENT'];
        $data['remote_url']     =  $_SERVER['HTTP_REFERER'];
        $data['remote_url']     =  strlen($data['remote_url']) > 100 ? '' : $data['remote_url'];

        $result    =  $this->phpPostSec($data, 'http://test.yz/analysis_wp_plugin.php');
        if (!$plugin_name) {
            wp_die();
        }
    }

    public function phpPostSec($data, $url) {
        // php 进行 http post 模拟，这个方法可用
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

    public function haveTable($table_name) {
        // 数据表中是否有对应的表格
        // 有就会返回表名，没有返回 null
        $sql    =  "SHOW TABLES LIKE '$table_name'";
        return $this->wp_db->get_var($sql);
    }

    private function createTableSocial() {
        // 创建第三方用户表
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");  //引用wordpress的内置方法库

        $sql    =  "DROP TABLE IF EXISTS `wp_zoe_users_social`;
            CREATE TABLE `wp_zoe_users_social` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `type` smallint(2) DEFAULT NULL COMMENT '对应的第三方账号类型，1：微博，2：微信，3：qq',
              `uid` int(11) DEFAULT NULL,
              `identifier` varchar(256) DEFAULT NULL COMMENT '昵称',
              `created_at` datetime DEFAULT NULL COMMENT '创建时间',
              `img` text,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

        // 创建数据表的时候不能使用 wpdb->query()，需要 require_once(ABSPATH . "wp-admin/includes/upgrade.php")，然后使用 dbDelta。
        dbDelta($sql);
//        $this->wp_db->query($sql);
    }

    private function createTableSocialConnection() {
        // 创建关联表
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");  //引用wordpress的内置方法库

        $sql    =  "DROP TABLE IF EXISTS `wp_zoe_users_local_social`;
            CREATE TABLE `wp_zoe_users_local_social` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_local_id` int(11) DEFAULT NULL COMMENT '本地账号id',
              `user_social_id` int(11) DEFAULT NULL COMMENT '第三方账号id，见库 wp_zoe_users_social',
              `type` SMALLINT(2) DEFAULT NULL COMMENT '1：微博，2：微信，3：QQ',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
//        $this->wp_db->query($sql);
    }

    public function addLoginLogo() {
        echo '<style type="text/css">
            .login h1 a {
            background-image:url("./images/logo-test.jpg") !important;
            height: 22px;
            width: 22px;
            -webkit-background-size: 22px;
            background-size: 22px;
        }
    </style>';
    }

    public function zoe_login_message() {
        $img_url    =  plugins_url("/".$this->file_name."/images/logo-test.jpg");
        $weibo_index=  plugins_url("/".$this->file_name."/weibo/index.php");
        echo "<p><a href='$weibo_index'><img src='$img_url'></a></p><br />";
    }

    function didWeiboLogin() {
//        var_dump($_SESSION);
        if ($_SESSION['zl_weibo_accesstoken']) {
            $social_user_id    =  $this->haveSocialUserWithUid($_SESSION['zl_weibo_uid']);
            if (!$social_user_id) {
                // 进行第三方用户记录
                $this->insertUser($_SESSION['zl_weibo_uid'], $_SESSION['zl_weibo_identifier']);
            }
            // 查看是否绑定本地用户
            $social_user_id    =  $this->haveSocialUserWithUid($_SESSION['zl_weibo_uid']);
            $local_id          =  $this->haveLocalUserWithSocialId($social_user_id);
            if (!$local_id) {
                // 没有绑定本地用户
                echo '<u style="color: red">当前微博账号未绑定站点用户</u>';
//                return;
            } else {
                // 绑定了本地用户
                $local_user    =  $this->getLocalUser($local_id);
                wp_set_current_user($local_id, $local_user->user_login);
                wp_set_auth_cookie($local_id);
                do_action('wp_login', $local_user->user_login);
                header("Location:wp-admin/");
            }
        } else {
            $this->insertUser($_SESSION['zl_weibo_uid'], $_SESSION['zl_weibo_identifier']);
        }
    }

    public function getTypeNum($type) {
        // 获取第三方对应的存储数值
        $type    =  strtolower($type);
        switch ($type) {
            case "weibo":
                return 1;
            case "wechat":
                return 2;
            case "qq":
                return 3;
            default:
                return 0;
        }
    }

    public function haveSocialUserWithUid($uid) {
        // 使用query，进行 select 返回结果数据条数
        // 使用 get_results 返回一个对象数组，即包括几个对象的一个数组
        $sql    =  "SELECT id FROM $this->tb_social WHERE uid = $uid";
        $result =  $this->wp_db->get_results($sql);
        if (count($result) > 0) {
            $result    =  $result[0];
            $result    =  $result->id;
        }
        return $result;
        // 最后返回的是第三方账号在数据库的id
    }

    public function haveLocalUserWithSocialId($social_id) {
        // 使用第三方用户的 id 查找是否有对应的本地用户
        $sql    =  "SELECT user_local_id FROM $this->tb_connection WHERE user_social_id = $social_id";
        $result =  $this->wp_db->get_results($sql);
        if (count($result) > 0) {
            $result    =  $result[0];
            $result    =  $result->user_local_id;
        }
        return $result;
    }

    public function insertUser($uid, $identifier=null, $img=null) {
        $time   =  date('Y-m-d H:i:s');
        $sql    =  "INSERT INTO $this->tb_social (type, uid, identifier, created_at, img) VALUES (1, $uid, '$identifier', '$time', '$img')";
        return $this->wp_db->query($sql);
    }

    public function insertSocialUserIdentifier($identifier) {
        $time    =  date("Y-m-d H:i:s");
        $sql     =  "INSERT INTO $this->tb_social (type, identifier, created_at) VALUES (1, '$identifier', '$time')";
        return $this->wp_db->query($sql);
    }

    public function getLocalUser($user_id) {
        $sql    =  "SELECT * FROM wp_users WHERE ID = $user_id";
        $result =  $this->wp_db->get_results($sql);
        if (count($result) > 0) {
            $result    =  $result[0];
        }
        return $result;
    }

    public function getSocialUser($social_id) {
        $sql    =  "SELECT * FROM $this->tb_social WHERE id = $social_id";
        $result =  $this->wp_db->get_results($sql);
        if (count($result) > 0) {
            $result    =  $result[0];
        }
        return $result;
    }

    public function getCorrespondingLocalUser() {
        //
    }

    public function userLogout() {
        if ($_SESSION['zl_weibo_accesstoken']) {
            $_SESSION['zl_weibo_uid']    =  null;
            $_SESSION['zl_weibo_accesstoken']    =  null;
        }
    }

    public function adminMenu() {
        add_options_page( $this->name_cn , $this->name_cn , 8 , basename(__FILE__) , [$this, 'adminMenuDetail']);
    }

    public function getSocialByIdentifier($identifier) {
        $sql    =  "SELECT id FROM $this->tb_social WHERE identifier = '$identifier'";
        $result =  $this->wp_db->get_results($sql);
        if (count($result) > 0) {
            $result    =  $result[0];
        }
        return $result;
        // 返回的是一个对象，或者一个空数组
    }

    public function attachSocialUserToLocal($social_id, $local_id) {
        // 绑定第三方到本地账户
        // 本地账号是否绑定过，有两种策略，找到已有的进行更新，或者删除所有已有的，这里使用删除所有已有的
        $this->detachSocialUserToLocal($local_id);
        $sql    =  "INSERT INTO $this->tb_connection (user_local_id, user_social_id, type) VALUES ($local_id, $social_id, 1)";
        $result =  $this->wp_db->query($sql);
        return $result;
    }

    public function detachSocialUserToLocal($local_id, $social_id=null) {
        // 解绑第三方和本地用户，如果没有提供 social_id 即解绑所有 local_id 对应的第三方
        if (!$social_id) {
            $sql    =  "DELETE FROM $this->tb_connection WHERE user_local_id = $local_id AND type = 1";
        } else {
            $sql    =  "DELETE FROM $this->tb_connection WHERE user_local_id = $local_id AND user_social_id = $social_id AND type = 1";
        }
        $result    =  $this->wp_db->query($sql);
        return $result;
    }

    public function getSocialUserByLocalId($local_id) {
        // 通过本地用户 id 获取第三方信息
        $sql    =  "SELECT * FROM $this->tb_connection WHERE user_local_id = $local_id";
        $result =  $this->wp_db->get_results($sql);
        if (!count($result) > 0) {
            return null;
        }
        $result =  $result[0];
        $result =  $this->getSocialUser($result->user_social_id);
        return $result;
    }

    public function adminMenuDetail() {
        $submitted    =  false;
        $current_user =  wp_get_current_user();
        $current_correspond_social    =  $this->getSocialUserByLocalId($current_user->ID);
        // 当前用户对应的第三方用户，可能是不存在的在这里，即用的时候要做空判断
        if ($current_correspond_social) {
            $current_social_identifier    =  $current_correspond_social->identifier;
        } else {
            $current_social_identifier    =  '';
        }
        $identifier   =  $_POST['weibo_identifier'];
        if ($identifier) {
            if ($social_user = $this->getSocialByIdentifier($identifier)) {
                // 已经存在第三方账号
                if ($local_id = $this->haveLocalUserWithSocialId($social_user->id)) {
                    // 第三方账号有没有被绑定
                    echo '微博已被绑定';
                } else {
                    $attach_result    =  $this->attachSocialUserToLocal($social_user->id, $current_user->ID);
                    if ($attach_result) {
                        $current_social_identifier    =  $identifier;
                        echo '绑定成功A';
                    } else {
                        echo '绑定失败A';
                    }
                }
            } else {
                // 未存在第三方账号
                $this->insertSocialUserIdentifier($identifier);
                $social_user = $this->getSocialByIdentifier($identifier);
                $attach_result    =  $this->attachSocialUserToLocal($social_user->id, $current_user->ID);
                if ($attach_result) {
                    $current_social_identifier    =  $identifier;
                    echo '绑定成功B';
                } else {
                    echo '绑定失败B';
                }
            }
            $submitted    =  true;
        }

        echo '<div class="wrap">';
        echo '<form name="menu_form" method="post" action="">';
        echo '<p style="font-weight:bold;">在此进行微博账号关联</p>';
        echo '<p>微博账号<u style="color: red">昵称</u>：<input type="text" name="weibo_identifier" value="'.$current_social_identifier.'"></p>';
        echo '<p class="submit"><input type="submit" value="保存设置"/>';
        echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
        echo '</form>';
        echo '</div>';
    }
}

$object    =  new ZhulangWeiboLogin();
$object->doAction();