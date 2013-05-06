<?php

function send_error($message) {
	die('<div style="color: #fff; background-color: #f00; padding: 10px; text-align: center; font-weight: normal; font-family: Arial;">'.$message.'</div>');
}

function show_error() { 
	if (count($_error) > 0) { ?>
<div class="alert alert-error">
<?php echo implode('<br />', $_error) ?>
</div>	
<?php }
}

//if (count($_error) > 0) { 
//  
// echo implode('<br />', $_error)
//  
// } 

function show_breadcrumb($category_id) {
	
	$categories = array();
	if ($category_id > 0) {
		$categories[sizeof($categories)] = $category_id;
	}
	get_parent_categories($categories, $category_id);
	
	echo '  <ul class="breadcrumb">'."\n";
	echo '    <li><a href="">Content Management</a></li>'."\n";
	$categories = array_reverse($categories);
	for($i=0;$i<count($categories);$i++) {
		$get_category_info = mysql_query("select category_id, category_name from cms_categories where category_id = ".(int)$categories[$i]." limit 1");
		$category_info = mysql_fetch_assoc($get_category_info);
		echo '    <li'.($category_id==$categories[$i]?' class="active"':'').'><span class="divider">/</span><a href="'.($category_id==$categories[$i]&&(strstr($_SERVER['PHP_SELF'], 'content_manage.php')||strstr($_SERVER['PHP_SELF'], 'content_manage_photos.php')||strstr($_SERVER['PHP_SELF'], 'content_edit.php'))?(strstr($_SERVER['PHP_SELF'], 'content_manage_photos.php')?'content_manage_photos.php?category_id':'content_manage.php?category_id'):'index.php?parent_id').'='.$category_info['category_id'].'">'.stripslashes($category_info['category_name']).'</a></li>'."\n";
	}
	echo '  </ul>'."\n";
}

function get_parent_categories(&$categories, $category_id) {
	$parent_categories_query = mysql_query("select category_parent_id from cms_categories where category_id = " . (int)$category_id);
	while ($parent_categories = mysql_fetch_assoc($parent_categories_query)) {
		if ($parent_categories['category_parent_id'] == 0) return true;
		$categories[sizeof($categories)] = $parent_categories['category_parent_id'];
		if ($parent_categories['category_parent_id'] != $category_id) {
			get_parent_categories($categories, $parent_categories['category_parent_id']);
		}
	}
}

function make_filename_safe($filename) {
	
		$temp = $filename;
		//$temp = strtolower($temp);

		// Replace spaces with a '_'
		$temp = str_replace(" ", "_", $temp);
		$result = '';
		for ($i=0; $i<strlen($temp); $i++) {
				if (preg_match('([0-9]|[A-Za-z]|_)', $temp[$i])) {
						$result = $result . $temp[$i];
				}    
		}

		return $result;
} 

function clean_url($url){
	$url = strtolower(trim($url));
	$remove_chars  = array( "([\40])" , "([^a-zA-Z0-9-])", "(-{2,})" );
	$replace_with = array("-", "", "-");
	return preg_replace($remove_chars, $replace_with, $url);
}

function resize_content_image($uploaded_file, $category_id, $current_filename=false) {

	if (isset($uploaded_file) && is_uploaded_file($uploaded_file["tmp_name"]) && $uploaded_file["error"] == 0) {
		
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			$s3 = new S3(S3_KEY, S3_SECRET);
		}
		
		$split_filename = explode('.', $uploaded_file["name"], 2);
		$filename = time().make_filename_safe($split_filename[0]).'.'.$split_filename[1];
		
		move_uploaded_file($uploaded_file["tmp_name"], DIR_IMAGES_ORIGINAL.$filename);
		
		////
		// Get resizes
		$get_resize_sizes = mysql_query("select * from cms_categories_image_sizes where category_id = ".(int)$category_id);
		if (mysql_num_rows($get_resize_sizes) > 0) {
			while ($size = mysql_fetch_assoc($get_resize_sizes)) {
				resize_image($filename, DIR_IMAGES_ORIGINAL.$filename, DIR_IMAGES . $size['image_folder'].'/', $size['image_width'], $size['image_height'], ($size['image_crop']), false);
				
				if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
					$s3->putObjectFile(DIR_IMAGES . $size['image_folder'].'/'.$filename, 
														 S3_BUCKET, 
														 S3_IMAGES . $size['image_folder'].'/' . $filename, 
														 S3::ACL_PUBLIC_READ);
					@unlink(DIR_IMAGES . $size['image_folder'].'/'.$filename);
				}
				
				////
				// Delete the old photo
				if (!empty($current_filename)) {
					@unlink(DIR_IMAGES . $size['image_folder'].'/'.$current_filename);
					
					if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
						$s3->deleteObject(S3_BUCKET, S3_IMAGES . $size['image_folder'] . '/' . $current_filename);
					}
				}
			}
		}
		
		////
		// Check whether to keep the original image
		$keep_original = mysql_num_rows(mysql_query("select category_id from cms_categories where category_id = ".(int)$category_id." and category_keep_original_images = 1 limit 1"));
		if ($keep_original < 1) {
			@unlink(DIR_IMAGES_ORIGINAL.$filename);
		} elseif (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			
			$s3->putObjectFile(DIR_IMAGES_ORIGINAL.$filename, 
												 S3_BUCKET, 
												 S3_IMAGES . 'original/' . $filename, 
												 S3::ACL_PUBLIC_READ);
			
		}
		
		if (!empty($current_filename)) {
			////
			// Delete the old original image
			@unlink(DIR_IMAGES_ORIGINAL.$current_filename);
			if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
				$s3->deleteObject(S3_BUCKET, S3_IMAGES . 'original' . '/' . $current_filename);
			}
		}
		
		return $filename;
		
	} else {
		if (empty($current_filename)) return '';
		return $current_filename;
	}

}

