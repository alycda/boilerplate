<?php

////
// Set Error Reporting
ini_set('display_errors', 'On');
ini_set('error_reprting', E_ALL & ~E_NOTICE);
error_reporting(E_ALL & ~E_NOTICE);

////
// Set any fluid needed directories
define('DIR_CMSINCLUDES', dirname(__FILE__).(substr(dirname(__FILE__), -1)!='/'?'/':''));
define('DIR_CMSROOT', realpath(DIR_CMSINCLUDES.'../').'/');

////
// Include the main config file
require(DIR_CMSINCLUDES . 'config.php');

////
// Include the functions file
require(DIR_CMSINCLUDES . 'functions.php');

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
mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD) or die("database access denied");
mysqli_select_db(DB_NAME) or die("database does not exist");

if (defined('SESSION_PATH'))
	session_save_path(SESSION_PATH);
session_name(SESSION_NAME);
session_start();

////
// Generate the image folder URLs
$_IMAGE_FOLDERS = array();
if (defined('GENERATE_IMAGE_FOLDER_URLS') && GENERATE_IMAGE_FOLDER_URLS === true) {
	$get_image_folders = mysql_query("select image_folder from cms_categories_image_sizes group by image_folder");
	while ($image_folder = mysql_fetch_assoc($get_image_folders)) {
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			$_IMAGE_FOLDERS[$image_folder['image_folder']] = S3_URL . S3_IMAGES . $image_folder['image_folder'] . '/';
		} else {
			$_IMAGE_FOLDERS[$image_folder['image_folder']] = URL_IMAGES . $image_folder['image_folder'] . '/';
		}
	}
}

if ($_POST['keep_session_alive']) die();

/* ?> */