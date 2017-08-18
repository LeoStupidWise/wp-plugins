<?php
/*
Plugin Name: 逐浪网页关键词
Plugin URI:
Description: 为站点增加关键字和描述，使站点的百度排名更加靠前。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

class ZhulangWebKeyword
{
    public  $name_cn    =  '逐浪网页关键词';
    public  $name_en    =  'zhulangWebKeyword';
    private $option_prefix    =  '';                              // 数据表中的选项前缀
    private $option_keyword   =  '';                              // 关键字存放的 option 键
    private $option_describe  =  '';                              // 描述存放的 option 键
    private $author      =  'zoe';                                 // 作者，可以做前缀使用
    private $menu_page_slug_top        =  '';                     // 顶级菜单页面的 slug
    private $menu_page_slug_keyword    =  '';                    // 关键字菜单页面的 slug
    private $menu_page_slug_describe   =  '';                    // 描述菜单页面的 slug

    public function __construct() {
        $this->option_prefix    =  'zl_zoe_'.$this->name_en;
        $this->option_keyword   =  $this->option_prefix.'_'.'keyword';
        $this->option_describe  =  $this->option_prefix.'_'.'describe';
        $this->menu_page_slug_top   =  $this->author.'_'.$this->name_en.'_page_menu_top';
        $this->menu_page_slug_keyword   =  $this->author.'_'.$this->name_en.'_page_menu_keyword';
        $this->menu_page_slug_describe  =  $this->author.'_'.$this->name_en.'_page_menu_describe';
    }

    public function doAction() {
        add_action('admin_menu', [$this, 'createMenu']);
        // 增加菜单

        $this->saveOptions();
        // 保存选项值

        add_action('wp_head', [$this, 'addMetaKeyword']);
        add_action('wp_head', [$this, 'addMetaDescribe']);
        // 增加 meta 标签
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
        add_submenu_page(
            /*$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = ''*/
            $this->menu_page_slug_top,
            '网页描述设置',
            '网页描述设置',
            'manage_options',
            $this->menu_page_slug_describe,
            [$this, 'describeMenuHtml']
        );
    }

    public function topMenuHtml() {
        $old_option    =  get_option($this->option_keyword);
        echo '<div class="wrap">';
        echo '<form name="option_form" method="post" action="">';
        echo '<p style="font-weight:bold;">请在此处输入站点的关键词。</p>';
        echo "<p><textarea style='height:300px;width:750px' name='$this->option_keyword'>" . $old_option . '</textarea></p>';
        if($old_option) {
            echo '<div><p style="color:blue"><strong>关键词已保存。</strong></p></div>';
        }
        echo '<p class="submit"><input type="submit" value="保存设置"/>';
        echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
        echo '</form>';
        echo '</div>';
    }

    public function saveOptions() {
        // 保存从页面传来的选项值
        $option_value_keyword    =  $_POST[$this->option_keyword];
        $option_value_describe   =  $_POST[$this->option_describe];
        if ($option_value_describe) {
            update_option($this->option_describe, $option_value_describe);
        }
        if ($option_value_keyword) {
            update_option($this->option_keyword, $option_value_keyword);
        }
    }

    public function keywordMenuHtml() {
        //
    }

    public function describeMenuHtml() {
        $old_option    =  get_option($this->option_describe);
        echo '<div class="wrap">';
        echo '<form name="option_form" method="post" action="">';
        echo '<p style="font-weight:bold;">请在此处输入站点的描述。</p>';
        echo "<p><textarea style='height:300px;width:750px' name='$this->option_describe'>" . $old_option . '</textarea></p>';
        if($old_option) {
            echo '<div><p style="color:blue"><strong>描述已以保存。</strong></p></div>';
        }
        echo '<p class="submit"><input type="submit" value="保存设置"/>';
        echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
        echo '</form>';
        echo '</div>';
    }

    public function addMetaKeyword() {
        $keywords    =  get_option($this->option_keyword);
        echo "<meta name='keywords' content='$keywords'/>";
    }

    public function addMetaDescribe() {
        $describe    =  get_option($this->option_describe);
        echo "<meta name='description' content='$describe'/>";
    }
}

$object    =  new ZhulangWebKeyword();
$object->doAction();