function resize_image($current_image_name, $current_image_location, $new_folder, $max_width, $max_height, $crop=false, $generate_new_name=false) {
						
	$image_extension = substr($current_image_name, strrpos($current_image_name, '.') + 1);
	$split_image_name = array();
	$split_image_name[0] = substr($current_image_name, 0, strrpos($current_image_name, '.') - 1);
	$split_image_name[1] = $image_extension;
	
	if ($generate_new_name === true) {
		$current_image_name = time().make_filename_safe($split_image_name[0]).'.'.$split_image_name[1];
		$split_image_name = explode('.', $current_image_name, 2);
	}
			
	if (!is_writable($new_folder)) {
		return false;
	}
		
	if (empty($max_width) && empty($max_height)) {
		return false;
	}
					
	if (defined('USE_IMAGEMAGICK') && USE_IMAGEMAGICK === true && !empty($im_custom)) {
		if (!empty($crop)) {
			
			$image_size = getimagesize($current_image_location);
			$current_image_width = $image_size[0];
			$current_image_height = $image_size[1];
			
			$ratio = $current_image_width / $current_image_height;
			
			if ($max_width/$max_height > $ratio) {
				$new_height = round($max_width / $ratio);
				$new_width = round($max_width);
			} else {
				$new_width = round($max_height * $ratio);
				$new_height = round($max_height);
			}			
			exec('convert '.$current_image_location.' -resize '.$new_width.'x'.$new_height.' -gravity center -crop '.$max_width.'x'.$max_height.'+0+0 '.(!empty($im_custom)?$im_custom.' ':'').$new_folder . $current_image_name);
		} else {
			exec('convert '.$current_image_location.' -resize '.(!empty($max_width)?$max_width:'').'x'.(!empty($max_height)?$max_height:'').' '.(!empty($im_custom)?$im_custom.' ':'').$new_folder . $current_image_name);
		}
	} else {
						
		switch ($image_extension) {
			case 'gif':
				$image = imagecreatefromgif($current_image_location);
			break;
			case 'jpeg':
			case 'jpg':
				$image = imagecreatefromjpeg($current_image_location);
			break;
			case 'png':
				$image = imagecreatefrompng($current_image_location);
			break;
			default:
				return false;
		}
			
		$current_image_width = imagesx($image);
		$current_image_height = imagesy($image);
		
		if ($max_width > $current_image_width) {
			$max_width = $current_image_width;
		}
		if ($max_height > $current_image_height) {
			$max_height = $current_image_height;
		}
		
		$ratio = $current_image_width / $current_image_height;
			
		if (!empty($crop)) {
			
			if (empty($max_width) || empty($max_height)) {
				return false;
			}
			
			if ($max_width/$max_height > $ratio) {
				$new_height = round($max_width / $ratio);
				$new_width = round($max_width);
			} else {
				$new_width = round($max_height * $ratio);
				$new_height = round($max_height);
			}
			
			$x_mid = $new_width / 2;
			$y_mid = $new_height / 2;
						
			if ($image_extension == 'gif') {
				$image_holder = imagecreate($new_width, $new_height);
			} else {
				$image_holder = imagecreatetruecolor($new_width, $new_height);
			}
				 
			if (($image_extension == 'gif') || ($image_extension == 'png')) {
				$trnprt_indx = imagecolortransparent($image);
				if ($trnprt_indx >= 0) {
					$trnprt_color = imagecolorsforindex($image, $trnprt_indx);
					$trnprt_indx = imagecolorallocate($image_holder, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
					imagefill($image_holder, 0, 0, $trnprt_indx);
					imagecolortransparent($image_holder, $trnprt_indx);
					imagetruecolortopalette($image_holder, true, imagecolorstotal($image) );
				} elseif ($image_extension == 'png') {
					imagealphablending($image_holder, false);
					$color = imagecolorallocatealpha($image_holder, 0, 0, 0, 127);
					imagefill($image_holder, 0, 0, $color);
					imagesavealpha($image_holder, true);
				}
			}
			imagecopyresampled($image_holder, $image, 0, 0, 0, 0, $new_width, $new_height, $current_image_width, $current_image_height);
			
			if ($image_extension == 'gif') {
				$new_image = imagecreate($max_width, $max_height);
			} else {
				$new_image = imagecreatetruecolor($max_width, $max_height);
			}
			if (($image_extension == 'gif') || ($image_extension == 'png')) {
				$trnprt_indx = imagecolortransparent($image_holder);
				if ($trnprt_indx >= 0) {
					$trnprt_color = imagecolorsforindex($image_holder, $trnprt_indx);
					$trnprt_indx = imagecolorallocate($new_image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
					imagefill($new_image, 0, 0, $trnprt_indx);
					imagecolortransparent($new_image, $trnprt_indx);
					imagetruecolortopalette($new_image, true, imagecolorstotal($image_holder) );
				} elseif ($image_extension == 'png') {
					imagealphablending($new_image, false);
					$color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
					imagefill($new_image, 0, 0, $color);
					imagesavealpha($new_image, true);
				}
			}
			imagecopyresampled($new_image, $image_holder, 0, 0, ($x_mid-($max_width/2)), ($y_mid-($max_height/2)), $max_width, $max_height, $max_width, $max_height);
			
			imagedestroy($image);
			imagedestroy($image_holder);
					
		} else {
			
			if (empty($max_height)) {
				$new_width = round($max_width);
				$new_height = round($max_width / $ratio);
			} elseif (empty($max_width)) {
				$new_height = round($max_height);
				$new_width = round($max_height * $ratio);
			} else {
				$scale = min($max_width/$current_image_width, $max_height/$current_image_height);
				$new_width = floor($scale*$current_image_width);
				$new_height = floor($scale*$current_image_height);
			}
			
			if ($image_extension == 'gif') {
				$new_image = imagecreate($new_width, $new_height);
			} else {
				$new_image = imagecreatetruecolor($new_width, $new_height);
			}
			if (($image_extension == 'gif') || ($image_extension == 'png')) {
				$trnprt_indx = imagecolortransparent($image);
				if ($trnprt_indx >= 0) {
					$trnprt_color = imagecolorsforindex($image, $trnprt_indx);
					$trnprt_indx = imagecolorallocate($new_image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
					imagefill($new_image, 0, 0, $trnprt_indx);
					imagecolortransparent($new_image, $trnprt_indx);
					imagetruecolortopalette($new_image, true, imagecolorstotal($image) );
				} elseif ($image_extension == 'png') {
					imagealphablending($new_image, false);
					$color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
					imagefill($new_image, 0, 0, $color);
					imagesavealpha($new_image, true);
				}
			}
			imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $current_image_width, $current_image_height);
			imagedestroy($image);
			
		}
		
		switch($image_extension) {
			case 'jpg':
			case 'jpeg':
				imagejpeg($new_image, $new_folder . $current_image_name, 100); // Best Quality
				break;
			
			case 'gif':
				imagegif($new_image, $new_folder . $current_image_name);
				break;
			
			case 'png':
				imagesavealpha($new_image, true);
				imagepng($new_image, $new_folder . $current_image_name, 0); // No Compression
				break;
		}
		
		imagedestroy($new_image);
		
	}
	
	return $current_image_name;
	  
}

function upload_file($uploaded_file, $current_filename=false) {
	
	if (isset($uploaded_file) && is_uploaded_file($uploaded_file["tmp_name"]) && $uploaded_file["error"] == 0) {
		
		$split_filename = explode('.', $uploaded_file["name"], 2);
		$filename = time().make_filename_safe($split_filename[0]).'.'.$split_filename[1];
		
		move_uploaded_file($uploaded_file["tmp_name"], DIR_FILES.$filename);
		
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
		
			$s3 = new S3(S3_KEY, S3_SECRET);
			if ($s3->putObjectFile(DIR_FILES.$filename, 
												 S3_BUCKET, 
												 S3_FILES . $filename, 
												 S3::ACL_PUBLIC_READ)) {
				@unlink(DIR_FILES.$filename);
			}
			
		}
		
		return $filename;
		
	} else {
		return $current_filename;
	}
}

function delete_content_image($current_filename, $category_id) {
	
	if (empty($current_filename)) return;
	
	$get_resize_sizes = mysql_query("select * from cms_categories_image_sizes where category_id = ".(int)$category_id);
	if (mysql_num_rows($get_resize_sizes) > 0) {
		while ($size = mysql_fetch_assoc($get_resize_sizes)) {
			@unlink(DIR_IMAGES . $size['image_folder'].'/'.$current_filename);
			if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
				$s3 = new S3(S3_KEY, S3_SECRET);
				$s3->deleteObject(S3_BUCKET, S3_IMAGES . $size['image_folder'] . '/' . $current_filename);
			}
		}
	}
	
	////
	// Check whether to keep the original image
	@unlink(DIR_IMAGES_ORIGINAL.$current_filename);
	if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
		$s3 = new S3(S3_KEY, S3_SECRET);
		$s3->deleteObject(S3_BUCKET, S3_IMAGES . 'original/' . $current_filename);
	}
}

