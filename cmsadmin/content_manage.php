<?php require('_includes/application_top.php');

if (empty($_GET['category_id'])) {
	header("Location: index.php", true, 301);
	exit();
}

////
// Get the parent category info
$get_category = mysql_query("select * from cms_categories where category_id = ".(int)$_GET['category_id']." limit 1");
$category = mysql_fetch_assoc($get_category);

if (empty($category['category_allow_content'])) {
	header("Location: index.php?parent_id=".$category['category_parent_id'], true, 301);
	exit();
}

if (!empty($_GET['delete'])) {
	
	$get_delete_content = mysql_query("select content_id, content_image_1, content_image_2, content_image_3, content_pdf, content_file_1, content_file_2 from cms_content where content_id = ".(int)$_GET['delete']." limit 1");
	$delete_content = mysql_fetch_assoc ($get_delete_content);
	
	delete_content_image($delete_content['content_image_1'], $category['category_id']);
	delete_content_image($delete_content['content_image_2'], $category['category_id']);
	delete_content_image($delete_content['content_image_3'], $category['category_id']);
	
	delete_file($delete_content['content_pdf']);
	delete_file($delete_content['content_file_1']);
	delete_file($delete_content['content_file_2']);
	
	$get_content_images = mysql_query("select image_name from cms_content_gallery_images where content_id = ".(int)$delete_content['content_id']);
	while ($content_image = mysql_fetch_assoc($get_content_images)) {
		delete_content_image($content_image['image_name'], $category['category_id']);
	}
	
	mysql_query("delete from cms_content_gallery_images where content_id = ".(int)$delete_content['content_id']);
	mysql_query("delete from cms_content where content_id = ".(int)$delete_content['content_id']." limit 1");
	
	$_SESSION['messages']['content_manage'][] = 'Content Deleted Successfully';
	
	header("Location: content_manage.php?category_id=".$_GET['category_id'], true, 301);
	exit();
	
}

if (!empty($_GET['new_position'])) {
	
	$get_new_position = mysql_query("select content_id, content_order from cms_content where content_id = ".(int)$_GET['new_position']." limit 1");
	$new_position = mysql_fetch_assoc($get_new_position);
	
	$get_current = mysql_query("select content_id, content_order from cms_content where content_id = ".(int)$_GET['content_id']." limit 1");
	$current = mysql_fetch_assoc($get_current);
	
	if (!empty($current['content_id']) && !empty($new_position['content_id'])) {
		
		mysql_query("update cms_content set content_order = ".(int)$new_position['content_order']." where content_id = ".(int)$current['content_id']." limit 1");
		mysql_query("update cms_content set content_order = ".(int)$current['content_order']." where content_id = ".(int)$new_position['content_id']." limit 1") or die (mysql_error());
		
	}
	
}

$get_category_fields = mysql_query("select title_1_text, active_show from cms_categories_fields where category_id = ".(int)$category['category_id']." limit 1");
$category_fields = mysql_fetch_assoc($get_category_fields);

////
// The name for the Messages Session Array
$_message_name = 'content_manage';

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1>Content Management</h1></div>
<?php show_breadcrumb($category['category_id']); ?>
  <div style="margin: 0 0 20px;"><i class="icon-plus"></i><a href="content_edit.php?category_id=<?php echo $category['category_id'] ?>">Add Content</a></div>
  <table class="table table-bordered table-striped">
    <tr>
      <th><?php echo stripslashes($category_fields['title_1_text']) ?></th>
<?php if (!empty($category_fields['active_show'])) { ?>
      <th class="span1">Active?</th>
<?php } ?>
      <th class="span1">Edit</th>
      <th class="span1">Delete</th>
      <th class="span1">Order</th>
    </tr>
<?php $get_content = mysql_query("select content_id, content_title, content_active, content_order from cms_content where category_id = ".(int)$category['category_id']." order by content_order desc");
while ($content = mysql_fetch_assoc ($get_content)) {
	
	$previous = mysql_fetch_assoc (mysql_query("select content_id from cms_content where content_order > ".(int)$content['content_order']." and category_id = ".(int)$category['category_id']." order by content_order asc limit 1"));
	$next = mysql_fetch_assoc (mysql_query("select content_id from cms_content where content_order < ".(int)$content['content_order']." and category_id = ".(int)$category['category_id']." order by content_order desc limit 1")); ?>
    <tr>
      <td class="content-name"><a href="content_edit.php?content_id=<?php echo $content['content_id'] ?>&category_id=<?php echo $category['category_id'] ?>"><?php echo stripslashes($content['content_title']) ?></a></td>
<?php if (!empty($category_fields['active_show'])) { ?>
      <td><?php echo (!empty($content['content_active'])?'<i class="icon-ok"></i>':'<i class="icon-remove">') ?></td>
<?php } ?>
      <td><a href="content_edit.php?content_id=<?php echo $content['content_id']?>"><i class="icon-pencil"></i></a></td>
      <td><a href="content_manage.php?category_id=<?php echo $category['category_id'] ?>&delete=<?php echo $content['content_id']?>" onclick="return confirm('Are you sure you want to delete this Content?');"><i class="icon-trash"></i></a></td>
      <td><a href="content_manage.php?category_id=<?php echo $category['category_id'] ?>&content_id=<?php echo $content['content_id']?>&new_position=<?php echo $previous['content_id'] ?>"><i class="icon-chevron-up"></i></a>&nbsp;<a href="content_manage.php?category_id=<?php echo $category['category_id'] ?>&content_id=<?php echo $content['content_id']?>&new_position=<?php echo $next['content_id'] ?>"><i class="icon-chevron-down"></i></a></td>
    </tr>
<?php }
if (mysql_num_rows($get_content) < 1) { ?>
    <tr>
      <td colspan="5">There is no content in this category.</td>
    </tr>
<?php } ?>
  </table>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?> */