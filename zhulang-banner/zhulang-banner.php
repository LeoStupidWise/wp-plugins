<?php
/*
Plugin Name: 逐浪轮播图
Plugin URI:
Description: 为站点增加一个轮播图。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

class ZhulangBanner
{
    /*
     * 把轮播图的信息都放在 option 里面，按照一定格式，存的时候存 JSON
     * 格式可能是这样子的
     * [
	    {
		    'name'    => '整个轮播的名称',            // 一个站点可能有多个轮播，怎么设置页面和轮播的对应关系呢
            'start'   => '是否启用'                   // 该轮播组当前是否启用，1：启用，0：未启用
            'place'   => '显示位置',                  // 轮播显示的位置，home - 主页，或者是分类的 slug
                                                      // 是不是可以给分类加一个 meta 来关联轮播
            'height'  =>                              // 显示时的高，应该有这个，先不做
            'length'  =>                              // 显示时的宽，先不考虑
            'images'  => [
                {
                    'url'    =>                   // 单个轮播的地址
                    'order'  =>                   // 显示的顺序
                    'name'   =>                   // 单个图片的显示标题
                    'link'   =>                   // 单个图片的链接地址
                }
            // 可能还有更多的属性，比如单张图片的显示时间，图片的切换方式，等等，后面可以进行扩展
             .... 多个轮播图片
            ]
	    }
    ...
       ]
    这是一个轮播插件，插件里面有多个轮播组，一个轮播组里面有多张轮播图
    这是一个对象数组，并且这个数组可能是空的
    如果存在多个轮播组对象的 place 值一样，只取第一个
    TODO 第一个版本只有一个轮播组，且只能显示在首页
     * */
    private $name_cn = '逐浪轮播图';
    private $name_en = 'zhulangBanner';
    private $file_name = 'zhulang-banner';
    private $wp_db = '';
    private $option_prefix = '';
    private $option_detail = '';                                   // 轮播插件详情配置选项对应的键名 option_name
    private $option_plugin_menu_slug = '';                        // 组件菜单的 slug
    private $full_url_prefix = '';
    private $main_js_name = 'main.js';
    private $css_name = 'css/main.css';
    private $css_normal_name = 'css/normalize.css';
    private $css_app_name = 'css/app.css';
    private $js_x_slider = 'js/xSlider.js';

    public function __construct()
    {
        global $wpdb;
        date_default_timezone_set('Asia/Shanghai');
        $this->wp_db = $wpdb;
        $this->option_prefix = 'zl_zoe_'.$this->name_en;
        $this->option_detail = $this->option_prefix.'_detail';
        $this->option_plugin_menu_slug = $this->option_prefix.'_menu_slug';
        $this->full_url_prefix = home_url('wp-content/plugins/'.$this->file_name);
    }

    public function createAdminMenu() {
        // 创建后台菜单
        add_menu_page(
        /*$page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null*/
            $this->name_cn,
            $this->name_cn,
            'manage_options',
            $this->option_plugin_menu_slug,
            [$this, 'createAdminMenuHtml'],
            plugins_url('/images/icon.png', __FILE__)
        );
    }

    public function createAdminMenuHtml() {
//        $old_option    =  get_option($this->option_keyword);
        $detail_arr    =  $this->getOptionDetail();
        $group_name    =  empty($detail_arr) ? '' : $detail_arr['name'];
        $start         =  empty($detail_arr) ? 1 : $detail_arr['start'];
        $checked_yes   =  $start ? "checked='checked'" : '';
        $checked_no    =  $start ? '' : "checked='checked'";
        $images        =  empty($detail_arr) ? null : $detail_arr['images'];
        $images_len    =  count($images) ? count($images) : 1;
        echo '<div class="wrap">';
        echo '<form name="option_form" id="zoe_plugin_banner_form" method="post" action="">';
        echo "<p>轮播组标题：<input type='text' name='group_name'>$group_name</p>";
        echo "<p>是否启用：<label><input name='banner_start' type='radio' value='1' $checked_yes/>是</label>
<label><input name='banner_start' type='radio' value='0' $checked_no/>否</label></p><br/>";
        echo "<input name='banner_hidden' type='hidden' value=$images_len id='zoe_plugin_banner_hidden'>";

        if ($images) {
            $count     =  1;
            foreach ($images as $key=>$value) {
                $div_str    =  'div_banner_image_'.$count;
                $url_str    =  'banner_image_'.$count.'_url';
                $order_str  =  'banner_image_'.$count.'_order';
                $name_str   =  'banner_image_'.$count.'_name';
                $link_str   =  'banner_image_'.$count.'_link';
                $delete_str =  'banner_button_delete_'.$count;
                $url        =  $value['url'];
                $order      =  $value['order'];
                $name       =  $value['name'];
                $link       =  $value['link'];
                echo "<div id=$div_str>";
                echo "<p>图片$count</p>";
                echo "<p>图片地址：<input style='width: 600px' type='text' name=$url_str data-num='$count' value='$url'></p>";
                echo "<p>图片显示顺序：<input type='text' name=$order_str data-num='$count' value='$order'></p>";
                echo "<p>图片标题：<input type='text' name=$name_str data-num='$count' value='$name'></p>";
                echo "<p>图片跳转链接：<input style='width: 600px' type='text' name=$link_str data-num='$count' value='$link'></p>";
                echo "<p><input type='button' class='banner_button_delete' value='删除该图片' id=$delete_str data-num='$count'></p>";
                echo "</div>";
                $count ++;
            }
        } else {
            echo "<div id='div_banner_image_1'>";
            echo "<p>图片1</p>";
            echo "<p>图片地址：<input style='width: 600px' type='text' name='banner_image_1_url' data-num='1'></p>";
            echo "<p>图片显示顺序：<input type='text' name='banner_image_1_order' data-num='1'></p>";
            echo "<p>图片标题：<input type='text' name='banner_image_1_name' data-num='1'></p>";
            echo "<p>图片跳转链接：<input style='width: 600px;' type='text' name='banner_image_1_link' data-num='1'></p>";
            echo "<p><input type='button' class='banner_button_delete' value='删除该图片' id='banner_button_delete_1' data-num='1'></p>";
            echo "</div>";
        }

//        if($old_option) {
//            echo '<div><p style="color:blue"><strong>关键词已保存。</strong></p></div>';
//        }
        echo '<p class="submit"><input type="button" value="增加图片" id="banner_button_add">';
        echo '<input type="submit" value="保存设置"/>';
        echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
        echo '</form>';
        echo '</div>';
    }

    public function saveOption() {
        // 保存配置
        if ($_POST) {
            $banner_hidden = $_POST['banner_hidden'];
            $common_prefix = 'banner_image_';
            $url_suffix    = '_url';
            $order_suffix  = '_order';
            $name_suffix   = '_name';
            $link_suffix   = '_link';
            $group_name    =  $_POST['group_name'];
            $banner_start  =  $_POST['banner_start'];
            $json_arr      =  [
                'name' => $group_name,
                'start' => $banner_start,
                'images' => []
            ];
            $max    =  100;
            if ($banner_hidden >= 1) {
                for ($i=1; $i<=$max; $i++) {
                    if (!empty($_POST[$common_prefix.$i.$url_suffix])) {
                        $tmp['url']        =  $_POST[$common_prefix.$i.$url_suffix];
                        $tmp['order']      =  $_POST[$common_prefix.$i.$order_suffix];
                        $tmp['name']       =  $_POST[$common_prefix.$i.$name_suffix];
                        $tmp['link']       =  $_POST[$common_prefix.$i.$link_suffix];
                        $json_arr['images'][]    =  $tmp;
                    }
                }
            }
            $json_storage   =  json_encode($json_arr, JSON_UNESCAPED_UNICODE);
            // TODO：这里没有进行输入检查，比如图片地址没有填也是可以的，无脑往数据库塞
            update_option($this->option_detail, $json_storage);
        }
    }

    public function getOptionDetail() {
        // 获得配置详情
        $detail_json    =  get_option($this->option_detail);
        $detail_arr     =  json_decode($detail_json, true);
        return $detail_arr;
    }

    public function actionWpFooter() {
        // 页面输出 HTML
        $default_img_url_1    =  $this->full_url_prefix.'/images/slider-1.jpg';
        $default_img_url_2    =  $this->full_url_prefix.'/images/slider-2.jpg';
        $default_img_url_3    =  $this->full_url_prefix.'/images/slider-3.jpg';
        // 如果配置中没有图片，使用这三张默认图片
        // 这个轮播最少需要两张图片才能进行循环
        // 主体部分是按顺序循环照片，比如对编号 1、2、3、4、5 的照片进行循环，1,2,3,4,5 是主体部分
        // 主题部分之前是最后一张照片，5是最后一张
        // 主题部分之后是第一章照片，1是第一张
        // 那么最后得出来的顺序是 5、1、2、3、4、5、1

        $option_detail        =  $this->getOptionDetail();
        $images               =  $option_detail['images'];
        if (empty($images)) {
            $this->showDefaultBanner($default_img_url_1, $default_img_url_2, $default_img_url_3);
        } else {
            $this->showBanner($images);
        }
    }

    public function showBanner($images_arr) {
        // 进行前台轮播图展示
        /*          $tmp['url']
                    $tmp['order']
                    $tmp['name']
                    $tmp['link']
        */
        $length    =  count($images_arr);
        if (!($length > 0)) {
            return;
        } else if($length > 1) {
            // 图片排序
            for($i=1;$i<$length;$i++)
            {
                for($k=0;$k<$length-$i;$k++)
                {
                    if($images_arr[$k]['order'] > $images_arr[$k+1]['order'])
                    {
                        $tmp        =  $images_arr[$k+1];
                        $images_arr[$k+1]    =  $images_arr[$k];
                        $images_arr[$k]      =  $tmp;
                    }
                }
            }
        }
        // 如果图片只有1张或2张，拼凑多3张
        if ($length == 1) {
            $images_arr[1]    =  $images_arr[0];
            $images_arr[2]    =  $images_arr[0];
        } else if ($length == 2) {
            $images_arr[2]    =  $images_arr[0];
        }
        // 到这里，照片已经是拍好序的，最少3张的照片
        $len_new    =  count($images_arr);
        foreach($images_arr as $key=>$image) {
            if ($key == 0) {
                $end_url      =  $images_arr[$len_new]['url'];
                $present_url  =  $image['url'];
                echo '<div class="slider">';
                echo '<div class="slider-img">';
                echo '<ul class="slider-img-ul">';
                echo "<li><img src=$end_url></li>";
                echo "<li><img src=$present_url></li>";
            } elseif ($key == ($len_new-1)) {
                $start_url    =  $images_arr[0]['url'];
                $present_url  =  $image['url'];
                echo "<li><img src=$present_url></li>";
                echo "<li><img src=$start_url></li>";
                echo '</ul>';
                echo '</div>';
                echo '</div>';
            } else {
                $present_url   =  $image['url'];
                echo "<li><img src=$present_url></li>";
            }
        }
    }

    public function showDefaultBanner($img_1, $img_2, $img_3) {
        // 没有照片时使用这个进行默认轮播输出
        echo '<div class="slider">';
            echo '<div class="slider-img">';
                echo '<ul class="slider-img-ul">';
                    echo "<li><img src=$img_3></li>";
                    echo "<li><img src=$img_1></li>";
                    echo "<li><img src=$img_2></li>";
                    echo "<li><img src=$img_3></li>";
                    echo "<li><img src=$img_1></li>";
                echo '</ul>';
            echo '</div>';
        echo '</div>';

        /*
         *  <div class="slider">
	            <div class="slider-img">
		            <ul class="slider-img-ul">
			            <li><img src="images/slider-5.jpg"></li>
			            <li><img src="images/slider-1.jpg"></li>
			            <li><img src="images/slider-2.jpg"></li>
                        <li><img src="images/slider-3.jpg"></li>
			            <li><img src="images/slider-4.jpg"></li>
		            	<li><img src="images/slider-5.jpg"></li>
			            <li><img src="images/slider-1.jpg"></li>
		            </ul>
	            </div>
            </div>
         * */
    }

    public function doAction() {
        add_action('admin_menu', [$this, 'createAdminMenu']);
        // 增加菜单
        add_action('zoe_zhulang_banner_before_header', [$this, 'actionWpFooter']);
        if ( is_admin() ) {
            wp_register_script('main-js', $this->full_url_prefix.'/js/'.$this->main_js_name, array('jquery'), '' );
            wp_enqueue_script( 'main-js');
            if ($_GET['page'] == $this->option_plugin_menu_slug) {
                $this->saveOption();
            }
        }
//        if (is_home()) {
            wp_register_style( 'main', $this->full_url_prefix.'/'.$this->css_name);
            wp_register_style( 'normal', $this->full_url_prefix.'/'.$this->css_normal_name);
            wp_register_style( 'app', $this->full_url_prefix.'/'.$this->css_app_name);
            wp_enqueue_style('main');
            wp_enqueue_style('normal');
            wp_enqueue_style('app');
            wp_register_script('x_slider', $this->full_url_prefix.'/'.$this->js_x_slider, array('jquery'), '' );
            wp_enqueue_script( 'x_slider');
    }

    public function test() {
        echo "<script>alert('Ys')</script>";
    }
}

$object = new ZhulangBanner();
$object->doAction();