function delete_file($current_filename) {
	@unlink(DIR_FILES.$current_filename);
	if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
		$s3 = new S3(S3_KEY, S3_SECRET);
		$s3->deleteObject(S3_BUCKET, S3_FILES . $current_filename);
	}
}

function delete_category($category_id) {
	
	////
	// Does this category have subcats
	$get_category_subcats = mysql_query("select category_id from cms_categories where category_parent_id = ".(int)$category_id);
	while ($category_subcat = mysql_fetch_assoc ($get_category_subcats)) {
		delete_category($category_subcat['category_id']);
	}
	
	$get_category_content = mysql_query("select content_id, content_image_1, content_image_2, content_image_3, content_pdf, content_file_1, content_file_2 from cms_content where category_id = ".(int)$category_id);
	while ($category_content = mysql_fetch_assoc ($get_category_content)) {
		delete_content_image($category_content['content_image_1'], $category_id);
		delete_content_image($category_content['content_image_2'], $category_id);
		delete_content_image($category_content['content_image_3'], $category_id);
		
		delete_file($category_content['content_pdf']);
		delete_file($category_content['content_file_1']);
		delete_file($category_content['content_file_2']);
		
		$get_content_images = mysql_query("select image_name from cms_content_gallery_images where content_id = ".(int)$category_content['content_id']);
		while ($content_image = mysql_fetch_assoc($get_content_images)) {
			delete_content_image($content_image['image_name'], $category_id);
		}
		
		mysql_query("delete from cms_content_gallery_images where content_id = ".(int)$category_content['content_id']);
		mysql_query("delete from cms_content where content_id = ".(int)$category_content['content_id']." limit 1");
	}
	
	mysql_query("delete from cms_categories_image_sizes where category_id = ".(int)$category_id);
	mysql_query("delete from cms_categories_fields where category_id = ".(int)$category_id." limit 1");
	mysql_query("delete from cms_categories where category_id = ".(int)$category_id." limit 1");

}

