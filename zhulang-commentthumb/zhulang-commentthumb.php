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
    private $table_name_judge  =  'wp_zoe_user_judge';         // 用户评分表
    private $img_did_thumb      =  'images/remove.png';         // 已点赞图标，红色
    private $img_do_thumb       =  'images/add.png';            // 未点赞图标，黑色
    private $img_black_dark     =  'images/red.jpg';            // 深色评分按钮
    private $img_black_light    =  'images/black-light.jpg';   // 浅色评分按钮
    private $stars_num           =  5;                             // 评分初始星星数

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

        add_action("wp_ajax_do_user_thumb_up_judge", [$this,'doUserThumbUpJudge']);
        add_action("wp_ajax_nopriv_do_user_thumb_up_judge", [$this,'doUserThumbUpJudge']);

        add_filter('edit_comment_link', [$this, 'thumbLogo']);
        // 在评论出增加图标
        $this->getCurrentUser();
        // 获取当前用户
    }

    public function addJSLayer() {
        $script    =  $this->full_url_prefix.'/js/layer.js';
        echo"<script src='$script'></script>";
    }

    public function thumbLogo() {
        $comment_id  =  get_comment_ID();
        if ($this->didUserThumbUpComment($this->current_user_id, $comment_id)) {
            $img_src    =  $this->img_did_thumb;
        } else {
            $img_src    =  $this->img_do_thumb;
        }
        $img_like    =  $this->full_url_prefix.'/'.$img_src;

        $judge_str   =  $this->getJudgedImg($this->current_user_id, $comment_id);
        echo "<a><img src=$img_like class='zl_comment_thumb_do_like' data-comment-id='$comment_id'></a><br/>".$judge_str;
    }

    public function do_user_thumb_up() {
        // 进行用户点赞
        // 不知道怎么的，AJAX就是回调不到这里来
        // 是可以传过来的，我参数名称写错了

        $comment_id    =  $_POST['comment_id'];
        $user_id       =  $_POST['user_id'];

        if (is_user_logged_in()) {
            // 用户登陆后才允许点赞
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
                        'msg'     => 'success',
                        'img'     => $this->full_url_prefix.'/images/remove.png'
                    ];
                } else {
                    $output    =  [
                        'code'    => '5000',
                        'msg'     => 'wrong'
                    ];
                }
            }
        } else {
            $output    =  [
                'code'    => '4000',
                'msg'     => 'need log in'
            ];
        }
        echo json_encode($output);
        exit();
        // 不加 exit 会在返回的时候多输出一个数字0，考虑应该是 php 在输出的时候有一些其他处理，或者 WP 有一些默认处理
        // 这边 echo 的是一个字符串，JS 在接收后需要做字符串转 JSON 处理， var data_obj    =  eval('(' + data + ')');
        // SW.Leo 2017-8-17
    }

    public function doUserThumbUpJudge() {
        // 进行用户评分
        $score        =  $_POST['score'];
        $user_id      =  $_POST['user_id'];
        $comment_id   =  $_POST['comment_id'];
        if (is_user_logged_in()) {
            // 用户登陆后才允许点赞
            if ($this->didUserJudgeComment($user_id, $comment_id)) {
                // 如果该用户已经对评论评过分
                $output    =  [
                    'code'    => '3000',
                    'msg'     => 'you have already judge'
                ];
            } else {
                if ($result=$this->doUserJudgeComment($user_id, $comment_id, $score)) {
                    $output    =  [
                        'code'    => '2000',
                        'msg'     => 'success',
                        'img'     => $this->full_url_prefix.'/'.$this->img_black_dark
                    ];
                } else {
                    $output    =  [
                        'code'    => '5000',
                        'msg'     => 'wrong'
                    ];
                }
            }
        } else {
            $output    =  [
                'code'    => '4000',
                'msg'     => 'need log in'
            ];
        }
        echo json_encode($output);
        exit();
    }

    public function getCurrentUser() {
        global $current_user;
        setcookie('zl_current_user_id_'.$this->name_en, $current_user->ID);
        $this->current_user_id    =  $current_user->ID;
    }

    public function createTable() {
        // 创建数据表用来保存点赞记录
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");  //引用wordpress的内置方法库
        // 创建数据库的时候，这里是创建了两个表，如果前一个表已经存在，后一个表的创建会失败
        // 只有在两个表都不存在的时候，两个表才能一起创建成功
        // SW.Leo 2017-8-18 16:26:53

        $sql    =  "DROP TABLE IF EXISTS `$this->table_name`;
            CREATE TABLE `$this->table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) DEFAULT NULL COMMENT '用户id',
              `comment_id` int(11) DEFAULT NULL COMMENT '评论id',
              `created_at` datetime DEFAULT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            DROP TABLE IF EXISTS `$this->table_name_judge`;
            CREATE TABLE `$this->table_name_judge` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) DEFAULT NULL COMMENT '用户id',
              `comment_id` int(11) DEFAULT NULL COMMENT '评论id',
              `score` SMALLINT(2) DEFAULT NULL COMMENT '用户评分',
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

    public function didUserJudgeComment($user_id, $comment_id) {
        // 用户有没有对评论进行过评分
        $sql    =  "SELECT * FROM $this->table_name_judge WHERE user_id = $user_id AND comment_id = $comment_id";
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

    public function doUserJudgeComment($user_id, $comment_id, $score) {
        // 用户对评论进行点赞
        $time   =  date('Y-m-d H:i:s');
        $sql    =  "INSERT INTO $this->table_name_judge (user_id, comment_id, score, created_at) VALUES ($user_id, $comment_id, $score, '$time')";
        return $this->wp_db->query($sql);
    }

    public function getInitLikeImg($comment_id, $counter=5) {
        // 获取评分的星星
        // zl_comment_thumb_do_like
        /*<a><img src=$img_like class='zl_comment_thumb_do_like' data-comment-id='$comment_id'></a>*/
        $img_src     =  $this->img_black_light;
        $img_judge   =  $this->full_url_prefix.'/'.$img_src;
        $str         =  '';
        for ($i=0; $i<$counter; $i++ ) {
            $j       =  $i + 1;
            $str     .= "<a><img style='margin-right: 4px' class='zl_comment_thumb_do_judge' src=$img_judge data-comment-id='$comment_id' data-judge-id='$j'></a>";
        }
        return $str;
    }

    public function getJudgedImg($user_id, $comment_id) {
        // 获取评分后的星星链接
        // $num：星星个数
        $str    =  '';
        if ($judge = $this->didUserJudgeComment($user_id, $comment_id)) {
            $score    =  $judge->score;
            $img_src  =  $this->img_black_dark;
        } else {
            $score    =  $this->stars_num;
            $img_src  =  $this->img_black_light;
        }
        $img_src      =  $this->full_url_prefix.'/'.$img_src;
        for ($i=1; $i<=$score; $i++) {
            $str     .= "<a><img style='margin-right: 4px' class='zl_comment_thumb_do_judge' src=$img_src data-comment-id='$comment_id' data-judge-id='$i'></a>";
        }
        return $str;
    }
}


$object    =  new ZhulangCommentThumb();
$object->doAction();