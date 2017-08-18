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


    public function __construct()
    {
        $this->plugin_file_name    =  dirname(plugin_basename(__FILE__));
        $this->full_url_prefix     =  home_url('wp-content/plugins/'.$this->plugin_file_name);
        $this->option_prefix       = 'zl_zoe_' . $this->name_en;
        $this->menu_page_slug_top  =  $this->author.'_'.$this->name_en.'_page_menu_top';
//        $this->option_name_html    = $this->option_prefix . '_html';
    }

    public function doAction() {
//        add_action('init', [$this, 'showFrame']);
        // 不能再 init 的时候使用，不然会出现 bug
        wp_register_script('tipsy', $this->full_url_prefix.'/'.$this->tipsy_jd_name, array('jquery'), '' );
        wp_register_style( 'default', $this->full_url_prefix.'/'.$this->css_name);
        if ( !is_blog_admin() ) { /** Load Scripts and Style on Website Only */
            wp_enqueue_script( 'tipsy');
            wp_enqueue_style('default');
        }
        add_action('admin_menu', [$this, 'createMenu']);
        // 增加菜单
    }

    public function showFrame() {
        //
        // 简单形式：http://wpa.qq.com/msgrd?v=3&uin=14789544&site=http://www.ijoyer.com/&menu=yes

        // 窗口模式：<img  style="CURSOR: pointer" onclick="javascript:window.open('http://b.qq.com/webc.htm?new=0&sid=999999&o=test.yz.com&q=7', '_blank', 'height=502, width=644,toolbar=no,scrollbars=no,menubar=no,status=no');"  border="0" SRC=http://wpa.qq.com/pa?p=1:999999:1 alt="点击这里给我发消息">
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
        echo '<h2> <a href="javascript:;">在线技术支持</a> </h2>';
        echo '<div class="online_content overz" id="onlineType1" style="display: block;">';
        echo '<ul class="overz">';
        echo '<li><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1216512565&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:12345678:51" alt="我们竭诚为您服务！" title="我们竭诚为您服务！"/></a></li>';
        echo '<li><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1216512565&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:12345678:51" alt="我们竭诚为您服务！" title="我们竭诚为您服务！"/></a></li>';
        echo '<li><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1216512565&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:12345678:51" alt="我们竭诚为您服务！" title="我们竭诚为您服务！"/></a></li>';
        echo '<li><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1216512565&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:12345678:51" alt="我们竭诚为您服务！" title="我们竭诚为您服务！"/></a></li>';
        echo '<li>电话：18812345678</li>';
        echo '</ul>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="online_w_bottom"> </div>';
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
            'manage_options',
            $this->menu_page_slug_top,
            [$this, 'topMenuHtml'],
            plugins_url('/images/icon.png', __FILE__)
        );
    }

    public function topMenuHtml() {
//        $old_option    =  get_option($this->option_describe);
        echo '<div class="wrap">';
        echo '<form name="option_form" method="post" action="">';
        echo '<p style="font-weight:bold;">请在此处输入站点的描述。</p>';
        echo "<p><textarea style='height:300px;width:750px' name='sss'>" . 'sss' . '</textarea></p>';
//        if($old_option) {
//            echo '<div><p style="color:blue"><strong>描述已以保存。</strong></p></div>';
//        }
        echo '<p class="submit"><input type="submit" value="保存设置"/>';
        echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
        echo '</form>';
        echo '</div>';
    }
}

$object    =  new ZhulangOnlineSupport();
$object->doAction();