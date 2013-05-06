<?php require('_includes/application_top.php');

$type = 'add';
$content = array();

if (!empty($_GET['content_id'])) {
	
	////
	// Get Content
	$get_content = mysql_query("select * from cms_content where content_id = ".(int)$_GET['content_id']." limit 1");
	$content = mysql_fetch_assoc ($get_content);
		
	$type = 'edit';
	
	$_GET['category_id'] = $content['category_id'];
}

if (!empty($_GET['deleteimage']) && !empty($content)) {
	
	if (!empty($content[$_GET['deleteimage']])) {
		
		delete_content_image($content[$_GET['deleteimage']], (int)$_GET['category_id']);
				
		mysql_query("update cms_content set ".$_GET['deleteimage']." = '' where content_id = ".(int)$content['content_id']." limit 1");
		$content[$_GET['deleteimage']] = '';
		
	}
	
}

if (!empty($_GET['deletefile']) && !empty($content)) {
	
	if (!empty($content[$_GET['deletefile']])) {
	
		delete_file($content[$_GET['deletefile']]);
		
		mysql_query("update cms_content set ".$_GET['deletefile']." = '' where content_id = ".(int)$content['content_id']." limit 1");
		$content[$_GET['deletefile']] = '';
	
	}
	
}

if (!empty($_GET['deletegalleryimage'])) {
	
	////
	// Get this gallery image
	$get_gallery_image = mysql_query("select image_name from cms_content_gallery_images where image_id = ".(int)$_GET['deletegalleryimage']." limit 1");
	$gallery_image = mysql_fetch_assoc($get_gallery_image);
	
	if (!empty($gallery_image['image_name'])) {
		
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			$s3 = new S3(S3_KEY, S3_SECRET);
		}
		
		////
		// Get resizes
		$get_resize_sizes = mysql_query("select * from cms_categories_image_sizes where category_id = ".(int)$_GET['category_id']);
		if (mysql_num_rows($get_resize_sizes) > 0) {
			while ($size = mysql_fetch_assoc($get_resize_sizes)) {
				if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
					$s3->deleteObject(S3_BUCKET, S3_IMAGES . $size['image_folder'] . '/' . $gallery_image['image_name']);
				} else {
					@unlink(DIR_IMAGES . $size['image_folder'].'/'.$gallery_image['image_name']);
				}
			}
		}
		if (defined('S3_ACTIVE') && S3_ACTIVE === true) {
			$s3->deleteObject(S3_BUCKET, S3_IMAGES . 'original/' . $gallery_image['image_name']);
		} else {
			@unlink(DIR_IMAGES_ORIGINAL . $gallery_image['image_name']);
		}
		
		mysql_query("delete from cms_content_gallery_images where image_id = ".(int)$_GET['deletegalleryimage']." limit 1");
		
	}
	
}

$get_category_fields = mysql_query("select * from cms_categories_fields where category_id = ".(int)$_GET['category_id']." limit 1");
$category_fields = mysql_fetch_assoc($get_category_fields);

