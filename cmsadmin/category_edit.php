<?php require('_includes/application_top.php');

$type = 'add';
$category = array();

if (!empty($_GET['category_id'])) {
	
	////
	// Get Category
	$get_category = mysql_query("select * from cms_categories where category_id = ".(int)$_GET['category_id']." limit 1");
	$category = mysql_fetch_assoc ($get_category);
		
	////
	// Get Parent Category
	$get_parent_category = mysql_query("select * from cms_categories where category_id = ".(int)$category['category_parent_id']." limit 1");
	$parent_category = mysql_fetch_assoc ($get_parent_category);
		
	$type = 'edit';
	
} elseif (!empty($_GET['parent_id'])) {
	
	////
	// Get Parent Category
	$get_parent_category = mysql_query("select * from cms_categories where category_id = ".(int)$_GET['parent_id']." limit 1");
	$parent_category = mysql_fetch_assoc ($get_parent_category);
	
}

$get_granparent_category = mysql_query("select * from cms_categories where category_id = ".(int)$parent_category['category_parent_id']." limit 1");
$granparent_category = mysql_fetch_assoc($get_granparent_category);

if (!empty($_GET['deleteimage']) && !empty($category)) {
	
	if (!empty($category[$_GET['deleteimage']])) {
		
		delete_content_image($category[$_GET['deleteimage']], $category['category_id']);
				
		mysql_query("update cms_categories set ".$_GET['deleteimage']." = '' where category_id = ".(int)$category['category_id']." limit 1");
		$category[$_GET['deleteimage']] = '';
		
	}
	
}

