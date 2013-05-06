<?php date_default_timezone_set('America/Los_Angeles');

////
// Database Config
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'username');	
define('DB_PASSWORD', 'password');	
define('DB_NAME', 'database');	

////
// Session Config
define('SESSION_LIFETIME', 7200);
define('SESSION_NAME', 'cms');
define('SESSION_PATH', '/tmp');

////
// Session Keep Alive (uncomment to start)
// define('KEEP_SESSION_ALIVE', 20);

////
// Directories
define('DIR_FILES', DIR_CMSROOT.'6bFQM3N9IqgAHoP/');
define('DIR_IMAGES', DIR_CMSROOT.'_images/');
define('DIR_IMAGES_ORIGINAL', DIR_IMAGES.'original/');
define('DIR_TEMPLATES', DIR_CMSROOT.'_templates/');
define('DIR_UPLOAD_TMP', DIR_CMSROOT.'52Eb0wNACqfUcuD/');
$PATH_FROM_ROOT = '/';		

////
// URIs
define('URL_BASE', 'http://boilerplate'.$PATH_FROM_ROOT);
define('URL_FILES', URL_BASE . str_replace(DIR_CMSROOT, '', DIR_FILES));
define('URL_IMAGES', URL_BASE . str_replace(DIR_CMSROOT, '', DIR_IMAGES));
define('URL_IMAGES_ORIGINAL', URL_BASE . str_replace(DIR_CMSROOT, '', DIR_IMAGES_ORIGINAL));

////
// S3
//define('S3_ACTIVE', false);
//define('S3_KEY', '');
//define('S3_SECRET', '');
//define('S3_BUCKET', '');
//define('S3_IMAGES', '');
//define('S3_FILES', '');
//define('S3_URL', '');

////
// Other Config

// true, false, auto (Auto will check to see if Image Magick exists, and if it does then it'll set it to true)
$USE_IMAGEMAGICK = 'auto'; 

////
// This will fill the $_IMAGE_FOLDERS array with 'foldername'=>'folder url'
define('GENERATE_IMAGE_FOLDER_URLS', true);

/* ?> */