$_error = array();
if (!empty($_POST)) {
	
	if (empty($_POST['content_title'])) {
		$_error[] = 'Please enter "'.stripslashes($category_fields['title_1_text']).'"';
	}
	
	if (count($_error) < 1) {
		
		$content_image_1 = resize_content_image($_FILES['content_image_1'], (int)$_GET['category_id'], $content['content_image_1']);
		$content_image_2 = resize_content_image($_FILES['content_image_2'], (int)$_GET['category_id'], $content['content_image_2']);
		$content_image_3 = resize_content_image($_FILES['content_image_3'], (int)$_GET['category_id'], $content['content_image_3']);
		
		$content_pdf = upload_file($_FILES['content_pdf'], $content['content_pdf']);
		$content_file_1 = upload_file($_FILES['content_file_1'], $content['content_file_1']);
		$content_file_2 = upload_file($_FILES['content_file_2'], $content['content_file_2']);
		
		if ($type == 'add') {
			
			mysql_query("insert into cms_content set category_id = ".(int)$_GET['category_id'].",
																							 content_title = '".mysql_real_escape_string($_POST['content_title'])."',
																							 content_slug = '".clean_url($_POST['content_title'])."',
																							 content_show_title = ".(int)$_POST['content_show_title'].",
																							 content_title_2 = '".mysql_real_escape_string($_POST['content_title_2'])."',
																							 content_title_3 = '".mysql_real_escape_string($_POST['content_title_3'])."',
																							 content_date = ".(int)strtotime($_POST['content_date']).",
																							 content_content_1 = '".mysql_real_escape_string($_POST['content_content_1'])."',
																							 content_content_2 = '".mysql_real_escape_string($_POST['content_content_2'])."',
																							 content_image_1 = '".$content_image_1."',
																							 content_image_2 = '".$content_image_2."',
																							 content_image_3 = '".$content_image_3."',
																							 content_pdf = '".$content_pdf."',
																							 content_file_1 = '".$content_file_1."',
																							 content_file_2 = '".$content_file_2."',
																							 content_f1 = '".mysql_real_escape_string($_POST['content_f1'])."',
																							 content_f2 = '".mysql_real_escape_string($_POST['content_f2'])."',
																							 content_f3 = '".mysql_real_escape_string($_POST['content_f3'])."',
																							 content_active = ".(int)$_POST['content_active']) or die (mysql_error());
			
			$content_id = mysql_insert_id();
			
			$content['content_id'] = $content_id;
			
			mysql_query("update cms_content set content_order = ".(int)$content_id." where content_id = ".(int)$content_id." limit 1");
			
			$_SESSION['messages']['content_manage'][] = 'Content Added Successfully';
			
		} else {
			
			mysql_query("update cms_content set category_id = ".(int)$_GET['category_id'].",
																				  content_title = '".mysql_real_escape_string($_POST['content_title'])."',
																				  content_slug = '".clean_url($_POST['content_title'])."',
																				  content_show_title = ".(int)$_POST['content_show_title'].",
																				  content_title_2 = '".mysql_real_escape_string($_POST['content_title_2'])."',
																				  content_title_3 = '".mysql_real_escape_string($_POST['content_title_3'])."',
																				  content_date = ".(int)strtotime($_POST['content_date']).",
																				  content_content_1 = '".mysql_real_escape_string($_POST['content_content_1'])."',
																				  content_content_2 = '".mysql_real_escape_string($_POST['content_content_2'])."',
																				  content_image_1 = '".$content_image_1."',
																				  content_image_2 = '".$content_image_2."',
																				  content_image_3 = '".$content_image_3."',
																				  content_pdf = '".$content_pdf."',
																				  content_file_1 = '".$content_file_1."',
																				  content_file_2 = '".$content_file_2."',
																				  content_f1 = '".mysql_real_escape_string($_POST['content_f1'])."',
																				  content_f2 = '".mysql_real_escape_string($_POST['content_f2'])."',
																				  content_f3 = '".mysql_real_escape_string($_POST['content_f3'])."',
																				  content_active = ".(int)$_POST['content_active']."
																					where content_id = ".(int)$content['content_id']."
																					limit 1");
			
			$_SESSION['messages']['content_manage'][] = 'Content Updated Successfully';
			
		}
		
		////
		// Gallery Images
		if ($category_fields['gallery_image_count'] > 0) {
			for($i=0;$i<$category_fields['gallery_image_count'];$i++) {
				if (!empty($_POST['gallery_image_id'][$i])) {
					$get_gallery_image = mysql_query("select * from cms_content_gallery_images where image_id = ".(int)$_POST['gallery_image_id'][$i]." limit 1");
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
				
				$image_name = resize_content_image($uploaded_image[$i], (int)$_GET['category_id'], $gallery_image['image_name']);
				$image_title = trim($_POST['gallery_image_title'][$i]);
				$image_description = trim($_POST['gallery_image_description'][$i]);
				
				if (empty($image_name) && empty($image_title) && empty($image_description) && !empty($gallery_image)) {
					mysql_query("delete from cms_content_gallery_images where image_id = ".(int)$gallery_image['image_id']." limit 1");
				} elseif (!empty($gallery_image)) {
					mysql_query("update cms_content_gallery_images set image_name = '".mysql_real_escape_string($image_name)."',
																														 image_title = '".mysql_real_escape_string($image_title)."',
																														 image_description = '".mysql_real_escape_string($image_description)."',
																														 image_order = ".(int)($i+1)."
																														 where image_id = ".(int)$gallery_image['image_id']."
																														 limit 1");
				} elseif (!empty($image_name) || !empty($image_title) || !empty($image_description)) {
					mysql_query("insert into cms_content_gallery_images set content_id = ".(int)$content['content_id'].",
																														 			category_id = ".(int)$_GET['category_id'].",
																														 			image_name = '".mysql_real_escape_string($image_name)."',
																														 			image_title = '".mysql_real_escape_string($image_title)."',
																														 			image_description = '".mysql_real_escape_string($image_description)."',
																																	image_order = ".(int)($i+1)) or die (mysql_error());
				}
			}	
		}
		
		header("Location: content_manage.php?category_id=".(int)$_GET['category_id'], true, 301);
		exit();
		
	}
}

$url_thumbnail = get_content_thumbnail_url($_GET['category_id']);
define('URL_THUMBNAIL', ($url_thumbnail === false ? URL_IMAGES_ORIGINAL : $url_thumbnail));

if ($type == 'add')
	$content['content_active'] = 1;

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1><?php echo $type ?> Content</h1></div>
<?php show_breadcrumb($_GET['category_id']); ?>

<?php show_error(); ?>
<?php //if (count($_error) > 0) { ?>
  <!--div class="error"-->
    <?php //echo implode('<br />', $_error) ?>
  <!--/div-->
<?php //} ?>


  <form action="content_edit.php?category_id=<?php echo $_GET['category_id'] ?>&content_id=<?php echo $content['content_id'] ?>" enctype="multipart/form-data" method="post">
    <table class="table table-bordered table-striped">
      <tr>
        <th class="span2"><?php echo stripslashes($category_fields['title_1_text']) ?>:</td>
        <td>
          <input type="text" name="content_title" value="<?php echo stripslashes(!empty($_POST['content_title'])?$_POST['content_title']:$content['content_title']) ?>" />
<?php if (!empty($category_fields['show_title_show'])) { ?>
          &nbsp;&nbsp;Show Title?&nbsp;<input type="checkbox" name="content_show_title" value="1"<?php echo (!empty($_POST['content_show_title'])?' checked="checked"':(!empty($content['content_show_title'])?' checked="checked"':'')); ?> />
<?php } ?>
        </td>
      </tr>
<?php if (!empty($category_fields['title_2_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['title_2_text']) ?>:</td>
        <td><input type="text" name="content_title_2" value="<?php echo stripslashes(!empty($_POST['content_title_2'])?$_POST['content_title_2']:$content['content_title_2']) ?>" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['title_3_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['title_3_text']) ?>:</td>
        <td><input type="text" name="content_title_3" value="<?php echo stripslashes(!empty($_POST['content_title_3'])?$_POST['content_title_3']:$content['content_title_3']) ?>" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['date_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['date_text']) ?>:</td>
        <td><input type="text" name="content_date" value="<?php echo stripslashes(!empty($_POST['content_date'])?$_POST['content_date']:date("m/d/Y", (!empty($content['content_date'])?$content['content_date']:time()))) ?>" data-date-format="mm/dd/yyyy" class="datepicker" />    
          </td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['content_1_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['content_1_text']) ?>:</td>
        <td><textarea style="width: 810px; height: 200px" name="content_content_1" <?php echo (empty($category_fields['content_1_wysiwyg'])?'':' class="editor"') ?>><?php echo stripslashes(!empty($_POST['content_content_1'])?$_POST['content_content_1']:$content['content_content_1']) ?></textarea>        
        </td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['content_2_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['content_2_text']) ?>:</td>
        <td><textarea style="width: 810px; height: 200px" name="content_content_2" cols="60" rows="20"<?php echo (empty($category_fields['content_2_wysiwyg'])?'':' class="editor"') ?>><?php echo stripslashes(!empty($_POST['content_content_2'])?$_POST['content_content_2']:$content['content_content_2']) ?></textarea></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['image_1_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['image_1_text']) ?>:</td>
        <td>
<?php if (!empty($content['content_image_1'])) { ?>
          <img src="<?php echo URL_THUMBNAIL . $content['content_image_1'] ?>" />
          <br />
          <a href="content_edit.php?category_id=<?php echo $_GET['category_id'] ?>&content_id=<?php echo $content['content_id'] ?>&deleteimage=content_image_1">Delete Image</a><br />
<?php } ?>
          <input type="file" name="content_image_1" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['image_2_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['image_2_text']) ?>:</td>
        <td>
<?php if (!empty($content['content_image_2'])) { ?>
          <img src="<?php echo URL_THUMBNAIL . $content['content_image_2'] ?>" />
          <br />
          <a href="content_edit.php?category_id=<?php echo $_GET['category_id'] ?>&content_id=<?php echo $content['content_id'] ?>&deleteimage=content_image_2">Delete Image</a><br />
<?php } ?>
          <input type="file" name="content_image_2" /></td>
      </tr>
<?php } ?>
    <?php if (!empty($category_fields['image_3_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['image_3_text']) ?>:</td>
        <td>
<?php if (!empty($content['content_image_3'])) { ?>
          <img src="<?php echo URL_THUMBNAIL . $content['content_image_3'] ?>" />
          <br />
          <a href="content_edit.php?category_id=<?php echo $_GET['category_id'] ?>&content_id=<?php echo $content['content_id'] ?>&deleteimage=content_image_3">Delete Image</a><br />
<?php } ?>
          <input type="file" name="content_image_3" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['pdf_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['pdf_text']) ?>:</td>
        <td>
<?php if (!empty($content['content_pdf'])) { ?>
          <a href="<?php echo URL_FILES . $content['content_pdf'] ?>" target="_blank">View PDF</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="content_edit.php?category_id=<?php echo $_GET['category_id'] ?>&content_id=<?php echo $content['content_id'] ?>&deletefile=content_pdf">Delete PDF</a><br />
<?php } ?>
          <input type="file" name="content_pdf" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['file_1_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['file_1_text']) ?>:</td>
        <td>
<?php if (!empty($content['content_file_1'])) { ?>
          <a href="<?php echo URL_FILES . $content['content_file_1'] ?>" target="_blank">View <?php echo stripslashes($category_fields['file_1_text']) ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="content_edit.php?category_id=<?php echo $_GET['category_id'] ?>&content_id=<?php echo $content['content_id'] ?>&deletefile=content_file_1">Delete <?php echo stripslashes($category_fields['file_1_text']) ?></a><br />
<?php } ?>
          <input type="file" name="content_file_1" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['file_2_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['file_2_text']) ?>:</td>
        <td>
<?php if (!empty($content['content_file_2'])) { ?>
          <a href="<?php echo URL_FILES . $content['content_file_2'] ?>" target="_blank">View <?php echo stripslashes($category_fields['file_2_text']) ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="content_edit.php?category_id=<?php echo $_GET['category_id'] ?>&content_id=<?php echo $content['content_id'] ?>&deletefile=content_file_2">Delete <?php echo stripslashes($category_fields['file_2_text']) ?></a><br />
<?php } ?>
          <input type="file" name="content_file_2" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['f1_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['f1_text']) ?>:</td>
        <td><input type="text" name="content_f1" value="<?php echo stripslashes(!empty($_POST['content_f1'])?$_POST['content_f1']:$content['content_f1']) ?>" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['f2_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['f2_text']) ?>:</td>
        <td><input type="text" name="content_f2" value="<?php echo stripslashes(!empty($_POST['content_f2'])?$_POST['content_f2']:$content['content_f2']) ?>" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['f3_show'])) { ?>
      <tr>
        <th><?php echo stripslashes($category_fields['f3_text']) ?>:</td>
        <td><input type="text" name="content_f3" value="<?php echo stripslashes(!empty($_POST['content_f3'])?$_POST['content_f3']:$content['content_f3']) ?>" /></td>
      </tr>
<?php } ?>
<?php if (!empty($category_fields['active_show'])) { ?>
      <tr>
        <th>Active:</td>
        <td><select name="content_active">
            <option value="1">Yes</option>
            <option value="0"<?php echo (empty($content['content_active'])?' selected="selected"':'') ?>>No</option>
          </select></td>
      </tr>
<?php } ?>
      <tr>
        <th>&nbsp;</td>
        <td><button class="btn" type="submit" name="submit" ><?php echo ($type=='add'?'Add':'Update') ?> Content</button></td>
      </tr>
<?php if ($category_fields['gallery_image_count'] > 0) { ?>
      <tr>
        <th colspan="2">Gallery Images</th>
      </tr>
      <tr class="nohover">
      	<td colspan="2" style="padding: 0;">
        	<div id="gallery-images-container">
<?php $gallery_images = array();

	////
	// Get the gallery images
	$get_gallery_images = mysql_query("select * from cms_content_gallery_images where content_id = ".(int)$content['content_id']." order by image_order asc limit ".(int)$category_fields['gallery_image_count']);
																																																		
	////
	// Build an array with the gallery images
	while ($gallery_image = mysql_fetch_assoc ($get_gallery_images)) {
		$gallery_images[] = array('image_id' => $gallery_image['image_id'],
															'image_name' => $gallery_image['image_name'],
															'image_title' => $gallery_image['image_title'],
															'image_description' => $gallery_image['image_description']);
	}
	
	if (count($gallery_images) < $category_fields['gallery_image_count']) {
		$gallery_images = array_pad($gallery_images, $category_fields['gallery_image_count'], 0);
	}
	for($i=0;$i<$category_fields['gallery_image_count'];$i++) { ?>
          	<div id="image_<?php echo $i ?>" class="item<?php echo (!empty($gallery_images[$i])?' sortme':'') ?>">
            	<input type="hidden" name="gallery_image_id[]" value="<?php echo (int)$gallery_images[$i]['image_id'] ?>" />
              <table class="table table-bordered table-hover">
                <tr>
                  <th class="span1" valign="top"><?php echo $category_fields['gallery_image_text'] ?>:</td>
                  <td><div> Image:<br />
<?php if (!empty($gallery_images[$i]['image_name'])) { ?>
                      <img src="<?php echo URL_THUMBNAIL . $gallery_images[$i]['image_name'] ?>" />
                      <br />
                      <a href="content_edit.php?category_id=<?php echo $_GET['category_id'] ?>&content_id=<?php echo $content['content_id'] ?>&deletegalleryimage=<?php echo $gallery_images[$i]['image_id'] ?>">Delete Image</a><br />
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
      <tr>
        <th>&nbsp;</td>
        <td><button class="btn" type="submit" name="submit"><?php echo ($type=='add'?'Add':'Update') ?> Content</button></td>
      </tr>
<?php } ?>
    </table>
  </form>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?>
*/