$_error = array();
if (!empty($_POST)) {
	
	if (empty($_POST['category_name'])) {
		$_error[] = 'Please enter a Category Name';
	}
	
	if (count($_error) < 1) {
		
		if ($type == 'add') {
			
			mysql_query("insert into cms_categories set category_parent_id = ".(int)$parent_category['category_id'].",
																									category_name = '".mysql_real_escape_string($_POST['category_name'])."',
																									category_slug = '".clean_url($_POST['category_name'])."',
																									category_meta_title = '".mysql_real_escape_string($_POST['category_meta_title'])."',
																									category_meta_description = '".mysql_real_escape_string($_POST['category_meta_description'])."',
																									category_active = ".(int)$_POST['category_active'].",
																									category_allow_delete = ".(int)$parent_category['category_allow_subcat_delete'].",
																									category_allow_content = ".(int)$parent_category['category_allow_subcat_content'].",
																									category_allow_subcats = 0,
																									category_show_subcats = 0,
																									category_allow_subcat_content = 0,
																									category_allow_subcat_delete = ".(int)$parent_category['category_allow_subcat_delete'].",
																									category_keep_original_images = ".(int)$parent_category['category_keep_original_images']);

			$category_id = mysql_insert_id();

			mysql_query("update cms_categories set category_order = ".$category_id." where category_id = ".$category_id." limit 1");
			
			if (empty($parent_category)) {
				
				mysql_query("update cms_categories set category_allow_delete = 1,
																							 category_allow_content = 1,
																							 category_allow_subcats = 1,
																							 category_show_subcats = 1,
																							 category_allow_subcat_content = 1,
																							 category_keep_original_images = 1
																							 where category_id = ".$category_id." limit 1");
				
			}
			
			if (!empty($parent_category)) {
				
				////
				// Get the column names
				$get_column_names = mysql_query("select * from cms_categories_fields limit 1");
				
				$insert_string = array();
				$column_names = mysql_fetch_assoc($get_column_names);
				foreach($column_names as $name=>$value) {
					if ($name != 'fields_id')
						$insert_string[] = $name;
				}
				
				mysql_query("insert into cms_categories_fields (".implode(', ', $insert_string).") (select ".implode(', ', $insert_string)." from cms_categories_fields where category_id = ".$parent_category['category_id']." limit 1)") or die (mysql_error());
				$fields_id = mysql_insert_id();
				mysql_query("update cms_categories_fields set category_id = ".(int)$category_id." where fields_id = ".(int)$fields_id." limit 1") or die (mysql_error());
				
				////
				// Get parent image sizes
				$get_parent_image_sizes = mysql_query("select * from cms_categories_image_sizes where category_id = ".(int)$parent_category['category_id']);
				while ($parent_image_size = mysql_fetch_assoc ($get_parent_image_sizes)) {
					mysql_query("insert into cms_categories_image_sizes set category_id = ".(int)$category_id.", image_width = ".(int)$parent_image_size['image_width'].", image_height = ".(int)$parent_image_size['image_height'].", image_crop = ".(int)$parent_image_size['image_crop'].", image_folder = '".mysql_real_escape_string($parent_image_size['image_folder'])."', image_thumbnail = ".(int)$parent_image_size['image_thumbnail']);
				}
				
			} else {
				mysql_query("insert into cms_categories_fields set category_id = ".(int)$category_id) or die (mysql_error());
			}
			
		} else {
			
			mysql_query("update cms_categories set category_name = '".mysql_real_escape_string($_POST['category_name'])."',
																						 category_slug = '".clean_url($_POST['category_name'])."',
																						 category_meta_title = '".mysql_real_escape_string($_POST['category_meta_title'])."',
																						 category_meta_description = '".mysql_real_escape_string($_POST['category_meta_description'])."',
																						 category_active = ".(int)$_POST['category_active']."
																						 where category_id = ".$category['category_id']."
																						 limit 1") or die (mysql_error());

		}
		
		$_SESSION['messages']['index'][] = 'Category successfully '.($type=='edit'?'Updated':'Added');
		
		header("Location: index.php".(!empty($parent_category)?'?parent_id='.$parent_category['category_id']:''), true, 301);
		exit();
		
	}
	
}

if ($type == 'add')
	$category['category_active'] = 1;

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1><?php echo $type ?> Category</h1>
  </div>
<?php show_breadcrumb(!empty($category['category_parent_id'])?$category['category_parent_id']:$parent_category['category_id']); ?>

<?php show_error(); ?>

<?php //if (count($_error) > 0) { ?>
  <!--div class="alert alert-error"-->
    <?php //echo implode('<br />', $_error) ?>
  <!--/div-->
<?php //} ?>
  <form action="category_edit.php?category_id=<?php echo $_GET['category_id'] ?>&parent_id=<?php echo $parent_category['category_id'] ?>" enctype="multipart/form-data" method="post">
    <table class="table table-bordered table-striped table-condensed">
      <tr>
        <th class="span2">Name:</td>
        <td><input type="text" name="category_name" value="<?php echo stripslashes(!empty($_POST['category_name'])?$_POST['category_name']:$category['category_name']) ?>" /></td>
      </tr>
      <tr>
        <th>Meta Title:</td>
        <td><input type="text" name="category_meta_title" value="<?php echo stripslashes(!empty($_POST['category_meta_title'])?$_POST['category_meta_title']:$category['category_meta_title']) ?>" size="60" /></td>
      </tr>
      <tr>
        <th>Meta Description:</td>
        <td><input type="text" name="category_meta_description" value="<?php echo stripslashes(!empty($_POST['category_meta_description'])?$_POST['category_meta_description']:$category['category_meta_description']) ?>" size="60" /></td>
      </tr>
      <tr>
        <th>Active:</td>
        <td><select name="category_active">
            <option value="1">Yes</option>
            <option value="0"<?php echo (empty($category['category_active'])?' selected="selected"':'') ?>>No</option>
          </select></td>
      </tr>
      <tr>
        <th>&nbsp;</td>
        <td><button class="btn" type="submit" name="submit" ><?php echo ($type=='add'?'Add':'Update') ?> Category</button></td>
      </tr>
    </table>
  </form>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?>
*/