<?php require('_includes/application_top.php');

$get_category = mysql_query("select * from cms_categories where category_id = ".(int)$_GET['category_id']." limit 1");
$category = mysql_fetch_assoc ($get_category);

$get_parent_category = mysql_query("select * from cms_categories where category_id = ".(int)$category['category_parent_id']." limit 1");
$parent_category = mysql_fetch_assoc ($get_parent_category);

if (!empty($_GET['deletegalleryimage'])) {
	
	////
	// Get this gallery image
	$get_gallery_image = mysql_query("select content_image_1 from cms_content where content_id = ".(int)$_GET['deletegalleryimage']." limit 1");
	$gallery_image = mysql_fetch_assoc($get_gallery_image);
	
	if (!empty($gallery_image['content_image_1'])) {
		
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			$s3 = new S3(S3_KEY, S3_SECRET);
		}
		
		////
		// Get resizes
		$get_resize_sizes = mysql_query("select * from cms_categories_image_sizes where category_id = ".(int)$_GET['category_id']);
		if (mysql_num_rows($get_resize_sizes) > 0) {
			while ($size = mysql_fetch_assoc($get_resize_sizes)) {
				if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
					$s3->deleteObject(S3_BUCKET, S3_IMAGES . $size['image_folder'] . '/' . $gallery_image['content_image_1']);
				} else {
					@unlink(DIR_IMAGES . $size['image_folder'].'/'.$gallery_image['content_image_1']);
				}
			}
		}
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			$s3->deleteObject(S3_BUCKET, S3_IMAGES . 'original/' . $gallery_image['image_name']);
		} else {
			@unlink(DIR_IMAGES_ORIGINAL . $gallery_image['image_name']);
		}
		
		mysql_query("delete from cms_content where content_id = ".(int)$_GET['deletegalleryimage']." limit 1");
		
	}
	
}
if (!empty($_GET['deletegallerymp3'])) {
	
	$get_gallery_mp3 = mysql_query("select content_file_1 from cms_content where content_id = ".(int)$_GET['deletegallerymp3']." limit 1");
	$gallery_mp3 = mysql_fetch_assoc($get_gallery_mp3);
	
	if (!empty($gallery_mp3['content_file_1'])) {
		
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			$s3 = new S3(S3_KEY, S3_SECRET);
			$s3->deleteObject(S3_BUCKET, S3_FILES . $gallery_image['content_file_1']);
		} else {
			@unlink(DIR_FILES . $gallery_image['content_file_1']);
		}
		
		mysql_query("update cms_content set content_file_1 = '' where content_id = ".(int)$_GET['deletegalleryimage']." limit 1");
		
	}
	
}

$get_category_fields = mysql_query("select * from cms_categories_fields where category_id = ".(int)$_GET['category_id']." limit 1");
$category_fields = mysql_fetch_assoc($get_category_fields);

