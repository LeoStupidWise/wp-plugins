<?php

/*
Plugin Name: 逐浪文章管理
Plugin URI:
Description: 将提供给您文章的时间排序、热度排序、随机排序以及自定义顺序置顶。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

class ZhulangPostManage
{
    public $name_cn                 =  '逐浪文章管理';
    public $name_en                 =  'zhulangPostManage';
    private $plugin_file_name      =  '';
    private $full_url_prefix       =  '';
    private $start_js_name         =  'js/start_v5.js';
    private $tipsy_jd_name         =  'js/jquery.tipsy.js';
    private $css_name               =  'css/zzsc.css';
    private $option_prefix         =  '';
    private $author                 =  'zoe';
    private $wp_db                  =  '';
    private $menu_page_slug_top   =  '';
    private $table_name            =  'wp_zoe_post_click';                  // 文章浏览记录表
    private $view_meta_name        =  '_zoe_views';                         // 文章浏览量额外属性的键名


    public function __construct()
    {
        global $wpdb;
        $this->plugin_file_name    =  dirname(plugin_basename(__FILE__));
        $this->full_url_prefix     =  home_url('wp-content/plugins/' . $this->plugin_file_name);
        $this->option_prefix       =  'zl_zoe_' . $this->name_en;
        $this->menu_page_slug_top =  $this->author . '_' . $this->name_en . '_page_menu_top';
        $this->wp_db                =  $wpdb;
    }

    public function doAction() {
        global $post;
        if (is_single($post->ID)) {
            // 只有当前是文章的时候才进行文章浏览量的统计等等
            if (!$this->haveTable($this->table_name)) {
                $this->createTable();
            }
            echo $post->ID;
            var_dump($this->addPostViewsMeta($post->ID));
            exit();
        } else {
            echo '我特么不是文章';
        }
    }

    public function createTable() {
        // 创建数据表用来保存点赞记录
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");  //引用wordpress的内置方法库

        $sql    =  "DROP TABLE IF EXISTS `$this->table_name`;
            CREATE TABLE `$this->table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `visit_ip` VARCHAR(16) DEFAULT NULL COMMENT '浏览者ip',
              `user_agent` VARCHAR(50) DEFAULT NULL COMMENT 'http_user_agent',
              `created_at` datetime DEFAULT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }

    public function addPostViewsMeta($post_id) {
        // 增加文章的浏览次数额外属性
        $views    =  get_post_meta($post_id, $this->view_meta_name, true);
        return $views;
    }

    public function haveTable($table_name) {
        // 数据表中是否有对应的表格
        // 有就会返回表名，没有返回 null
        $sql    =  "SHOW TABLES LIKE '$table_name'";
        return $this->wp_db->get_var($sql);
    }

    public function addViewTime() {
        // 增加浏览次数一次

    }
}

$subject    =  new ZhulangPostManage();
$subject->doAction();