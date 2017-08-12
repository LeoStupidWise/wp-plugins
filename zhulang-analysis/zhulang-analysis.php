<?php
/*
Plugin Name: 逐浪网页分析
Plugin URI:
Description: 使用该插件能让您更好的采集站点信息，从而更好的运营您的站点。<a href="options-general.php?page=zhulang-baidushare.php">启用插件后，可以点击这里进行配置</a>。
Version: 1.0
Author: Zoe
Author URI:
*/

/*
 * 从配置取出 js 放到 wp_footer 钩子中，同时可以在设置中设置对应的选项配置
 */

define('ZL_WEB_ANALYSIS_CN_NAME', '逐浪网页分析');
define('ZL_WEB_ANALYSIS_OPTION', 'zl_web_analysis_option');

function zlWebAnalysisFunc(){
    $analysis_option    =  get_option(ZL_WEB_ANALYSIS_OPTION);
    echo $analysis_option;
}
add_action('wp_footer', 'zlWebAnalysisFunc');

add_action('admin_menu', 'zl_analysis_menu');
function zl_analysis_menu() {
    // Add submenu page to the Settings main menu.
    add_options_page( ZL_WEB_ANALYSIS_CN_NAME , ZL_WEB_ANALYSIS_CN_NAME, 8 , basename(__FILE__) , 'zl_option_add');
}

function zl_option_add() {
//    $b_upd = false;
    if($_POST['zl_analysis_code'] != '') {
//        if($_POST['b_pos'] != '') {
            $b_option_tmp['code'] = stripslashes_deep($_POST['zl_analysis_code']);
//            $b_option_tmp['position'] = $_POST['b_pos'];
//            $b_option_str = implode('%', $b_option_tmp);
            update_option(ZL_WEB_ANALYSIS_OPTION, $b_option_tmp['code']);
//            $b_upd = true;
//        }
    }
    $tmp = get_option(ZL_WEB_ANALYSIS_OPTION);
    $arr = $tmp;
    echo '<div class="wrap">';
    echo '<form name="zl_analysis_form" method="post" action="">';
    echo '<p style="font-weight:bold;">请在此处输入您从站长工具获得的Javascript代码。</p>';
    echo '<p>默认嵌入的代码风格为按钮式标准风格，显示在网站左下方</p>';

    echo '<p><textarea style="height:300px;width:750px" name="zl_code">' . $arr . '</textarea></p>';
//    if($b_upd) {
//        echo '<div><p style="color:blue"><strong>百度分享按钮设置已经保存。</strong></p></div>';
//    }
    echo '<br />';
//    echo '嵌入位置 ：&nbsp;&nbsp;';
//    echo '<input type="radio" name="b_pos" value="1" ' . ($arr[1] == 1 ? 'checked="checked"' : '') . ' /> 文章上方&nbsp;&nbsp;';
//    echo '<input type="radio" name="b_pos" value="2" ' . ($arr[1] == 2 ? 'checked="checked"' : '') . ' /> 文章下方&nbsp;&nbsp;';
    echo '<br /><br />';
    echo '<p class="submit"><input type="submit" value="保存设置"/>';
    echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
    echo '</form>';

    echo '</div>';
}