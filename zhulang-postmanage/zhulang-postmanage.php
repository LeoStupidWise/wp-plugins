<?php

/*
Plugin Name: 逐浪文章管理
Plugin URI:
Description: 将提供给您文章的时间排序、热度排序、随机排序以及自定义顺序置顶。
Version: 1.0
Author: Zoe
Author URI: https://github.com/LeoStupidWise/wp-plugins
*/

class ZhulangPostManage
{
    /*
     * 1. 文章会有新的表来记录浏览信息，但总浏览量在 postmeta 中可以快速获取
     * */
    public $name_cn                 =  '逐浪文章管理';
    public $name_en                 =  'zhulangPostManage';
    private $plugin_file_name      =  '';
    private $full_url_prefix       =  '';
    private $start_js_name         =  'js/start_v5.js';
    private $tipsy_jd_name         =  'js/jquery.tipsy.js';
    private $css_name               =  'css/zzsc.css';
    private $option_prefix         =  '';
    private $author                 =  'zoe';
    private $wp_db                  =  '';
    private $menu_page_slug_top   =  '';
    private $table_name            =  'wp_zoe_post_click';                  // 文章浏览记录表
    private $view_meta_name        =  '_zoe_views';                         // 文章浏览量额外属性的键名
    private $did_post_added        =  false;                                 // 一起请求一篇文章只记录一次浏览，当此属性为true时，禁止插入新的浏览记录


    public function __construct()
    {
        global $wpdb;
        date_default_timezone_set('Asia/Shanghai');
        $this->plugin_file_name    =  dirname(plugin_basename(__FILE__));
        $this->full_url_prefix     =  home_url('wp-content/plugins/' . $this->plugin_file_name);
        $this->option_prefix       =  'zl_zoe_' . $this->name_en;
        $this->menu_page_slug_top =  $this->author . '_' . $this->name_en . '_page_menu_top';
        $this->wp_db                =  $wpdb;
    }

    public function doAction() {

        if (!$this->haveTable($this->table_name)) {
            $this->createTable();
        }
        add_action('the_post', [$this, 'addViewTime']);
        add_action( 'widgets_init', [$this, 'registerWidget']);
        // 使用 the_post 钩子会在页面加载文章的任何时候进行挂载，那么在 sidebar 里面的近期文章进行加载的时候，也会写入数据

        add_action('pre_get_posts', [$this, 'doPostOrder']);
        // 在输出文章列表之前，对文章进行排序
    }

    public function registerWidget() {
        register_widget('ZhulangPostOrderWidget');
    }

    public function doPostOrder($query) {
        //
        if ($_POST) {
            $post_order    =  $_POST['post_order'];

            switch($post_order) {
                default:
                    break;
                case 'order_time_desc':
                    break;
                case 'order_time_asc':
                    $query->set( 'order', 'asc' );
                    break;
                case 'order_hot':
                    /*
                     * Excludes an array of single post ID's from your home page
                        function exclude_single_posts_home($query) {
                            if ($query->is_home() && $query->is_main_query()) {
                                $query->set('post__not_in', array(7,11));
                             }
                        }

                    add_action('pre_get_posts', 'exclude_single_posts_home');
                     * */
                    break;
                case 'order_random':
                    break;
            }
        }
    }

