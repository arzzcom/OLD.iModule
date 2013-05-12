<?php
header('Content-type: text/html; charset=utf-8', true);

// Server Value Init.
$_SERVER['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) == true ? $_SERVER['HTTP_REFERER'] : '';
$_SERVER['DOCUMENT_ROOT'] = isset($_SERVER['DOCUMENT_ROOT']) == true ? $_SERVER['DOCUMENT_ROOT'] : '';

if (file_exists($_SERVER['DOCUMENT_ROOT'].'/iModule.conf.php') == true) {
	REQUIRE_ONCE $_SERVER['DOCUMENT_ROOT'].'/iModule.conf.php';
}

$_ENV['isIncluded'] = array();
$_ENV['isHeaderIncluded'] = false;
$_ENV['isFooterIncluded'] = false;
$_ENV['debug'] = isset($_ENV['debug']) == true ? $_ENV['debug'] : false;

if ($_ENV['debug'] == false) {
	error_reporting(0);
} else {
	error_reporting(E_ALL);
}

if (get_magic_quotes_gpc() == true) {
	foreach ($_REQUEST as $key=>$value) {
		if (is_array($value) == true) {
			$_REQUEST[$key] = array_map('stripslashes',$value);
		} else {
			$_REQUEST[$key] = stripslashes($value);
		}
	}
}

// Root Path & Root Dir
if (isset($_ENV['path']) == false) {
	$_ENV['path'] = '';
	$temp = explode('/',__FILE__);
	while (sizeof($temp) > 0) {
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/'.implode('/',$temp)) == true) {
			$_ENV['path'] = str_replace('/config/default.conf.php','',$_SERVER['DOCUMENT_ROOT'].'/'.implode('/',$temp));
			break;
		}
		array_shift($temp);
	}
}
$_ENV['dir'] = isset($_ENV['dir']) == true ? $_ENV['dir'] : str_replace($_SERVER['DOCUMENT_ROOT'],'',$_ENV['path']);
$temp = explode(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),str_replace('\\','/',realpath(__FILE__)));

if (isset($_SERVER['HTTP_HOST']) == true) {
	$temp = parse_url('http://'.$_SERVER['HTTP_HOST']);
	$_ENV['domain'] = str_replace('www.','',$temp['host']);
} else {
	$_ENV['domain'] = '';
}
$_ENV['userfileDir'] = isset($_ENV['userfileDir']) == true ? $_ENV['userfileDir'] : '/userfile';
$_ENV['userfilePath'] = isset($_ENV['userfilePath']) == true ? $_ENV['userfilePath'] : $_ENV['path'].'/userfile';


$_ENV['timezone'] = '+0900';
$_ENV['ver'] = '0.0.9';
$_ENV['page'] = isset($_SERVER['REDIRECT_URL'])==true ? $_SERVER['REDIRECT_URL'] : $_SERVER['PHP_SELF'];

if (isset($_SERVER['HTTP_USER_AGENT'])==true) {
	if (preg_match('/msie/i',$_SERVER['HTTP_USER_AGENT'])==true) {
		$_ENV['browser'] = 'IE';
	} elseif (preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT'])==true) {
		$_ENV['browser'] = 'OP';
	} elseif (preg_match('/firefox/i',$_SERVER['HTTP_USER_AGENT'])==true) {
		$_ENV['browser'] = 'FF';
	} elseif (preg_match('/Safari/i',$_SERVER['HTTP_USER_AGENT'])==true) {
		$_ENV['browser'] = 'SF';
	} else {
		$_ENV['browser'] = 'NC';
	}
}

session_set_cookie_params(0,'/','.'.$_ENV['domain']);
session_cache_expire(3600);
session_start();

REQUIRE_ONCE $_ENV['path'].'/class/default.func.php';
REQUIRE_ONCE $_ENV['path'].'/config/table.conf.php';

$_ENV['starttime'] = GetMicrotime();

function __autoload($class) {
	if ($class != 'Module' && preg_match('/Module/',$class) == true) {
		$module = strtolower(str_replace('Module','',$class));
		if (file_exists($_ENV['path'].'/module/'.$module.'/'.$class.'.class.php') == true) REQUIRE_ONCE $_ENV['path'].'/module/'.$module.'/'.$class.'.class.php';
	} else {
		if (file_exists($_ENV['path'].'/class/'.$class.'.class.php') == true) REQUIRE_ONCE $_ENV['path'].'/class/'.$class.'.class.php';
	}
}
?>