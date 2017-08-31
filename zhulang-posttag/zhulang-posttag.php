<?php
/*
Plugin Name: 逐浪文章标签
Plugin URI:
Description: 提供文章在列表页的标签筛选。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

require_once 'helper.php';

class ZhulangPostTag
{
    /*
     * 1. 进行标签筛选是在每个分类页面（加上主页）之前获取已选标签的查询
     *      在插件中使用 $query->set('tag_slug__in', []) 页面会一直处于正在加载状态，不能正常运行
     *      在 主题shop-isle 中可以较好的展示，在 主题2017 中无法正常使用
     *      So strange，后面尝试使用插件的方式，通过 $query->set() 又可以了...
     *      但经过后面的测试发现，pre_get_posts 是所有获取文章的地方，如果在插件中写，所有地方的文章都会被筛选，不仅仅是主体部分
     *          同时导航栏也会没有
     *      所以还是要在分类模板进行修改
     * */
    public $name_cn = '逐浪文章标签';
    public $name_en = 'zhulangPostTag';
    private $plugin_file_name = 'zhulang-posttag';
    private $full_url_prefix = '';
    private $option_prefix = '';
    private $author = 'zoe';
    private $wp_db = '';
    private $menu_page_slug = '';          // 一起请求一篇文章只记录一次浏览，当此属性为true时，禁止插入新的浏览记录
    private $css_name = 'css/main.css';

    public function __construct()
    {
        global $wpdb;
        date_default_timezone_set('Asia/Shanghai');
        $this->plugin_file_name = dirname(plugin_basename(__FILE__));
        $this->full_url_prefix = home_url('wp-content/plugins/' . $this->plugin_file_name);
        $this->option_prefix = 'zl_zoe_' . $this->name_en;
        $this->menu_page_slug = $this->author . '_' . $this->name_en . '_page_menu_top';
        $this->wp_db = $wpdb;
    }

    public function registerWidget() {
        register_widget('ZhulangPostTagWidget');
    }

    public function doPostTagOrder($query) {
        if (!is_category() && !is_admin()) {
            $tags_checked    =  $_POST['zoe_post_tag'];
            if (count($tags_checked) > 0) {
                $query->set('tag_slug__in', $tags_checked);
            }
        }
    }

    public function doAction()
    {
//        add_action('the_post', [$this, 'test']);

//        add_action('pre_get_posts', [$this, 'doPostTagOrder']);
//         获取文章前对所属标签进行筛选
        wp_register_style('zoe-tag-main', $this->full_url_prefix.'/'.$this->css_name);
        wp_enqueue_style('zoe-tag-main');
        add_action('zoe_posttag_tag_show', [$this, 'test']);

//        add_action( 'widgets_init', [$this, 'registerWidget']);
        // 注册小工具
    }

    public function test() {
        if (is_category() || is_home()) {
            // 只让这个小工具在主页显示
            $tags    =  get_tags(['get'=>'all']);
            if (count($tags) > 0) {
                if (isset($_POST['zoe_post_tag'])) {
                    $tags_checked    =  $_POST['zoe_post_tag'];
                } else {
                    $tags_checked    =  [];
                }
//                echo '<div class="zoe_plugin_post_tag_border" style="margin-bottom: 4px">';
                echo "<div class='col-sm-4 col-sm-offset-4'>";
                echo '<div class="zoe_plugin_post_tag_border">';
                    echo '<form class="zoe_plugin_post_tag_form" action="" method="post">';
                        foreach ($tags as $tag) {
                            $is_checked    =  in_array($tag->slug, $tags_checked) ? "checked='checked'" : '' ;
//                            echo "<label class='zoe_plugin_post_tag_single' style='margin-right: 10px;'><input name='zoe_post_tag[]' $is_checked type='checkbox' value='$tag->slug'>$tag->name</label>";
                            echo "<label class='zoe_plugin_post_tag_single'><input name='zoe_post_tag[]' $is_checked type='checkbox' value='$tag->slug'>$tag->name</label>";
//                            echo "<input class='zoe_plugin_post_tag_single' name='zoe_post_tag[]' $is_checked type='checkbox' value='$tag->slug'>$tag->name";
                        }
//                        echo '<input class="zoe_plugin_post_tag_confirm" style="height:20px; width:40px; display:block; text-align:center; line-height:1px; inline-size:auto" type="submit" value="确定">';
                        echo '<input class="zoe_plugin_post_tag_confirm" type="submit" value="确定">';
                    echo '</form>';
                echo '</div>';
                echo '</div>';
                echo '<br/>';
                echo '<br/>';
            }
        }
    }
}

class ZhulangPostTagWidget extends WP_Widget
{
    public $id_base    =  '';

    public function __construct()
    {
        $id_base    =  'zhulangposttagwidget';
        $this->id_base    =  $id_base;
        /*Optional Base ID for the widget, lowercase and unique. If left empty,a portion of the widget's class name will be used Has to be unique.*/

        $name       =  '逐浪文章标签小工具';
        /*Name for the widget displayed on the configuration page.*/

        $widget_options    =  [
            'classname'    => 'ZhulangPostTagWidget',
            'description'  => '通过标签来筛选文章。'
        ];

        $control_options   =  'widgets_control';
        parent::__construct($id_base, $name, $widget_options, $control_options);
    }

    public function form($instance) {
        //
    }

    public function update($new_instance, $old_instance) {
        //
    }

    public function widget($args, $instance) {
        // 不知道 WP 加载小工具的流程是怎么样的，这里使用 get 或者 post 完全传不到前台小工具
        if (is_category() || is_home()) {
            // 只让这个小工具在主页显示
            $tags    =  get_tags(['get'=>'all']);
            if (count($tags) > 0) {
                if (isset($_POST['zoe_post_tag'])) {
                    $tags_checked    =  $_POST['zoe_post_tag'];
                } else {
                    $tags_checked    =  [];
                }
                echo '<div style="margin-bottom: 4px">';
                    echo '<form action="" method="post">';
                        foreach ($tags as $tag) {
                            $is_checked    =  in_array($tag->slug, $tags_checked) ? "checked='checked'" : '' ;
                            echo "<label style='margin-right: 10px;'><input name='zoe_post_tag[]' $is_checked type='checkbox' value='$tag->slug'>$tag->name</label>";
                        }
                        echo '<input style="height:20px; width:40px; display:block; text-align:center; line-height:1px; inline-size:auto" type="submit" value="确定">';
                    echo '</form>';
                echo '</div>';
                echo '<br/>';
                echo '<br/>';
            }
        }
    }
}

$object    =  new ZhulangPostTag();
$object->doAction();