<?php require('_includes/application_top.php');

if (empty($_GET['category_id'])) {
	header("Location: adminindex.php", true, 301);
	exit();
}

$get_category_fields = mysql_query("select * from cms_categories_fields where category_id = ".(int)$_GET['category_id']." limit 1");
$category_fields = mysql_fetch_assoc($get_category_fields);

$get_category = mysql_query("select * from cms_categories where category_id = ".(int)$_GET['category_id']." limit 1");
$category = mysql_fetch_assoc($get_category);

if (!empty($_POST) && $_POST['action'] == 'config') {
	
	mysql_query("update cms_categories_fields set title_1_text = '".mysql_real_escape_string($_POST['title_1_text'])."',
																								show_title_show = ".(int)$_POST['show_title_show'].",
																								title_2_text = '".mysql_real_escape_string($_POST['title_2_text'])."',
																								title_2_show = ".(int)$_POST['title_2_show'].",
																								title_3_text = '".mysql_real_escape_string($_POST['title_3_text'])."',
																								title_3_show = ".(int)$_POST['title_3_show'].",
																								date_text = '".mysql_real_escape_string($_POST['date_text'])."',
																								date_show = ".(int)$_POST['date_show'].",
																								content_1_text = '".mysql_real_escape_string($_POST['content_1_text'])."',
																								content_1_show = ".(int)$_POST['content_1_show'].",
																								content_1_wysiwyg = ".(int)$_POST['content_1_wysiwyg'].",
																								content_2_text = '".mysql_real_escape_string($_POST['content_2_text'])."',
																								content_2_show = ".(int)$_POST['content_2_show'].",
																								content_2_wysiwyg = ".(int)$_POST['content_2_wysiwyg'].",
																								image_1_text = '".mysql_real_escape_string($_POST['image_1_text'])."',
																								image_1_show = ".(int)$_POST['image_1_show'].",
																								image_2_text = '".mysql_real_escape_string($_POST['image_2_text'])."',
																								image_2_show = ".(int)$_POST['image_2_show'].",
																								image_3_text = '".mysql_real_escape_string($_POST['image_3_text'])."',
																								image_3_show = ".(int)$_POST['image_3_show'].",
																								pdf_text = '".mysql_real_escape_string($_POST['pdf_text'])."',
																								pdf_show = ".(int)$_POST['pdf_show'].",
																								file_1_text = '".mysql_real_escape_string($_POST['file_1_text'])."',
																								file_1_show = ".(int)$_POST['file_1_show'].",
																								file_2_text = '".mysql_real_escape_string($_POST['file_2_text'])."',
																								file_2_show = ".(int)$_POST['file_2_show'].",
																								f1_text = '".mysql_real_escape_string($_POST['f1_text'])."',
																								f1_show = ".(int)$_POST['f1_show'].",
																								f2_text = '".mysql_real_escape_string($_POST['f2_text'])."',
																								f2_show = ".(int)$_POST['f2_show'].",
																								f3_text = '".mysql_real_escape_string($_POST['f3_text'])."',
																								f3_show = ".(int)$_POST['f3_show'].",
																								active_show = ".(int)$_POST['active_show'].",
																								gallery_image_text = '".mysql_real_escape_string($_POST['gallery_image_text'])."',
																								gallery_image_count = ".(int)$_POST['gallery_image_count'].",
																								gallery_title_text = '".mysql_real_escape_string($_POST['gallery_title_text'])."',
																								gallery_title_show = ".(int)$_POST['gallery_title_show'].",
																								gallery_description_text = '".mysql_real_escape_string($_POST['gallery_description_text'])."',
																								gallery_description_show = ".(int)$_POST['gallery_description_show']."
																								where category_id = ".(int)$_GET['category_id']."
																								limit 1") or die (mysql_error());
	
	mysql_query("update cms_categories set category_allow_delete = ".(int)$_POST['category_allow_delete'].",
																				 category_allow_content = ".(int)$_POST['category_allow_content'].",
																				 category_allow_subcats = ".(int)$_POST['category_allow_subcats'].",
																				 category_show_subcats = ".(int)$_POST['category_show_subcats'].",
																				 category_allow_subcat_content = ".(int)$_POST['category_allow_subcat_content'].",
																				 category_allow_subcat_delete = ".(int)$_POST['category_allow_subcat_delete'].",
																				 category_keep_original_images = ".(int)$_POST['category_keep_original_images']."
																				 where category_id = ".(int)$_GET['category_id']."
																				 limit 1");
	
	////
	// Delete the resizes
	mysql_query("delete from cms_categories_image_sizes where category_id = ".(int)$_GET['category_id']);
	
	////
	// Loop through the image resizes
	for($i=0,$n=count($_POST['image_folder']);$i<$n;$i++) {
		if (!empty($_POST['image_folder'][$i])) {
			mysql_query("insert into cms_categories_image_sizes set category_id = ".(int)$_GET['category_id'].",
																															image_width = ".(int)$_POST['image_width'][$i].",
																															image_height = ".(int)$_POST['image_height'][$i].",
																															image_crop = ".(int)$_POST['image_crop'][$i].",
																															image_thumbnail = ".(int)$_POST['image_thumbnail'][$i].",
																															image_folder = '".mysql_real_escape_string($_POST['image_folder'][$i])."',
																															image_custom_imagemagick = '".mysql_real_escape_string($_POST['image_custom_imagemagick'][$i])."'");			
		}
	}
	
	$_SESSION['messages']['admin_index'][] = 'Category Config Updated';
	
	header("Location: adminindex.php", true, 301);
	exit();
	
} elseif (!empty($_POST) && $_POST['action'] == 'resize') {
	
	////
	// Get resizes
	$get_to_size = mysql_query("select * from cms_categories_image_sizes where category_id = ".(int)$category['category_id']." and image_folder = '".mysql_real_escape_string($_POST['resize_to'])."' limit 1");
	$to_size = mysql_fetch_assoc($get_to_size);
	
	$resize_count = 0;
	
	$get_content = mysql_query("select content_id, content_image_1, content_image_2, content_image_3 from cms_content where category_id = ".(int)$category['category_id']);
	while ($content = mysql_fetch_assoc($get_content)) {
		
		if (!empty($content['content_image_1'])) {
			resize_image($content['content_image_1'], DIR_IMAGES . $_POST['resize_from'].'/'.$content['content_image_1'] , DIR_IMAGES . $to_size['image_folder'].'/', $to_size['image_width'], $to_size['image_height'], ($to_size['image_crop']), $to_size['image_custom_imagemagick'], false);
			$resize_count++;
		}
		if (!empty($content['content_image_2'])) {
			resize_image($content['content_image_2'], DIR_IMAGES . $_POST['resize_from'].'/'.$content['content_image_2'] , DIR_IMAGES . $to_size['image_folder'].'/', $to_size['image_width'], $to_size['image_height'], ($to_size['image_crop']), $to_size['image_custom_imagemagick'], false);
			$resize_count++;
		}
		if (!empty($content['content_image_3'])) {
			resize_image($content['content_image_3'], DIR_IMAGES . $_POST['resize_from'].'/'.$content['content_image_3'] , DIR_IMAGES . $to_size['image_folder'].'/', $to_size['image_width'], $to_size['image_height'], ($to_size['image_crop']), $to_size['image_custom_imagemagick'], false);
			$resize_count++;
		}
		
		$get_image_gallery = mysql_query("select image_name from cms_content_gallery_images where content_id = ".(int)$content['content_id']." and image_name != '' limit ".(int)$category_fields['gallery_image_count']);
		while ($gallery_image = mysql_fetch_assoc($get_image_gallery)) {
			resize_image($gallery_image['image_name'], DIR_IMAGES . $_POST['resize_from'].'/'.$gallery_image['image_name'] , DIR_IMAGES . $to_size['image_folder'].'/', $to_size['image_width'], $to_size['image_height'], ($to_size['image_crop']), $to_size['image_custom_imagemagick'], false);
			$resize_count++;
		}
		
	}
	
	$_SESSION['messages']['category_edit_config'][] = $resize_count.' images successfully resized.';
	
	header("Location: ".$_SERVER['REQUEST_URI'], true, 301);
	exit();
		
}

$_message_name = 'category_edit_config';

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1>Edit Category Config</h1></div>
  <form action="category_edit_config.php?category_id=<?php echo $_GET['category_id'] ?>" method="post">
    <input type="hidden" name="action" value="config" />
    <table class="table table-bordered table-striped table-hover">
      <tr>
        <th colspan="3">Edit Config</th>
      </tr>
      <tr>
        <th class="span2">Allow Content:</td>
        <td class="span1"><input type="checkbox" name="category_allow_content" value="1"<?php echo (!empty($category['category_allow_content'])?' checked="checked"':'') ?> /></td>
        <td>* Will be cascaded to subcats on creation</td>
      </tr>
<?php if (empty($category['category_parent_id'])) { ?>
      <tr>
        <th>Allow Delete:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="category_allow_delete" value="1"<?php echo (!empty($category['category_allow_delete'])?' checked="checked"':'') ?> /></td>
        <td>&nbsp;</td>
      </tr>
<?php } ?>
      <tr>
        <th>Allow Subcats:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="category_allow_subcats" value="1"<?php echo (!empty($category['category_allow_subcats'])?' checked="checked"':'') ?> /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th>Allow Subcat Content:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="category_allow_subcat_content" value="1"<?php echo (!empty($category['category_allow_subcat_content'])?' checked="checked"':'') ?> /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th>Allow Delete Subs:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="category_allow_subcat_delete" value="1"<?php echo (!empty($category['category_allow_subcat_delete'])?' checked="checked"':'') ?> /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th>Allow Show Subs:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="category_show_subcats" value="1"<?php echo (!empty($category['category_show_subcats'])?' checked="checked"':'') ?> /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th colspan="3">Edit Content Config</th>
      </tr>
      <tr>
        <th>Title 1:</td>
        <td>&nbsp;</td>
        <td><input type="text" name="title_1_text" value="<?php echo stripslashes($category_fields['title_1_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Show Title Show:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="show_title_show" value="1"<?php echo (!empty($category_fields['show_title_show'])?' checked="checked"':'') ?> /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th>Title 2:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="title_2_show" value="1"<?php echo (!empty($category_fields['title_2_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="title_2_text" value="<?php echo stripslashes($category_fields['title_2_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Title 3:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="title_3_show" value="1"<?php echo (!empty($category_fields['title_3_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="title_3_text" value="<?php echo stripslashes($category_fields['title_3_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Date:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="date_show" value="1"<?php echo (!empty($category_fields['date_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="date_text" value="<?php echo stripslashes($category_fields['date_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Content 1:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="content_1_show" value="1"<?php echo (!empty($category_fields['content_1_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="content_1_text" value="<?php echo stripslashes($category_fields['content_1_text']) ?>" /><br />
        WYSIWYG?&nbsp;<input type="checkbox" name="content_1_wysiwyg" value="1"<?php echo (!empty($category_fields['content_1_wysiwyg'])?' checked="checked"':'') ?> /></td>
      </tr>
      <tr>
        <th>Content 2:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="content_2_show" value="1"<?php echo (!empty($category_fields['content_2_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="content_2_text" value="<?php echo stripslashes($category_fields['content_2_text']) ?>" /><br />
        WYSIWYG?&nbsp;<input type="checkbox" name="content_2_wysiwyg" value="1"<?php echo (!empty($category_fields['content_2_wysiwyg'])?' checked="checked"':'') ?> /></td>
      </tr>
      <tr>
        <th>Image 1:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="image_1_show" value="1"<?php echo (!empty($category_fields['image_1_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="image_1_text" value="<?php echo stripslashes($category_fields['image_1_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Image 2:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="image_2_show" value="1"<?php echo (!empty($category_fields['image_2_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="image_2_text" value="<?php echo stripslashes($category_fields['image_2_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Image 3:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="image_3_show" value="1"<?php echo (!empty($category_fields['image_3_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="image_3_text" value="<?php echo stripslashes($category_fields['image_3_text']) ?>" /></td>
      </tr>
      <tr>
        <th>PDF:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="pdf_show" value="1"<?php echo (!empty($category_fields['pdf_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="pdf_text" value="<?php echo stripslashes($category_fields['pdf_text']) ?>" /></td>
      </tr>
      <tr>
        <th>File 1:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="file_1_show" value="1"<?php echo (!empty($category_fields['file_1_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="file_1_text" value="<?php echo stripslashes($category_fields['file_1_text']) ?>" /></td>
      </tr>
      <tr>
        <th>File 2:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="file_2_show" value="1"<?php echo (!empty($category_fields['file_2_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="file_2_text" value="<?php echo stripslashes($category_fields['file_2_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Field 1:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="f1_show" value="1"<?php echo (!empty($category_fields['f1_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="f1_text" value="<?php echo stripslashes($category_fields['f1_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Field 2:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="f2_show" value="1"<?php echo (!empty($category_fields['f2_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="f2_text" value="<?php echo stripslashes($category_fields['f2_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Field 3:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="f3_show" value="1"<?php echo (!empty($category_fields['f3_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="f3_text" value="<?php echo stripslashes($category_fields['f3_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Show Active:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="active_show" value="1"<?php echo (!empty($category_fields['active_show'])?' checked="checked"':'') ?> /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th colspan="3">Gallery Images</th>
      </tr>
      <tr>
        <th>Gallery Images:</td>
        <td align="center" style="padding: 5px;"><input class="input-mini" type="text" name="gallery_image_count" value="<?php echo (int)$category_fields['gallery_image_count'] ?>" /></td>
        <td><input class="" type="text" name="gallery_image_text" value="<?php echo stripslashes($category_fields['gallery_image_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Title:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="gallery_title_show" value="1"<?php echo (!empty($category_fields['gallery_title_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="gallery_title_text" value="<?php echo stripslashes($category_fields['gallery_title_text']) ?>" /></td>
      </tr>
      <tr>
        <th>Description:</td>
        <td align="center" style="padding: 5px;"><input type="checkbox" name="gallery_description_show" value="1"<?php echo (!empty($category_fields['gallery_description_show'])?' checked="checked"':'') ?> /></td>
        <td><input type="text" name="gallery_description_text" value="<?php echo stripslashes($category_fields['gallery_description_text']) ?>" /></td>
      </tr>

      <tr>
        <th colspan="3">Image Resizes</th>
      </tr>
      <tr>
      	<td colspan="3"><input type="checkbox" name="category_keep_original_images" value="1"<?php echo (!empty($category['category_keep_original_images'])?' checked="checked"':'') ?> />&nbsp;&nbsp;Keep Original</td>
      </tr>
      <tr>
      	<td colspan="3" style="text-align:center;"><a href="#" onclick="addImageResize(); return false;">Add Size</a></td>
      </tr>
      
      
      
      <tr>
      	<td colspan="3"><table class="table-bordered">
            <tr>
            <td><strong>Folder</strong></td>
            <td><strong>Width</strong></td>
            <td><strong>Height</strong></td>
            <td><strong>Crop</strong></td>
            <td><strong>Thumbnail</strong></td>
<?php if (defined('USE_IMAGEMAGICK') && USE_IMAGEMAGICK === true) { ?>
            <td><strong>Custom Image Magick</strong></td>
<?php } ?>
            <td>&nbsp;</td>
          </tr>
<?php $folders = array();
$image_folder = scandir(DIR_IMAGES);
if (!is_array($image_folder)) $image_folder = array();
foreach($image_folder as $item) {
	if ($item != '.' && $item != '..') {
		$resize_image_folder = DIR_IMAGES.$item.'/';
		if (is_dir($resize_image_folder) && $item != 'original') {
			$folders[] = $item; 
		}
	}
}

$get_category_resizes = mysql_query("select * from cms_categories_image_sizes where category_id = ".(int)$_GET['category_id']);
if (mysql_num_rows($get_category_resizes) < 1) {
	$get_category_resizes = mysql_query("select null");
	define('NULL_RESIZE', true);
}
while ($size = mysql_fetch_assoc ($get_category_resizes)) { ?>
          <tr class="image-resize">
            <td><select name="image_folder[]">
                <option value="">Select Folder</option>
<?php for($i=0,$n=count($folders);$i<$n;$i++) { ?>
                <option value="<?php echo $folders[$i] ?>"<?php echo ($size['image_folder'] == $folders[$i]?' selected="selected"':'') ?>><?php echo $folders[$i] ?></option>
<?php } ?>
              </select></td>
            <td><input type="text" name="image_width[]" size="3" value="<?php echo $size['image_width'] ?>" />
                px</td>
            <td><input type="text" name="image_height[]" size="3" value="<?php echo $size['image_height'] ?>" />
                px</td>
            <td align="center"><input type="checkbox" name="image_crop[]" value="1"<?php echo (!empty($size['image_crop'])?' checked="checked"':'') ?> /></td>
            <td align="center"><input type="checkbox" name="image_thumbnail[]" value="1"<?php echo (!empty($size['image_thumbnail'])?' checked="checked"':'') ?> /></td>
<?php if (defined('USE_IMAGEMAGICK') && USE_IMAGEMAGICK === true) { ?>
            <td><input type="text" name="image_custom_imagemagick[]" size="20" value="<?php echo $size['image_custom_imagemagick'] ?>" /></td>
<?php } ?>
            <td align="center"><a href="#" onclick="removeImageResize(this); return false;">Remove</a></td>
          </tr>
<?php } ?>
          </table>
          <!--script type="text/javascript">updateImageResizeNames();</script-->
          
<?php if (defined('USE_IMAGEMAGICK') && USE_IMAGEMAGICK === true) { ?>
          <br /><strong>Please Note:</strong>&nbsp;Entering an Invalid 'Custom Image Magick' will course the image resizes to fail. e.g.<br /><br />
          <pre>convert inputimagename.jpg -resize 60x60 <strong>customcommand</strong> outputfilename.jpg</pre>
          <br />
<?php } ?>
        </td>
      </tr>
      <tr>
        <th>&nbsp;</td>
        <td colspan="2"><button class="btn" type="submit" name="submit">Update Config</button></td>
      </tr>
    </table>
  </form>
<?php if (!defined('NULL_RESIZE') && mysql_num_rows($get_category_resizes) > 1) {
	mysql_data_seek($get_category_resizes, 0); ?>
	
	<div class="alert alert-warning">folders must be CHMOD 777 for below to work.</div>
	
  <form action="category_edit_config.php?category_id=<?php echo $_GET['category_id'] ?>" method="post">
    <input type="hidden" name="action" value="resize" />
    <table class="table table-bordered table-striped">
      <tr>
        <th colspan="3">Resize Category Images</th>
      </tr>
      <tr>
        <th>From:</td>
        <td align="center" style="padding: 5px;">
          <select name="resize_from" id="resize_from">
<?php if (!empty($category['category_keep_original_images'])) { ?>
            <option value="original">Original File</option>
<?php } ?>
<?php while ($size = mysql_fetch_assoc($get_category_resizes)) { ?>
            <option value="<?php echo $size['image_folder'] ?>"><?php echo $size['image_folder'] . (!empty($size['image_crop'])?' - Cropped at '.$size['image_width'].'x'.$size['image_height']:(empty($size['image_width'])?' - '.$size['image_height'].'px max height':(empty($size['image_height'])?' - '.$size['image_width'].'px max width':' - '.$size['image_width'].'x'.$size['image_height'].' max bounds'))); ?></option>
<?php }
	mysql_data_seek($get_category_resizes, 0); ?>
          </select>
        </td>
      </tr>
      <tr>
        <th>To:</td>
        <td align="center" style="padding: 5px;">
          <select name="resize_to" id="resize_to">
<?php while ($size = mysql_fetch_assoc($get_category_resizes)) { ?>
            <option value="<?php echo $size['image_folder'] ?>"><?php echo $size['image_folder'] . (!empty($size['image_crop'])?' - Cropped at '.$size['image_width'].'x'.$size['image_height']:(empty($size['image_width'])?' - '.$size['image_height'].'px max height':(empty($size['image_height'])?' - '.$size['image_width'].'px max width':' - '.$size['image_width'].'x'.$size['image_height'].' max bounds'))); ?></option>
<?php } ?>
          </select>
        </td>
      </tr>
      <tr>
        <th style="text-align: left; font-weight: normal;" colspan="2"><span style="font-weight: bold;">Please Note:</span> Only content images in this category that are found in the 'From' folder will be resized. This <strong>will not</strong> resize images for any content images in sub categories.<br /><br />Please make sure the 'From' images are bigger in pixels, than the 'To' images.<br /><br />This is only meant for use when creating new Image Resizes.</td>
      </tr>
      <tr>
        <th>&nbsp;</td>
        <td colspan="2"><button class="btn" type="submit" name="submit" onclick="if($F('resize_from') == $F('resize_to')) { alert('The From and To folders must be different.'); return false; } else return true;" >Resize</button></td>
      </tr>
    </table>
  </form>
<?php } require(DIR_ADMININCLUDES . 'footer.php');

/* ?>
*/