$_error = array();
if (!empty($_POST)) {
	
	if (count($_error) < 1) {
						
		////
		// Gallery Images
		if ($category_fields['gallery_image_count'] > 0) {
			for($i=0;$i<$category_fields['gallery_image_count'];$i++) {
				if (!empty($_POST['gallery_image_id'][$i])) {
					$get_gallery_image = mysql_query("select * from cms_content where content_id = ".(int)$_POST['gallery_image_id'][$i]." and category_id = ".(int)$_GET['category_id']." limit 1");
					$gallery_image = mysql_fetch_assoc ($get_gallery_image);
				} else {
					$gallery_image = array();
				}
				
				$uploaded_image[$i] = array(
																		'name'=>$_FILES['gallery_image']['name'][$i],
																		'type'=>$_FILES['gallery_image']['type'][$i],
																		'tmp_name'=>$_FILES['gallery_image']['tmp_name'][$i],
																		'error'=>$_FILES['gallery_image']['error'][$i],
																		'size'=>$_FILES['gallery_image']['size'][$i]
																		);	
				
				$uploaded_mp3[$i] = array(
																		'name'=>$_FILES['gallery_mp3']['name'][$i],
																		'type'=>$_FILES['gallery_mp3']['type'][$i],
																		'tmp_name'=>$_FILES['gallery_mp3']['tmp_name'][$i],
																		'error'=>$_FILES['gallery_mp3']['error'][$i],
																		'size'=>$_FILES['gallery_mp3']['size'][$i]
																		);
				
				$image_name = resize_content_image($uploaded_image[$i], (int)$_GET['category_id'], $gallery_image['content_image_1']);
				$mp3_name = upload_file($uploaded_mp3[$i], $gallery_image['content_file_1']);
				$image_title = trim($_POST['gallery_image_title'][$i]);
				$image_description = trim($_POST['gallery_image_description'][$i]);
				
				if (empty($image_name) && empty($image_title) && empty($image_description) && !empty($gallery_image)) {
					mysql_query("delete from cms_content where content_id = ".(int)$gallery_image['image_id']." and category_id = ".(int)$_GET['category_id']." limit 1");
				} elseif (!empty($gallery_image)) {
					mysql_query("update cms_content set content_image_1 = '".mysql_real_escape_string($image_name)."',
																														 content_file_1 = '".mysql_real_escape_string($mp3_name)."',
																														 content_title = '".mysql_real_escape_string($image_title)."',
																														 content_title_2 = '".mysql_real_escape_string($image_description)."',
																														 content_order = ".(int)($i+1)."
																														 where content_id = ".(int)$gallery_image['content_id']."
																														 and category_id = ".(int)$_GET['category_id']."
																														 limit 1");
				} elseif (!empty($image_name) || !empty($image_title) || !empty($image_description) || !empty($mp3_name)) {
					mysql_query("insert into cms_content set category_id = ".(int)$_GET['category_id'].",
																														 			content_image_1 = '".mysql_real_escape_string($image_name)."',
																														 			content_file_1 = '".mysql_real_escape_string($mp3_name)."',
																														 			content_title = '".mysql_real_escape_string($image_title)."',
																														 			content_title_2 = '".mysql_real_escape_string($image_description)."',
																																	content_order = ".(int)($i+1)) or die (mysql_error());
				}
			}	
		}
		
		header("Location: index.php?parent_id=".(int)$parent_category['category_id'], true, 301);
		exit();
		
	}
}

$url_thumbnail = get_content_thumbnail_url($_GET['category_id']);
define('URL_THUMBNAIL', ($url_thumbnail === false ? URL_IMAGES_ORIGINAL : $url_thumbnail));

if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
	define('URL_WORKING_FILES', S3_URL . S3_FILES);
} else {
	define('URL_WORKING_FILES', URL_FILES);
}

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1>Update Photos</h1></div>
<?php show_breadcrumb($_GET['category_id']); ?>

<?php show_error(); ?>

<?php //if (count($_error) > 0) { ?>
  <!--div class="error"-->
    <?php //echo implode('<br />', $_error) ?>
  <!--/div-->
<?php //} ?>
  <form action="content_manage_photos.php?category_id=<?php echo $_GET['category_id'] ?>" enctype="multipart/form-data" method="post">
    <table class="table table-bordered table-striped table-hover">
      <tr class="image-row">
        <th colspan="2">Gallery Images</th>
      </tr>
      <tr class="nohover image-row">
      	<td colspan="2" style="padding: 0;">
        	<div id="gallery-images-container">
<?php $gallery_images = array();

	////
	// Get the gallery images
	$get_gallery_images = mysql_query("select * from cms_content where category_id = ".(int)$_GET['category_id']." order by content_order asc limit ".(int)$category_fields['gallery_image_count']);
																																																		
	////
	// Build an array with the gallery images
	while ($gallery_image = mysql_fetch_assoc ($get_gallery_images)) {
		$gallery_images[] = array('image_id' => $gallery_image['content_id'],
															'image_name' => $gallery_image['content_image_1'],
															'mp3_name' => $gallery_image['content_file_1'],
															'image_title' => $gallery_image['content_title'],
															'image_description' => $gallery_image['content_title_2']);
	}
	
	if (count($gallery_images) < $category_fields['gallery_image_count']) {
		$gallery_images = array_pad($gallery_images, $category_fields['gallery_image_count'], 0);
	}
	for($i=0;$i<$category_fields['gallery_image_count'];$i++) { ?>
          	<div id="image_<?php echo $i ?>" class="item<?php echo (!empty($gallery_images[$i])?' sortme':'') ?>">
            	<input type="hidden" name="gallery_image_id[]" value="<?php echo (int)$gallery_images[$i]['image_id'] ?>" />
              <table border="0" cellspacing="0" cellpadding="0" class="gallery-image">
                <tr>
                  <td class="field-title" valign="top"><?php echo $category_fields['gallery_image_text'] ?>:</td>
                  <td><div> Image:<br />
<?php if (!empty($gallery_images[$i]['image_name'])) { ?>
                      <img src="<?php echo URL_THUMBNAIL . $gallery_images[$i]['image_name'] ?>" />
                      <br />
                      <a href="content_manage_photos.php?category_id=<?php echo $_GET['category_id'] ?>&deletegalleryimage=<?php echo $gallery_images[$i]['image_id'] ?>">Delete Image</a><br />
<?php } ?>
                      <input type="file" name="gallery_image[]" />
                    </div>
<?php if (!empty($category_fields['gallery_title_show'])) { ?>
                    <div> <?php echo $category_fields['gallery_title_text'] ?>:<br />
                      <input type="text" name="gallery_image_title[]" value="<?php echo stripslashes($gallery_images[$i]['image_title']) ?>" />
                    </div>
<?php } ?>
<?php if (!empty($category_fields['gallery_description_show'])) { ?>
                    <div> <?php echo $category_fields['gallery_description_text'] ?>:<br />
                      <input type="text" name="gallery_image_description[]" value="<?php echo stripslashes($gallery_images[$i]['image_description']) ?>" />
                    </div>
<?php } ?>
                  </td>
                </tr>
              </table>
            </div>
<?php } ?>
          </div>
          <script type="text/javascript">
          //Position.includeScrollOffsets = true;
          //Sortable.create("gallery-images-container", { elements:$$('#gallery-images-container div.sortme'), scroll: window });
          </script>
        </td>
      </tr>
      <tr class="image-row">
        <td class="field-title">&nbsp;</td>
        <td><button class="btn" type="submit" name="submit">Update Images</button></td>
      </tr>
    </table>
  </form>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?>
*/