function get_content_thumbnail_url($category_id) {
	$get_thumbnail = mysql_query("select image_folder from cms_categories_image_sizes where image_thumbnail = 1 and category_id = ".(int)$category_id." limit 1");
	$thumbnail = mysql_fetch_assoc ($get_thumbnail);
	if (!empty($thumbnail['image_folder'])) {
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			return S3_URL . S3_IMAGES . $thumbnail['image_folder'] . '/';
		} else {
			return URL_IMAGES . $thumbnail['image_folder'] . '/';
		}
	} else {
		return false;
	}
}

function add_ordinal($cdnl){
	$test_c = abs($cdnl) % 10;
	$ext = ((abs($cdnl) %100 < 21 && abs($cdnl) %100 > 4) ? 'th'
				 : (($test_c < 4) ? ($test_c < 3) ? ($test_c < 2) ? ($test_c < 1)
				 ? 'th' : 'st' : 'nd' : 'rd' : 'th'));
	return $cdnl.$ext;
}  

function geolocate_address($address_url) {
	$url = "http://maps.google.com/maps/geo?q=".urlencode($address_url)."&output=json&oe=utf8&sensor=false&key=ABQIAAAAYPe8B2-6-yxWYrpTt_0rvRRbPLBEGJ-cCvqwyXZZXs4N-ZSh9BRw-l6fIF9cicE1aW3K5IyjA5D1XA";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	curl_close($ch);
								
	$json = json_decode($response);
	return $json;
}

/* ?> */