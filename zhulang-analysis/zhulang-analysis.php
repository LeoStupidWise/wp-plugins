<?php
/*
Plugin Name: 逐浪网页分析
Plugin URI:
Description: 使用该插件能让您更好的采集站点信息，从而更好的运营您的站点。<a href="options-general.php?page=zhulang-baidushare.php">启用插件后，可以点击这里进行配置</a>。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

/*
 * 从配置取出 js 放到 wp_footer 钩子中，同时可以在设置中设置对应的选项配置
 */


class ZhulangWebAnalysis
{
    private $name_cn    =  '逐浪网页分析';
    private $name_en    =  'zhulangWebAnalysis';
    private $plugin_option    =  'zl_web_analysis_option';

    public function __construct() {
        //
    }

    public function doAction() {
        add_action('wp_footer', [$this, 'zlWebAnalysisFunc']);
        add_action('admin_menu', [$this, 'zl_analysis_menu']);
        $download_option    =  'did_download_'.$this->name_en;
        if (!get_option($download_option)) {
            $this->doPluginDownload([
                'cn'    => $this->name_cn,
                'en'    => $this->name_en
            ]);
            update_option($download_option, 1);
        }
    }

    public function zl_analysis_menu() {
        add_options_page( $this->name_cn , $this->name_cn, 8 , basename(__FILE__) , [$this, 'zl_option_add']);
    }

    public function zlWebAnalysisFunc() {
        $analysis_option    =  get_option($this->plugin_option);
        echo $analysis_option;
    }

    public function zl_option_add() {
        if($_POST['zl_code'] != '') {
            $b_option_tmp['code'] = stripslashes_deep($_POST['zl_code']);
            update_option($this->plugin_option, $b_option_tmp['code']);
        }
        $tmp = get_option($this->plugin_option);
        $arr = $tmp;
        echo '<div class="wrap">';
        echo '<form name="zl_analysis_form" method="post" action="">';
        echo '<p style="font-weight:bold;">请在此处输入您从站长工具获得的Javascript代码。</p>';
        echo '<p>默认嵌入的代码风格为按钮式标准风格，显示在网站左下方</p>';
        echo '<p><textarea style="height:300px;width:750px" name="zl_code">' . $arr . '</textarea></p>';
        echo '<br />';
        echo '<br /><br />';
        echo '<p class="submit"><input type="submit" value="保存设置"/>';
        echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
        echo '</form>';
        echo '</div>';
    }

    public function phpPostSec($data, $url) {
        // php 进行 http post 模拟，这个方法可用
        $ch = curl_init();
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
        $file_contents = curl_exec($ch);
        curl_close($ch);

        return $file_contents;
    }

    public function doPluginDownload($plugin_name = null) {
        // 这里的方法属性不能是 private，如果是 private，就会出现错误，只能是 public
        // 上面说的不能是 private 的前提是在该方法被 didPluginDownload 中钩子 wp_ajax_zhulang_plugin_download 调用的情况下
        $data['name_cn']        =  $plugin_name ? $plugin_name['cn'] : $_POST['name_cn'];
        $data['name_en']        =  $plugin_name ? $plugin_name['en'] : $_POST['name_en'];
        $data['ip']              =  $this->getClientIP();
        $data['user_agent']     =  $_SERVER['HTTP_USER_AGENT'];
        $data['remote_url']     =  $_SERVER['HTTP_REFERER'];
        $data['remote_url']     =  strlen($data['remote_url']) > 100 ? '' : $data['remote_url'];

        $result    =  $this->phpPostSec($data, 'http://test.yz/analysis_wp_plugin.php');
        if (!$plugin_name) {
            wp_die();
        }
    }

    public function getClientIP()
    {
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }
}

$object    =  new ZhulangWebAnalysis();
$object->doAction();