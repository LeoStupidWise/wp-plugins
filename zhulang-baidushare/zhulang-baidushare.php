<?php
/*
Plugin Name: 逐浪百度分享
Plugin URI:
Description: <a href="http://share.baidu.com/" target="_blank">百度分享</a>是一个提供网页地址收藏、分享及发送的WEB2.0按钮工具，借助百度分享按钮，网站的浏览者可以方便的分享内容到人人网、开心网、QQ空间、新浪微博等一系列SNS站点。 网站主可以在百度分享网站中获得分享按钮JS代码，嵌入到自己的网站，让网站链接分享到互联网各个角落！通过百度分享按钮，您的网站的浏览者可以便捷得分享您网站上的内容到人人网、开心网、qq空间、新浪微博等SNS站点进行传播，为您的网站带回更多的流量。通过百度分享按钮，您网站上的网页将更容易被百度搜索引擎所发现，从而有机会从百度搜索带回更多的流量。通过百度分享按钮，后续您还可以免费获取详尽的分享统计分析，了解网民将您网站上哪些内容分享到哪些SNS网站，每日的分享次数是多少，帮您更好得的跟踪、分析、激励用户的分享行为，为网站带来更多的流量。（该功能即将开放，敬请期待）赶紧免费获取百度分享按钮，获取更多的流量，您还等什么呢？<a href="options-general.php?page=zhulang-baidushare.php">启用插件后，可以点击这里进行配置</a>。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

require 'zhulang-help.php';

class ZhulangBaisuShare
{
	private $default_option    =  '<div class="bdsharebuttonbox"><a href="#" class="bds_more" data-cmd="more"></a><a href="#" class="bds_weixin" data-cmd="weixin" title="分享到微信"></a><a href="#" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博"></a><a href="#" class="bds_douban" data-cmd="douban" title="分享到豆瓣网"></a><a href="#" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间"></a><a href="#" class="bds_tqq" data-cmd="tqq" title="分享到腾讯微博"></a></div>
<script>window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"0","bdSize":"24"},"share":{}};with(document)0[(getElementsByTagName(\'head\')[0]||body).appendChild(createElement(\'script\')).src=\'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion=\'+~(-new Date()/36e5)];</script>';
	private $plugin_name        =  '逐浪百度分享';
	private $plugin_name_en     =  'zhulangBaiduShare';
	private $b_option_tmp       =  [];
	private $b_option_str       =  '';

	public function __construct()
	{
		$b_option                   =  get_option('b_option');
		if ($b_option == '') {
			$this->b_option_tmp['code']    =  $this->default_option;
			$this->b_option_tmp['position']=  2;
			$this->b_option_str    =  implode('%', $this->b_option_tmp);
			update_option('b_option', $this->b_option_str);
		}
	}

	public function doAction() {

		$download_option    =  'did_download_'.$this->plugin_name_en;
		if (!get_option($download_option)) {
			$helper    =  new ZhulangHelp();
			$helper->doPluginDownload([
				'cn'  => $this->plugin_name,
				'en'  => $this->plugin_name_en
			]);
				update_option($download_option, 1);
		}

		add_filter('the_content', [$this, 'b_content']);
		add_action('admin_menu', [$this, 'b_menu']);
	}

	public function b_content($content) {
		if(is_page() || is_single()) {
			$tmp = get_option('b_option');
			$arr = explode('%', $tmp);
			$b_option = $arr[0];
			$b_option=htmlspecialchars_decode($b_option);
			if($arr[1] == 2) {
				$content = $content."<div style='float:left'>".$b_option.'</div><br /><br />';
			}
			else {
				$content = '<br />'."<div style='float:left'>".$b_option.'</div><br /><br />'.$content;
			}
		}
		return $content;
	}

	public function b_menu() {
		/*$page_title, $menu_title, $capability, $menu_slug, $function = '' */
		add_options_page( $this->plugin_name , $this->plugin_name , 8 , basename(__FILE__) , [$this, 'b_option_add']);
	}

	public function b_option_add() {
		$b_upd = false;
		if($_POST['b_code'] != '') {
			if($_POST['b_pos'] != '') {
				$b_option_tmp['code'] = stripslashes_deep($_POST['b_code']);
				$b_option_tmp['position'] = $_POST['b_pos'];
				$b_option_str = implode('%', $b_option_tmp);
				update_option('b_option', $b_option_str);
				$b_upd = true;
			}
		}
		$tmp = get_option('b_option');
		$arr = explode('%', $tmp);
		echo '<div class="wrap">';
		echo '<form name="b_form" method="post" action="">';
		echo '<p style="font-weight:bold;">请在此处输入您从百度分享网站上获得的分享按钮Javascript代码。<a href="http://share.baidu.com" target="_blank"><u style="color:blue">请点击此处访问百度分享网站</u></a></p>';
		echo '<p>默认嵌入的代码风格为按钮式标准风格，显示在文章下方</p>';

		echo '<p><textarea style="height:300px;width:750px" name="b_code">' . $arr[0] . '</textarea></p>';
		if($b_upd) {
			echo '<div><p style="color:blue"><strong>百度分享按钮设置已经保存。</strong></p></div>';
		}
		echo '<br />';
		echo '嵌入位置 ：&nbsp;&nbsp;';
		echo '<input type="radio" name="b_pos" value="1" ' . ($arr[1] == 1 ? 'checked="checked"' : '') . ' /> 文章上方&nbsp;&nbsp;';
		echo '<input type="radio" name="b_pos" value="2" ' . ($arr[1] == 2 ? 'checked="checked"' : '') . ' /> 文章下方&nbsp;&nbsp;';
		echo '<br /><br />';
		echo '<p class="submit"><input type="submit" value="保存设置"/>';
		echo '<input type="button" value="返回上级" onclick="window.location.href=\'plugins.php\';" /></p>';
		echo '</form>';

		echo '</div>';
	}
}

$object    =  new ZhulangBaisuShare();
$object->doAction();