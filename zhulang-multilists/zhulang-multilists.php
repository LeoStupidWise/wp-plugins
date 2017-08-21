<?php
/*
Plugin Name: 逐浪炫彩页面
Plugin URI:
Description: 提供多种文章列表进行选择。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

class ZhulangMultiLists
{
    public $name_cn = '逐浪炫彩页面';
    public $name_en = 'zhulangMultiLists';
    private $plugin_file_name = '';
    private $full_url_prefix = '';
    private $start_js_name = 'js/start_v5.js';
    private $tipsy_jd_name = 'js/jquery.tipsy.js';
    private $css_name = 'css/zzsc.css';
    private $option_prefix = '';
    private $author = 'zoe';
    private $menu_page_slug_top = '';
    private $option_qq_number_1 = '';
    private $option_qq_number_2 = '';
    private $option_qq_number_3 = '';
    private $option_qq_number_4 = '';
    private $option_phone = '';
    private $option_support_time = '';

    public function __construct() {
        $this->plugin_file_name    =  dirname(plugin_basename(__FILE__));
        $this->full_url_prefix     =  home_url('wp-content/plugins/'.$this->plugin_file_name);
        $this->option_prefix       = 'zl_zoe_' . $this->name_en;
        $this->menu_page_slug_top  =  $this->author.'_'.$this->name_en.'_page_menu_top';
    }

    public function doAction() {
        //
    }
}

$object    =  new ZhulangMultiLists();
$object->doAction();