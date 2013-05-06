<?php require('_includes/application_top.php');

if (!empty($_POST)) {
	
	if (!empty($_POST['parent_category']) && count($_POST['category']) > 0) {
		
		$change_cats = $_POST['category'];
		
		$get_parent_cat = mysql_query("select * from cms_categories where category_id = ".(int)$_POST['parent_category']." limit 1");
		$parent_cat = mysql_fetch_assoc($get_parent_cat);
		
		$get_parent_info = mysql_query("select * from cms_categories_fields where category_id = ".(int)$_POST['parent_category']." limit 1");
		$parent_info = mysql_fetch_array($get_parent_info, MYSQL_NUM);
		
		$get_parent_resizes = mysql_query("select * from cms_categories_image_sizes where category_id = ".(int)$_POST['parent_category']);
		$count_parent_resizes = mysql_num_rows($get_parent_resizes);
		
		for($i=0,$n=count($change_cats);$i<$n;$i++) {
			
			if ($change_cats[$i] != $_POST['parent_category']) {
			
				mysql_query("delete from cms_categories_fields where category_id = ".(int)$change_cats[$i]." limit 1");
				
				$current_info = $parent_info;
				$current_info[1] = $change_cats[$i];
				
				$values = array();
				$values[] = "null";
				for($ii=1,$nn=count($current_info);$ii<$nn;$ii++) {
					$values[] = "'".$current_info[$ii]."'";
				}
				
				mysql_query("update cms_categories set category_allow_delete = ".(int)$parent_cat['category_allow_delete'].",
																							 category_allow_content = ".(int)$parent_cat['category_allow_content'].",
																							 category_allow_subcats = ".(int)$parent_cat['category_allow_subcats'].",
																							 category_show_subcats = ".(int)$parent_cat['category_show_subcats'].",
																							 category_allow_subcat_content = ".(int)$parent_cat['category_allow_subcat_content'].",
																							 category_allow_subcat_delete = ".(int)$parent_cat['category_allow_subcat_delete'].",
																							 category_keep_original_images = ".(int)$parent_cat['category_keep_original_images']."
																							 where category_id = ".(int)$change_cats[$i]."
																							 limit 1") or die (mysql_error());
				
				mysql_query("insert into cms_categories_fields values(".implode(', ', $values).")") or die ("insert into cms_categories_fields values(".implode(', ', $values).")<br />".mysql_error());
				
				mysql_query("delete from cms_categories_image_sizes where category_id = ".(int)$change_cats[$i]);
				if ($count_parent_resizes > 0) {
					mysql_data_seek($get_parent_resizes, 0);
					while ($parent_resize = mysql_fetch_assoc($get_parent_resizes)) {
						mysql_query("insert into cms_categories_image_sizes values (".(int)$change_cats[$i].", ".$parent_resize['image_width'].", ".$parent_resize['image_height'].", ".$parent_resize['image_crop'].", '".$parent_resize['image_folder']."', ".$parent_resize['image_thumbnail'].", '".$parent_resize['image_custom_imagemagick']."')") or die(mysql_error());
					}
				}
				
			}
			
		}
		
		$_SESSION['messages']['config_multichange'][] = 'Categories Successfully Updated';
		header("Location: config_multichange.php", true, 301);
		exit();
		
	}
	
}

$_message_name = 'config_multichange';

require(DIR_ADMININCLUDES . 'header.php'); ?>
	<div class="page-header"><h1>Category Config Management</h1></div>
  <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return confirm('Are you sure?\n\nThis will overwrite any config in the change categories.');">
    <button class="btn" type="submit" name="change">Change</button><br /><br />
  <table class="table table-bordered table-striped table-hover">
    <tr>
      <th class="span1">Change</th>
      <th>Category Name</th>
      <th class="span1">Parent</th>
    </tr>
<?php $get_categories = mysql_query("select category_id, category_name, category_active, category_allow_delete, category_show_subcats, category_allow_subcats, category_allow_content from cms_categories where category_parent_id = ".(int)$_GET['parent_id']." order by category_order asc");
while ($category = mysql_fetch_assoc ($get_categories)) {
	
	$get_subcategories = mysql_query("select category_id, category_name, category_active, category_allow_delete, category_show_subcats, category_allow_subcats, category_allow_content from cms_categories where category_parent_id = ".(int)$category['category_id']." order by category_order asc"); ?>
    <tr>
      <td><input type="checkbox" name="category[]" value="<?php echo $category['category_id'] ?>" /></td>
      <td class="category-name"><strong><?php echo stripslashes($category['category_name']) ?></strong></td>
      <td><input type="radio" name="parent_category" value="<?php echo $category['category_id'] ?>" /></td>
    </tr>
<?php while ($sub_category = mysql_fetch_assoc ($get_subcategories)) {
		
		$get_subsubcategories = mysql_query("select category_id, category_name, category_active, category_allow_delete, category_show_subcats, category_allow_subcats, category_allow_content from cms_categories where category_parent_id = ".(int)$sub_category['category_id']." order by category_order asc"); ?>
    <tr class="sub-category">
      <td><input type="checkbox" name="category[]" value="<?php echo $sub_category['category_id'] ?>" /></td>
      <td class="category-name"><i class="icon-chevron-right"></i><?php echo stripslashes($sub_category['category_name']) ?></td>
      <td><input type="radio" name="parent_category" value="<?php echo $sub_category['category_id'] ?>" /></td>
    </tr>
<?php while ($subsub_category = mysql_fetch_assoc ($get_subsubcategories)) {
			
			$get_subsubsubcategories = mysql_query("select category_id, category_name, category_active, category_allow_delete, category_show_subcats, category_allow_subcats, category_allow_content from cms_categories where category_parent_id = ".(int)$subsub_category['category_id']." order by category_order asc"); ?>
    <tr class="sub-category">
      <td><input type="checkbox" name="category[]" value="<?php echo $subsub_category['category_id'] ?>" /></td>
      <td class="category-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;<?php echo stripslashes($subsub_category['category_name']) ?></td>
      <td><input type="radio" name="parent_category" value="<?php echo $subsub_category['category_id'] ?>" /></td>
    </tr>
<?php while ($subsubsub_category = mysql_fetch_assoc ($get_subsubsubcategories)) { ?>
    <tr class="sub-category">
      <td><input type="checkbox" name="category[]" value="<?php echo $subsubsub_category['category_id'] ?>" /></td>
      <td class="category-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;<?php echo stripslashes($subsubsub_category['category_name']) ?></td>
      <td><input type="radio" name="parent_category" value="<?php echo $subsubsub_category['category_id'] ?>" /></td>
    </tr>
<?php } ?>
<?php }
	}
} ?>
  </table>
  </form>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?> */