<?php
/*
Plugin Name: 逐浪评论管理
Plugin URI:
Description: 增加文章评论的点赞功能。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

//require_once 'helper.php';

class ZhulangCommentThumb
{

    public  $name_cn    =  '逐浪评论点赞';
    public  $name_en    =  'zhulangCommentThumb';
    private $full_url_prefix    =  '';
    private $plugin_file_name   =  '';
    public $current_user_id     =  '';
    private $table_name =  'wp_zoe_user_thumb_up';

    public function __construct() {
        global $wpdb;
        date_default_timezone_set('Asia/Shanghai');
        $this->wp_db    =  $wpdb;
        $this->plugin_file_name    =  dirname(plugin_basename(__FILE__));
        $this->full_url_prefix     =  home_url('wp-content/plugins/'.$this->plugin_file_name);
    }

    public function doAction() {
        add_action('init', [$this, 'getCurrentUser']);

        if (!$this->haveTable($this->table_name)) {
            $this->createTable();
        }

        $action_name    =  $this->plugin_file_name.'-script';
        $script_name    =  'do-thumb.js';

        wp_enqueue_script(
            $action_name,
            plugins_url("js/$script_name", __FILE__),
            ['jquery']
        );
        // 引入 JQ

        wp_localize_script(
            $action_name,
            'ajax_object',
            [
                'url'       => admin_url('admin-ajax.php'),
                'user_id'   => $_COOKIE['zl_current_user_id_'.$this->name_en],
            ]);
        // 传递参数到 JS

        add_action("wp_ajax_do_user_thumb_up", [$this,'do_user_thumb_up']);
        add_action('wp_ajax_nopriv_do_user_thumb_up', [$this,'do_user_thumb_up']);

        add_filter('edit_comment_link', [$this, 'thumbLogo']);
        // 在评论出增加图标
    }

    public function thumbLogo() {
        $img_like    =  $this->full_url_prefix.'/images/add.png';
        $comment_id  =  get_comment_ID();
        echo "<a><img src=$img_like class='zl_comment_thumb_do_like' data-comment-id='$comment_id'></a>";
    }

    public function do_user_thumb_up() {
        // 进行用户点赞
        // 不知道怎么的，AJAX就是回调不到这里来
        // 是可以传过来的，我参数名称写错了

        $comment_id    =  $_POST['comment_id'];
        $user_id       =  $_POST['user_id'];

        if ($this->didUserThumbUpComment($user_id, $comment_id)) {
            // 如果该用户已经对评论点过赞

            $output    =  [
                'code'    => '3000',
                'msg'     => 'you have already thumb'
            ];
        } else {
            if ($result=$this->doUserThumbUpComment($user_id, $comment_id)) {
                $output    =  [
                    'code'    => '2000',
                    'msg'     => 'success'
                ];
            } else {
                $output    =  [
                    'code'    => '5000',
                    'msg'     => 'wrong'
                ];
            }
        }
        echo json_encode($output);
        exit();
        // 不加 exit 会在返回的时候多输出一个数字0，考虑应该是 php 在输出的时候有一些其他处理，或者 WP 有一些默认处理
        // SW.Leo
    }

    public function getCurrentUser() {
        global $current_user;
        setcookie('zl_current_user_id_'.$this->name_en, $current_user->ID);
        $this->current_user_id    =  $current_user->ID;
    }

    public function createTable() {
        // 创建数据表用来保存点赞记录
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");  //引用wordpress的内置方法库

        $sql    =  "DROP TABLE IF EXISTS `$this->table_name`;
            CREATE TABLE `$this->table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) DEFAULT NULL COMMENT '用户id',
              `comment_id` int(11) DEFAULT NULL COMMENT '评论id',
              `created_at` datetime DEFAULT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }

    public function haveTable($table_name) {
        // 数据表中是否有对应的表格
        // 有就会返回表名，没有返回 null
        $sql    =  "SHOW TABLES LIKE '$table_name'";
        return $this->wp_db->get_var($sql);
    }

    public function didUserThumbUpComment($user_id, $comment_id) {
        // 用户有没有对评论点过赞
        $sql    =  "SELECT * FROM $this->table_name WHERE user_id = $user_id AND comment_id = $comment_id";
        $result =  $this->wp_db->get_results($sql);
        if (count($result) > 0) {
            $result    =  $result[0];
        }
        return $result;
    }

    public function doUserThumbUpComment($user_id, $comment_id) {
        // 用户对评论进行点赞
        $time   =  date('Y-m-d H:i:s');
        $sql    =  "INSERT INTO $this->table_name (user_id, comment_id, created_at) VALUES ($user_id, $comment_id, '$time')";
        return $this->wp_db->query($sql);
    }
}


$object    =  new ZhulangCommentThumb();
$object->doAction();