    public function createTable() {
        // 创建数据表用来保存点赞记录
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");  //引用wordpress的内置方法库

        $sql    =  "DROP TABLE IF EXISTS `$this->table_name`;
            CREATE TABLE `$this->table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `post_id` int(11) DEFAULT NULL,
              `visitor_ip` VARCHAR(16) DEFAULT NULL COMMENT '浏览者ip',
              `user_agent` VARCHAR(50) DEFAULT NULL COMMENT 'http_user_agent',
              `url` VARCHAR(100) DEFAULT NULL COMMENT '用户评论的 URl',
              `created_at` datetime DEFAULT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }

    public function addPostViewsMeta($post_id) {
        // 增加文章的浏览次数额外属性
        $views    =  get_post_meta($post_id, $this->view_meta_name, true);
        if (!$views) {
            $result    =  add_post_meta($post_id, $this->view_meta_name, 1, true);
            return 1;
        } else {
            $result    =  update_post_meta($post_id, $this->view_meta_name, $views+1);
            return $views+1;
        }
    }

    public function haveTable($table_name) {
        // 数据表中是否有对应的表格
        // 有就会返回表名，没有返回 null
        $sql    =  "SHOW TABLES LIKE '$table_name'";
        return $this->wp_db->get_var($sql);
    }

    public function addViewTime() {
        // 增加浏览次数一次
        global $post;
        if( is_int( $post ) ) {
            $post = get_post( $post );
        }
        if( ! wp_is_post_revision( $post ) && ! is_preview() ) {
            if (is_single() || is_page()) {
                // 到此进入文章页
                if (!$this->did_post_added) {
                    // 页面中还有侧边栏，侧边栏中的文章加载时会更新 global $post，但一般情况下侧边栏会后加载
                    // 所以设置一个状态，只要页面写入过一次数据库就将这个状态变为 true
                    // 只有当状态为 false 时才能写入数据
                    // SW.Leo 2017-8-22
                    $post_id       =  $post->ID;
                    $user_agent    =  $_SERVER['HTTP_USER_AGENT'];
                    $client_ip     =  $this->getClientIP();
                    $url           =  home_url();
                    $time          =  date('Y-m-d H:i:s');
                    $result        =  $this->dbAddViewTime($post_id, $client_ip, $url, $user_agent, $time);
                    $views         =  $this->addPostViewsMeta($post_id);
                    if ($result) {
                        $this->did_post_added    =  true;
                    }
                }
            }
        }
    }

    public function getVisitorInfo() {
        // 获取访问者的 ip，user_agent，url 等等
        $user_agent    =  $_SERVER['user_agent'];
        $client_ip     =  $this->getClientIP();
        $url           =  home_url();
    }

    public function dbAddViewTime($post_id, $visit_ip, $url, $user_agent, $time=null) {
        if (!$time) {
            $time      =  date('Y-m-d H:i:s');
        }
        $sql    =  "INSERT INTO $this->table_name (post_id, visitor_ip, user_agent, url, created_at) VALUES ($post_id, '$visit_ip', '$user_agent', '$url', '$time')";
        return $this->wp_db->query($sql);
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

class ZhulangPostOrderWidget extends WP_Widget
{
    public function __construct()
    {
        $id_base    =  'zhulangpostordertool';
        /*Optional Base ID for the widget, lowercase and unique. If left empty,a portion of the widget's class name will be used Has to be unique.*/

        $name       =  '逐浪文章排序小工具';
        /*Name for the widget displayed on the configuration page.*/

        $widget_options    =  [
            'classname'    => 'ZhulangPostOrderWidget',
            'description'  => '浏览量排序，热度排序，随机排序。'
        ];

        $control_options   =  'widgets_control';
        parent::__construct($id_base, $name, $widget_options, $control_options);
    }

    public function form($instance) {
        $defaults = [
            'title'         => '文章排序',
            'order_time'   => '时间排序',
            'order_hot'     => '热度排序',
            'order_random'  => '随机排序'
        ];
        $instance = wp_parse_args( (array) $instance, $defaults );

        $title         = $instance['title'];
        $order_time    = $instance['order_time'];
        $order_hot     = $instance['order_hot'];
        $order_random  = $instance['order_random'];
        ?>
        <p>标题: <input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <p>排序一: <input class="widefat" name="<?php echo $this->get_field_name( 'order_time' ); ?> "type="text" value="<?php echo esc_attr( $order_time ); ?> " /></p>
        <p>排序二: <input class="widefat" name="<?php echo $this->get_field_name( 'order_hot' ); ?> "type="text" value="<?php echo esc_attr( $order_hot ); ?> " /></p>
        <p>排序三: <input class="widefat" name="<?php echo $this->get_field_name( 'order_random' ); ?> "type="text" value="<?php echo esc_attr( $order_random ); ?> " /></p>
        <?php
//        return parent::form($instance); // TODO: Change the autogenerated stub
    }

    public function update($new_instance, $old_instance) {
        $instance = $old_instance;

        $instance['title'] = strip_tags( trim( $new_instance['title'] ) );
        $instance['order_time'] = strip_tags( trim(  $new_instance['order_time'] ) );
        $instance['order_hot'] = strip_tags( trim( $new_instance['order_hot'] ) );
        $instance['order_random'] = strip_tags( trim( $new_instance['order_random'] ) );
//        return parent::update($new_instance, $old_instance); // TODO: Change the autogenerated stub
    }

    public function widget($args, $instance) {
        ?>

        <div>
            <form method="post" action="#">
                <select name="post_order" style="width: 150px; height: 30px">
                    <option value ="order_time_desc">时间降序</option>
                    <option value ="order_time_asc">时间升序</option>
                    <option value ="order_hot">热度排序</option>
                    <option value="order_random">随机排序</option>
                </select>
                <input style="height: auto; width: auto;" type="submit">
            </form>
        </div>

        <?php

        /*
         * 暂时无法实现通过后台来控制前台的显示内容，这里直接写死
         * SW.Leo 2017-8-22
         * */
/*        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $order_time    = empty( $instance['order_time'] ) ? ' ' : $instance['order_time'];
        $order_hot     = empty( $instance['order_hot'] ) ? ' ' : $instance['order_hot'];
        $order_random  = empty( $instance['order_random'] ) ? ' ' : $instance['order_random'];

        echo '<p> 标题: ' . $title . '</p>';
        echo '<p> 排序1: ' . $order_time. '</p>';
        echo '<p> 排序2: ' . $order_hot. '</p>';
        echo '<p> 排序3: ' . $order_random. '</p>';*/
//        parent::widget($args, $instance); // TODO: Change the autogenerated stub
    }
}

$subject    =  new ZhulangPostManage();
$subject->doAction();