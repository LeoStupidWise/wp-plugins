<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/10
 * Time: 16:53
 */

/*
Plugin Name: yazhou-test
Plugin URI: https://www.baidu.com
Description: This is a plugin tested by yazhou.
Version: 0.0
Author: Yazhou
Author URI: https://automattic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: akismet
*/

function yazhouActivationHook() {
    // 插件启动，添加一个默认的版权信息选项
    update_option("yz_test_copyright_text", "<p style='color: red'>本站点所有文章均为原创，转载请注明出处！</p>");
}

register_activation_hook(__FILE__, 'yazhouActivationHook');

add_action('wp_footer', 'yazhouTestFooter');

function yazhouTestFooter() {
    echo get_option('yz_test_copyright_text');
}


// 在页首添加信息，wp_head钩子
//add_action('wp_head', 'yazhouTestHead');


//function yazhouTestHead() {
//    echo "<script> alert('Hello world'); </script>";
//}



/*
 * Lesson 6：过滤器
 * 过滤器有返回值，而 action 是没有的
 * the_content过滤器钩子是在文章内容输出之前，在 wp_inckudes/post-template.php中可以找到the_content的用法
 */

//$value    =  'Hello';
// 必须先定义过滤器，然后才能使用，即先 add_filter, 然后才能 apply_filter
//add_filter('yazhouTestFilter', 'yazhouTestFilterFunc');
//function yazhouTestFilterFunc($value) {
//    return $value." World";
//}

// 可以对一个名称的过滤器进行多次过滤操作
// add_filter('yazhouTestFilter', 'yazhouTestFilterFuncTime');
// function yazhouTestFilterFuncTime($value) {
//     return date('Y-m-d H:i:s').$value;
// }

// 对 the_content 过滤器的一次实验
//add_filter('the_content', 'yazhouTestFilterFunc');
//add_filter('the_content', 'yazhouTestFilterFuncTime');

// $myvar    =  apply_filters('yazhouTestFilter', $value);
// 如果没有 yazhouTestFilter 这个过滤器，将返回$value原值
// echo $myvar;



/*
 * Lesson 7：带参数的过滤器
 * 优先级越小，越靠前执行（1比5要先执行），优先级在过滤器挂载方法的时候可以进行设置，默认优先级是10
 * 过滤器默认只接受一个参数
 */



/*
 * Lesson 8：认识钩子、常用过滤器实用案例
 *  常用过滤器：
 *      文章：
 *          the_content（输出内容时进行过滤）
 *          content_save_pre（文章保存之前进行过滤，就是在文章编辑的时候），使用这个钩子可以删除站外链接、图片等等
 *          the_title（对标题进行过滤）
 *      附件：
 *          wp_handle_upload_prefilter，比如图片上传的时候，如果是一张中文命名的图片，在WP中因为编码是无法正常显示的
 *      评论：
*           content_text：评论输出的时候进行过滤
 */

//add_filter('content_save+pre', 'yazhouTestAutoLink');

//function yazhouTestAutoLink($content) {
//    return str_replace('zoe', "<a href='http://yazhou.com'>zoe</a>", $content);
//}

//add_filter('wp_handle_upload_prefilter', 'yazhouTestUploadPrefilter');

//function yazhouTestUploadPrefilter($file) {
//    $time    =  date('Y-m-d');
//    $file['name']    =  $time."".mt_rand(1, 100).".".pathinfo($file['name'], PATHINFO_EXTENSION);
//    return $file;
//}



/*
 * Lesson 9：后台整合，创建菜单和子菜单
 * add_menu_page
 *  page_title：页面的title，和显示在<title>标签里的一样
 *  menu_title：在控制面板中显示的名称
 *  capability：要浏览菜单所需要的最低权限
 *  menu_slug：要引用该菜单的别名，必须是唯一，别名会显示在 url 里面
 *  function：要显示菜单对应的页面内容所调用的函数
 *  icon_url：菜单icon图片的url
 *  position：出现在菜单列表中的次序
 *
 * add_submenu_page
 *  parent_slug：父级菜单的别名
 *  page_title：
 *  menu_title：
 *  capability：
 *  menu_slug：别名会显示在url 里面
 *  function：
 */

//add_action('admin_menu', 'yazhouTestCreateMenu');

//function yazhouTestCreateMenu() {
//    add_menu_page(
//        'Yazhou插件首页',
//        'Yazhou的插件',
//        'manage_options',
//        'yazhou_test',
//        'yazhouTestPages',
//        plugins_url('/images/icon.jpg', __FILE__)
//    );

//    add_submenu_page(
//        'yazhou_test',
//        '关于yazhou的插件',
//        '关于',
//        'manage_options',
//        'yazhou_test_about',
//        'yazhouTestSubPages'
//    );
//}

//function yazhouTestSubPages() {
//    ?>
<!--    <h2>子菜单</h2>-->
<!--    --><?//
//}

//function yazhouTestPages() {
//    ?>
<!--        <h2>插件顶级菜单</h2>-->
<!--    --><?//
//}



/*
 *  Lesson 10：后台整合，创建小工具
 *      使用 WP_Widget 类来创建小工具
 *          声明
 *          构造函数
 *          小工具管理界面
 *          保存小工具设置
 *          显示小工具
 *      注册小工具
 *          add_action('widgets_init', 'hc_register_widgets');
 *          function hc_register_widgets() {
 *              register_widget('hc_widget_info');
 *          }
 */

class My_Widget extends WP_Widget {
    public function __construct()
    {
        $widget_ops    =  [
            'class_name'    => 'my_widget',
            'description'   => 'An awesome widget'
        ];
        parent::__construct('my_widget', 'My Widget', $widget_ops);
    }
}

add_action('widgets_init', 'yazhouTestWidget');

function yazhouTestWidget () {
    register_widget('My_Widget');
}



/*
 * Lesson 11：后台整合，元数据框
 *  
 */