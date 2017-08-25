<?php
/**
 * Created by PhpStorm.
 * User: YaZhou
 * Date: 2017/8/5
 * Time: 13:55
 */

if (!function_exists('dump')) {
    function dump($var, $strict=false) {
        echo '--------';
        echo '<pre>';
        if ($strict) {
            var_dump($var);
        } else {
            print_r($var);
        }
        echo '</pre>';
        echo '--------';
        echo '<br/>';
    }
}

if (!function_exists('config')) {
    function config($key, $default = null) {
        $key        =  strtolower($key);
        $configs    =  require_once './config.php';
        if (isset($configs[$key])) {
            return $configs[$key];
        } elseif($default) {
            return $default;
        } else {
            return null;
        }
    }
}

if (!function_exists('jsToBackPage')) {
    function jsToBackPage() {
        echo "<script> window.history.back(-1); </script>";
    }
}

if (!function_exists('jsToBackPageAndAlert')) {
    function jsAlertAndToBackPage($msg) {
        echo "<script> alert('$msg'); window.history.back(-1); </script>";
    }
}

if (!function_exists('jsAlert')) {
    function jsAlert($msg) {
        echo "<script> alert('$msg'); </script>";
    }
}

if (!function_exists('pdoConnect')) {
    function pdoConnect($type, $host, $database, $user, $password, $port=3306, $permanent=null, $options=null) {
        if ($permanent) {
            $options       =  [
                PDO::ATTR_PERSISTENT => true,
            ];
        }
        $pdo_connection    =  new PDO("$type:host=$host;port=$port;dbname=$database", $user, $password, $options);
        return $pdo_connection;
    }
}

if (!function_exists('pdoDefaultConnect')) {
    function pdoDefaultConnect() {
        $configs    =  require_once './config.php';
        $db_info    =  $configs['db'];
        return pdoConnect(
            $db_info['type'],
            $db_info['host'],
            $db_info['database'],
            $db_info['user'],
            $db_info['password']
        );
    }
}

if (!function_exists('recursionPlus')) {
    // 递归加法
    function recursionPlus($num) {
        if ($num == 1) {
            return 1;
        } else {
            return recursionPlus($num - 1) + $num;
        }
    }
}

if (!function_exists('debug')) {
	function debug($val, $exit=true, $dump=false) {
		if ($dump) {
			$func    =  'var_dump';
		} else {
			$func    =  (is_array($val) || is_object($val)) ? 'print_r' : 'printf';
		}
		header("Content-type: text/html; charset=utf-8");
		echo '<pre>Debug output:<hr/>';
		$func($val);
		echo '</pre>';
		if ($exit) {
			exit;
		}
	}
}