<?php
function curPageURL() {
	$pageURL = 'http';
 	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 	$pageURL .= "://";
 	if ($_SERVER["SERVER_PORT"] != "80") {
  		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 	} else {
  		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 	}
 	return $pageURL;
}

function clean_url($url){
	$url = strtolower(trim($url));
	$remove_chars  = array( "([\40])" , "([^a-zA-Z0-9-])", "(-{2,})" );
	$replace_with = array("-", "", "-");
	return preg_replace($remove_chars, $replace_with, $url);
}

function send404() {
	header("HTTP/1.0 404 Not Found");
	include(DIR_TEMPLATES . 'sitemap.php');
	include('_includes/application_bottom.php');
	die();
}

/* ?> */