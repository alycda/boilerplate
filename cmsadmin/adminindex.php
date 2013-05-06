<?php require('_includes/application_top.php');

$_message_name = 'admin_index';

require(DIR_ADMININCLUDES . 'header.php'); ?>
	<div class="page-header"><h1>Content Admin Management</h1></div>
  <table class="table table-bordered table-striped table-hover">
    <tr>
      <th>Category Name</th>
      <th>Edit</th>
    </tr>
<?php $get_categories = mysql_query("select category_id, category_name, category_active, category_allow_delete, category_show_subcats, category_allow_subcats, category_allow_content from cms_categories where category_parent_id = ".(int)$_GET['parent_id']." order by category_order asc");
while ($category = mysql_fetch_assoc ($get_categories)) {
	
	$get_subcategories = mysql_query("select category_id, category_name, category_active, category_allow_delete, category_show_subcats, category_allow_subcats, category_allow_content from cms_categories where category_parent_id = ".(int)$category['category_id']." order by category_order asc"); ?>
    <tr>
      <td><a href="category_edit_config.php?category_id=<?php echo $category['category_id'] ?>"><?php echo stripslashes($category['category_name']) ?></a></td>
      <td class="span1"><a href="category_edit_config.php?category_id=<?php echo $category['category_id'] ?>"><i class="icon-pencil"></i></a></td>
    </tr>
<?php while ($sub_category = mysql_fetch_assoc ($get_subcategories)) {
		
		$get_subsubcategories = mysql_query("select category_id, category_name, category_active, category_allow_delete, category_show_subcats, category_allow_subcats, category_allow_content from cms_categories where category_parent_id = ".(int)$sub_category['category_id']." order by category_order asc"); ?>
    <tr class="sub-category">
      <td class="category-name"><i class="icon-chevron-right"></i><a href="category_edit_config.php?category_id=<?php echo $sub_category['category_id'] ?>"><?php echo stripslashes($sub_category['category_name']) ?></a></td>
      <td><a href="category_edit_config.php?category_id=<?php echo $sub_category['category_id'] ?>"><i class="icon-pencil"></i></a></td>
    </tr>
<?php while ($subsub_category = mysql_fetch_assoc ($get_subsubcategories)) { ?>
    <tr class="sub-category">
      <td class="category-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;<a href="category_edit_config.php?category_id=<?php echo $subsub_category['category_id'] ?>"><?php echo stripslashes($subsub_category['category_name']) ?></a></td>
      <td><a href="category_edit_config.php?category_id=<?php echo $subsub_category['category_id'] ?>"><i class="icon-pencil"></i></a></td>
    </tr>
<?php }
	}
} ?>
  </table>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?> */