<?php
/*
Plugin Name: 逐浪基础插件
Plugin URI:
Description: 为逐浪系列插件提供基础服务。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

class ZhulangBasic
{
    /*
     * 1. 文章会有新的表来记录浏览信息，但总浏览量在 postmeta 中可以快速获取
     * */
    public $name_cn                 = '逐浪基础插件';
    public $name_en                 = 'zhulangBasic';
    private $plugin_file_name     = '';
    private $full_url_prefix      = '';
    private $option_prefix        = '';
    private $post_meta_prefix     = '';
    private $author                = 'zoe';
    private $wp_db                 = '';
    private $post_meta_stuck      =  '';                          // 记录该文章是否置顶
    private $the_only_category    =  '';                         // 首页唯一选择的分类


    public function __construct()
    {
        global $wpdb;
        $this->wp_db              = $wpdb;
        date_default_timezone_set('Asia/Shanghai');
        $this->plugin_file_name =  dirname(plugin_basename(__FILE__));
        $this->full_url_prefix  =  home_url('wp-content/plugins/' . $this->plugin_file_name);
        $this->option_prefix    =  'zl_zoe_' . $this->name_en;
        $this->post_meta_prefix =  $this->option_prefix;
        $this->post_meta_stuck   =  '_'.$this->post_meta_prefix.'_post_stuck';
    }

    public function doAction() {
//        add_action('pre_get_posts', [$this, 'getOnlyCategory']);
        // 为文章进行置顶排序
        // SW.Leo
        // 对文章的置顶排序不能在这个钩子里挂载，如果在这，菜单栏就不会显示
        // 将对文章的排序放到 index.php 或者 category-xxx.php 里面

        add_action('save_post', [$this, 'whenPostAdded']);
        // 文章保存后

        add_action('delete_post', [$this, 'whenPostDelete']);
        // 删除文章时
        // 和 deleted_post 不同，delete_post是在文章删除之前，传递post_id，deleted_post是在文章删除之后，传递一个 wp_query

        add_action('post_stuck', [$this, 'whenPostStuck']);
        // 置顶文章时触发

        add_action('post_unstuck', [$this, 'whenPostUnstuck']);
        // 取消文章置顶时触发
    }

    public function whenPostStuck($post_id) {
        update_post_meta($post_id, $this->post_meta_stuck, time());
    }

    public function whenPostAdded($post_id) {
        update_post_meta($post_id, $this->post_meta_stuck, 0);
    }

    public function whenPostDelete($post_id) {
        delete_post_meta($post_id, $this->post_meta_stuck);
    }

    public function whenPostUnstuck($post_id) {
        update_post_meta($post_id, $this->post_meta_stuck, 0);
    }

    public function addStickyOrder($query) {
        if (is_home() || is_category()) {
            $query->set('meta_key', $this->post_meta_stuck);
            $query->set('orderby', ['meta_value_num'=>'DESC']);
        }
    }

    public function getOnlyCategory($query) {
        if (is_home()) {
            $query->set('category_name', 'new');
        }
    }
}

$object    =  new ZhulangBasic();
$object->doAction();