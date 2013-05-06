<?php
include('_includes/application_top.php');

ob_start();

////
// Strip get from Request URI
if (strstr($_SERVER['REQUEST_URI'], '?')) {
	$request_split = explode('?', $_SERVER['REQUEST_URI'], 2);
	$_SERVER['REQUEST_URI'] = $request_split[0];
}

////
// Make the cat path to this file...
if ($PATH_FROM_ROOT != '/') {
	$PATH_FROM_ROOT = str_replace($PATH_FROM_ROOT, '/', $_SERVER['REQUEST_URI']);
} else {
	$PATH_FROM_ROOT = $_SERVER['REQUEST_URI'];
}
$urlPath_array = explode("/", $PATH_FROM_ROOT);

////
// Get rid of any duplicates and blanks
$tmp_array = array();
$n = sizeof($urlPath_array);
for ($i=0; $i<$n; $i++) {
	if (!empty($urlPath_array[$i])) {
		
		////
		// While we're at it, make sure it is in our clean filename format
		$tmp_array[] = clean_url($urlPath_array[$i]);
				
	}
}

$urlPath_array = $tmp_array;

if (empty($urlPath_array[0])) $urlPath_array[0] = 'home';

switch($urlPath_array[0]) {
	case 'home':
		include('_templates/home.php');
		break;
			
	default:
		send404();
		break;
	
}

include('_includes/application_bottom.php');

/* ?> */