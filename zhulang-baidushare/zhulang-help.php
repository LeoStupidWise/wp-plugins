<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/14
 * Time: 10:49
 */

class ZhulangHelp
{
    public function phpPost($data, $url) {
        // php 进行 http post 模拟，这个方法不可用
        $data = http_build_query($data);
        $opts = array (
            'http' => array (
                'method' => 'POST',
                'header'=> 'Content-type: application/x-www-form-urlencoded; charset=UTF-8' .
                'Content-Length: ' . strlen($data) ,
                'content' => $data
            )
        );
        $context = stream_context_create($opts);
        $html = file_get_contents($url, false, $context);
        return $html;
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

    public function didPluginDownload($plugin_name, $js_address='js/plugin_download.js', $option='zhulang_plugin_downloaded') {

//        if (get_option($download_option)) {
//            return;
//        }
        var_dump($plugin_name);
        $option    =  get_option($option);
        $plugin_name_cn    =  isset($plugin_name['cn']) ? $plugin_name['cn'] : '';
        $plugin_name_en    =  isset($plugin_name['en']) ? $plugin_name['en'] : '';
        if (!$option) {
            $script_name    =  'plugin_download';
            wp_enqueue_script(
                $script_name,
                plugins_url($js_address, __FILE__),
                ['jquery']
            );

            wp_localize_script(
                $script_name,
                'ajax_object',
                [
                    'ajax_url'    => admin_url('admin-ajax.php'),
                    'plugin_name_cn' => $plugin_name_cn,
                    'plugin_name_en' => $plugin_name_en
                ]
            );

            add_action( 'wp_ajax_zhulang_plugin_download' , [$this, 'doPluginDownload'] );
            add_action( 'wp_ajax_nopriv_zhulang_plugin_download' , [$this, 'doPluginDownload'] );
//            update_option($download_option, 1);
        }
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