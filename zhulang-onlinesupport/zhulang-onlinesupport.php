<?php
/*
Plugin Name: 逐浪在线客服
Plugin URI:
Description: 将提供给您一个在线客服悬浮框。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

class ZhulangOnlineSupport
{
    public $name_cn = '逐浪在线客服';
    public $name_en = 'zhulangOnlineSupport';
    private $plugin_file_name    =  '';
    private $full_url_prefix     =  '';
    private $start_js_name       =  'js/start_v5.js';
    private $tipsy_jd_name       =  'js/jquery.tipsy.js';
    private $css_name             =  'css/zzsc.css';
    private $option_prefix       =  '';
    private $author               =  'zoe';
    private $menu_page_slug_top =  '';
    private $option_qq_number_1 =  '';
    private $option_title        =  '';
    private $option_qq_number_2 =  '';
    private $option_qq_number_3 =  '';
    private $option_qq_number_4 =  '';
    private $option_phone        =  '';
    private $option_support_time=  '';
    private $fengefu              =  ',';


    public function __construct()
    {
        $this->plugin_file_name    =  dirname(plugin_basename(__FILE__));
        $this->full_url_prefix     =  home_url('wp-content/plugins/'.$this->plugin_file_name);
        $this->option_prefix       = 'zl_zoe_' . $this->name_en;
        $this->menu_page_slug_top  =  $this->author.'_'.$this->name_en.'_page_menu_top';
//        $this->option_name_html    = $this->option_prefix . '_html';
        //TODO 其实可以把 QQ 号码存在一条记录，用 JSON 保存，输入/输出做相关格式处理，暂时不搞那么先进
        $this->option_title         = $this->option_prefix.'_title';
        $this->option_qq_number_1  = $this->option_prefix.'_qq_number_1';
//        $this->option_qq_number_2  = $this->option_prefix.'_qq_number_2';
//        $this->option_qq_number_3  = $this->option_prefix.'_qq_number_3';
//        $this->option_qq_number_4  = $this->option_prefix.'_qq_number_4';
        $this->option_phone         = $this->option_prefix.'_phone';
        $this->option_support_time = $this->option_prefix.'_support_time';
    }

    public function doAction() {
        if (!is_admin()) {
            wp_register_script('tipsy', $this->full_url_prefix.'/'.$this->tipsy_jd_name, array('jquery'), '' );
            wp_register_script('start_v5', $this->full_url_prefix.'/'.$this->start_js_name, array('jquery'), '' );
            wp_register_style( 'default', $this->full_url_prefix.'/'.$this->css_name);
            if ( !is_blog_admin() ) { /** Load Scripts and Style on Website Only */
                wp_enqueue_script( 'tipsy');
                wp_enqueue_script( 'start_v5');
                wp_enqueue_style('default');
            }
            add_action('wp_footer', [$this, 'showFrame']);
            // 不能再 init 的时候使用，不然会出现 bug，页面会闪现一段输出
        } else {
            add_action('admin_menu', [$this, 'createMenu']);
            // 增加菜单

            $this->saveOptions();
        }
    }

    public function showFrame() {
        //
        // 简单形式：http://wpa.qq.com/msgrd?v=3&uin=14789544&site=http://www.ijoyer.com/&menu=yes

        // 窗口模式：<img  style="CURSOR: pointer" onclick="javascript:window.open('http://b.qq.com/webc.htm?new=0&sid=999999&o=test.yz.com&q=7', '_blank', 'height=502, width=644,toolbar=no,scrollbars=no,menubar=no,status=no');"  border="0" SRC=http://wpa.qq.com/pa?p=1:999999:1 alt="点击这里给我发消息">
        $title          =  get_option($this->option_title);
        $phone          =  get_option($this->option_phone);
//        $support_time   =  get_option($this->option_support_time);
        $slogan         =  '我们竭诚为您服务！';
        $qq_number_1    =  get_option($this->option_qq_number_1) ? get_option($this->option_qq_number_1) : $slogan;
//        $qq_number_2    =  get_option($this->option_qq_number_2) ? get_option($this->option_qq_number_2) : $slogan;
//        $qq_number_3    =  get_option($this->option_qq_number_3) ? get_option($this->option_qq_number_3) : $slogan;
//        $qq_number_4    =  get_option($this->option_qq_number_4) ? get_option($this->option_qq_number_4) : $slogan;
        $qq_numbers     =  explode($this->fengefu, $qq_number_1);
        $phones         =  explode($this->fengefu, $phone);
        echo '<div id="goto_top"></div>';
        echo '<div id="online_qq_layer" show="1" style="right: 0px; display: block;">';
        echo '<div id="online_qq_tab">';
        echo '<div class="online_icon">';
        echo '<a title="联系我们" id="floatTrigger" href="javascript:void(0);"></a>';
        echo '</div>';
        echo '</div>';
        echo '<div id="onlineService">';
        echo '<div class="online_windows overz">';
        echo '<div class="online_w_top"> </div>';
        echo '<div class="online_w_c overz">';
        echo '<div class="online_bar collapse" id="onlineSort1">';
        echo "<h2> <a href='javascript:;'>$title</a> </h2>";
        echo '<div class="online_content overz" id="onlineType1" style="display: block;">';
        echo '<ul class="overz">';
        if (count($qq_numbers) > 0) {
            foreach($qq_numbers as $key=>$qq_number) {
                echo "<li><a target='_blank' href='http://wpa.qq.com/msgrd?v=3&uin=$qq_number&site=qq&menu=yes'><img border='0' src='http://wpa.qq.com/pa?p=2:12345678:51' alt=$qq_number title=$slogan></a></li>";
            }
        }
//        echo "<li><a target='_blank' href='http://wpa.qq.com/msgrd?v=3&uin=$qq_number_2&site=qq&menu=yes'><img border='0' src='http://wpa.qq.com/pa?p=2:12345678:51' alt=$qq_number_2 title=$slogan></a></li>";
//        echo "<li><a target='_blank' href='http://wpa.qq.com/msgrd?v=3&uin=$qq_number_3&site=qq&menu=yes'><img border='0' src='http://wpa.qq.com/pa?p=2:12345678:51' alt=$qq_number_3 title=$slogan></a></li>";
//        echo "<li><a target='_blank' href='http://wpa.qq.com/msgrd?v=3&uin=$qq_number_4&site=qq&menu=yes'><img border='0' src='http://wpa.qq.com/pa?p=2:12345678:51' alt=$qq_number_4 title=$slogan></a></li>";
        if (count($phones) > 0) {
            foreach($phones as $key=>$value) {
                echo "<li>电话：$value</li>";
            }
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo "<div class='online_w_bottom'> </div>";
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    public function createMenu() {
        // 为插件创建菜单
        add_menu_page(
        /*$page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null*/
            $this->name_cn,
            $this->name_cn,
            'manage_options' ,
            $this->menu_page_slug_top,
            [$this, 'topMenuHtml']
        );
    }

    public function topMenuHtml() {
        $title          =  get_option($this->option_title);
        $qq_number_1    =  get_option($this->option_qq_number_1);
        $qq_number_2    =  get_option($this->option_qq_number_2);
        $qq_number_3    =  get_option($this->option_qq_number_3);
        $qq_number_4    =  get_option($this->option_qq_number_4);
        $phone          =  get_option($this->option_phone);
        $support_time   =  get_option($this->option_support_time);
        echo '<div class="wrap">';
        echo '<form name="option_form" method="post" action="">';
        echo '<p style="font-weight:bold;">在此处对在线客服进行设置。</p>';
        echo "<p>标题：<input type='text' name='title' value='$title'></p><br/>";
        echo "<p>联系QQ<u style=\"color: red\">多个QQ使用逗号（英文逗号）“,”隔开。</u>：</p><textarea style='height:150px;width:500px' name='qq_number_1'>" . $qq_number_1 . '</textarea><br/>';
//        echo "<p>联系QQ2：<input type='text' name='qq_number_2' value='$qq_number_2'></p>";
//        echo "<p>联系QQ3：<input type='text' name='qq_number_3' value='$qq_number_3'></p>";
//        echo "<p>联系QQ4：<input type='text' name='qq_number_4' value='$qq_number_4'></p>";
        echo "<p>联系电话<u style=\"color: red\">多个电话使用逗号（英文逗号）“,”隔开。</u>：</p><textarea style='height:150px;width:500px' name='phone'>" . $phone . '</textarea>';
//        echo "<p>在线时间：<input type='text' name='support_time' placeholder='示例  周一至周五：09:00-18:00' style='width: 230px' value='$support_time'></p>";
        if ($qq_number_1 || $qq_number_2 || $qq_number_3 || $qq_number_4 || $phone || $support_time) {
            echo '<div><p style="color:blue"><strong>设置已保存。</strong></p></div>';
        }
        echo '<p class="submit"><input type="submit" value="保存设置"/>';
        echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
        echo '</form>';
        echo '</div>';
    }

    public function saveOptions() {
        // 保存设置页面的值
        if ($_POST) {
            // TODO：未进行联系信息的正确性校验，正确手机号？正确QQ号？
            $title          =  $_POST['title'];
            $qq_number_1    =  $_POST['qq_number_1'];
            if (!$qq_number_1) {
                echo "<script>alert('请填写至少填写一个QQ号！')</script>";
                return;
            }
            $phone          =  $_POST['phone'];
            if (!$phone) {
                echo "<script>alert('请填写至少填写一个联系电话！')</script>";
                return;
            }
            $support_time   =  $_POST['support_time'];
            $title ? update_option($this->option_title, $title) : null;
            $qq_number_1 ? update_option($this->option_qq_number_1, $qq_number_1) : null;
            $phone ? update_option($this->option_phone, $phone) : null;
        }
    }
}

$object    =  new ZhulangOnlineSupport();
$object->doAction();