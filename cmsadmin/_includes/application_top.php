<?php

////
// Set Error Reporting
ini_set('display_errors', 'On');
ini_set('error_reprting', E_ALL & ~E_NOTICE);
error_reporting(E_ALL & ~E_NOTICE);
/*ini_set('error_reprting', E_ALL);
error_reporting(E_ALL);*/

////
// Set any fluid needed directories
define('DIR_ADMININCLUDES', dirname(__FILE__).(substr(dirname(__FILE__), -1)!='/'?'/':''), true);
define('DIR_ADMINROOT', realpath(DIR_ADMININCLUDES.'../').'/', true);
define('DIR_CMSROOT', realpath(DIR_ADMININCLUDES.'../../').'/', true);
define('DIR_CMSINCLUDES', DIR_CMSROOT.'_includes/', true);

////
// Include the main config file
require(DIR_CMSINCLUDES . 'config.php');

////
// Include the functions file
require(DIR_ADMININCLUDES . 'functions.php');

//if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
//	include(DIR_ADMININCLUDES . 'S3.php');
//}

////
// Image Magick stuff
if ($USE_IMAGEMAGICK === 'auto') {
	$return = array();
	exec("exec 2>&1; type convert", $return);
	if (!strstr($return[0], 'not found')) {
		$USE_IMAGEMAGICK = true;
	} else {
		$USE_IMAGEMAGICK = false;
	}
} elseif ($USE_IMAGEMAGICK != true) {
	$USE_IMAGEMAGICK = false;
} else {
	$USE_IMAGEMAGICK = true;
}
define('USE_IMAGEMAGICK', $USE_IMAGEMAGICK);

////
// Set other PHP values
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '256M');
ini_set('magic_quotes_gpc', 'Off');
ini_set('magic_quotes_runtime', 'Off');
ini_set('magic_quotes_sybase', 'Off');
ini_set('register_globals', 'Off');

ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

////
// Connect to the DB
mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD) or send_error(mysql_error());
mysql_select_db(DB_NAME) or send_error(mysql_error());

////
// Check whether the images and files folders are writable
if (!is_writable(DIR_FILES)) {
	send_error('Files folder ('.DIR_FILES.') is not writable.');
}
if (!is_writable(DIR_IMAGES)) {
	send_error('Images folder ('.DIR_IMAGES.') is not writable.');
}
if (!is_writable(DIR_IMAGES_ORIGINAL)) {
	send_error('Images Original folder ('.DIR_IMAGES_ORIGINAL.') is not writable.');
}
if (!is_writable(DIR_UPLOAD_TMP)) {
	send_error('Upload Tmp folder ('.DIR_UPLOAD_TMP.') is not writable.');
}

if (defined('SESSION_PATH'))
	session_save_path(SESSION_PATH);
session_name(SESSION_NAME);
session_start();

if ($_POST['keep_session_alive']) die();

/*if (empty($_SESSION['user_id']) && !ereg('^(.*)/login\.php$', $_SERVER['PHP_SELF'])) {
	$_SESSION['redir'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php", true, 302);
	exit(0);
}*/

/* ?> */