<?php require('_includes/application_top.php');

if (empty($_GET['parent_id'])) $_GET['parent_id'] = 0;
$_GET['parent_id'] = (int)$_GET['parent_id'];

////
// Get the parent category info
$get_parent_category = mysql_query("select * from cms_categories where category_id = ".(int)$_GET['parent_id']." limit 1");
$parent_category = mysql_fetch_assoc($get_parent_category);

if (!empty($parent_category['category_show_subcats'])) {
	header("Location: index.php?parent_id=".$parent_category['category_parent_id'], true, 301);
	exit();
}

if ($_GET['delete']) {
	delete_category($_GET['delete']);
}

////
// The name for the Messages Session Array
$_message_name = 'index';

require(DIR_ADMININCLUDES . 'header.php'); ?>

		<div class="page-header">
          <h1>Content Management</h1>
        </div>

		<?php show_breadcrumb($_GET['parent_id']); ?>
<div>

<?php //if (!empty($parent_category) && !empty($parent_category['category_allow_subcats'])) { ?>
  <div style="margin: 0 0 20px;"><i class="icon-plus"></i><a href="category_edit.php?parent_id=<?php echo $_GET['parent_id'] ?>">Add Category</a></div>
<?php //} ?>
  <table class="table table-bordered table-striped table-hover">
    <tr>
      <th>Category Name</th>
      <th class="span1">Active?</th>
      <th class="span1">Content</th>
      <th class="span1">Edit</th>
      <th class="span1">Delete</th>
    </tr>
<?php $get_categories = mysql_query("select * from cms_categories where category_parent_id = ".(int)$_GET['parent_id']." order by category_order asc");
while ($category = mysql_fetch_assoc ($get_categories)) {
	
	$get_subcategories = mysql_query("select * from cms_categories where category_parent_id = ".(int)$category['category_id']." order by category_order asc");
	$has_subcategories = mysql_num_rows($get_subcategories);
	if ($has_subcategories < 1) { ?>
    <tr>
<?php if (!empty($category['category_allow_content'])) { ?>
      <td class="category-name"><a href="content_manage.php?category_id=<?php echo $category['category_id'] ?>"><?php echo stripslashes($category['category_name']) ?></a><?php echo (!empty($category['category_allow_subcats'])?'<span class="add-sub"><a href="category_edit.php?parent_id='.$category['category_id'].'"> <i class="icon-plus"></i></a></span>':'') ?></td>
<?php } else { ?>
      <td class="category-name"><?php echo stripslashes($category['category_name']) ?><?php echo (!empty($category['category_allow_subcats'])?'<span class="add-sub"><a href="category_edit.php?parent_id='.$category['category_id'].'"><i class="icon-plus"></i></a></span>':'') ?></td>
<?php } ?>
      <td><?php echo (!empty($category['category_active'])?'<i class="icon-ok"></i>':'<i class="icon-remove">') ?></td>
<?php if (!empty($category['category_allow_content'])) { ?>
      <td><a href="content_manage.php?category_id=<?php echo $category['category_id'] ?>"><i class="icon-folder-open"></i></a></td>
<?php } else { ?>
      <td>&nbsp;</td>
<?php } ?>
      <td><a href="category_edit.php?category_id=<?php echo $category['category_id'] ?>"><i class="icon-pencil"></i></a></td>
<?php if (!empty($parent_category['category_allow_subcat_delete']) || (empty($parent_category) && !empty($category['category_allow_delete']))) { ?>
      <td><a href="index.php?parent_id=<?php echo $_GET['parent_id'] ?>&delete=<?php echo $category['category_id']?>" onclick="return confirm('Are you sure you want to delete this Category?');">
      <i class="icon-trash"></i><!--i class="icon-fire"></i--></a></td>
<?php } else { ?>
      <td>&nbsp;</td>
<?php } ?>
    </tr>
<?php } elseif (empty($category['category_show_subcats'])) { ?>
    <tr>
      <td class="category-name"><a href="index.php?parent_id=<?php echo $category['category_id'] ?>"><?php echo stripslashes($category['category_name']) ?></a><?php echo (!empty($category['category_allow_subcats'])?'<span class="add-sub"><a href="category_edit.php?parent_id='.$category['category_id'].'"><i class="icon-plus"></i></a></span>':'') ?></td>
      <td><?php echo (!empty($category['category_active'])?'<i class="icon-ok"></i>':'<i class="icon-remove">') ?></td>
<?php if (!empty($category['category_allow_content'])) { ?>
      <td><a href="content_manage.php?category_id=<?php echo $category['category_id'] ?>"><i class="icon-folder-open"></i></a></td>
<?php } else { ?>
      <td>&nbsp;</td>
<?php } ?>
      <td><a href="category_edit.php?category_id=<?php echo $category['category_id'] ?>"><i class="icon-pencil"></i></a></td>
<?php if (!empty($parent_category['category_allow_subcat_delete']) || (empty($parent_category) && !empty($category['category_allow_delete']))) { ?>
      <td><a href="index.php?parent_id=<?php echo $_GET['parent_id'] ?>&delete=<?php echo $category['category_id']?>" onclick="return confirm('Are you sure you want to delete this Category?');"><i class="icon-trash"></i></a></td>
<?php } else { ?>
      <td>&nbsp;</td>
<?php } ?>
    </tr>
<?php } else { ?>
    <tr class="parent-category">
      <td class="category-name"><?php echo stripslashes($category['category_name']) ?><?php echo (!empty($category['category_allow_subcats'])?'<span class="add-sub"><a href="category_edit.php?parent_id='.$category['category_id'].'"><i class="icon-plus"></i></a></span>':'') ?></td>
      <td><?php echo (!empty($category['category_active'])?'<i class="icon-ok"></i>':'<i class="icon-remove">') ?></td>
<?php if (!empty($category['category_allow_content'])) { ?>
      <td><a href="content_manage.php?category_id=<?php echo $category['category_id'] ?>"><i class="icon-folder-open"></i></a></td>
<?php } else { ?>
      <td>&nbsp;</td>
<?php } ?>
      <td><a href="category_edit.php?category_id=<?php echo $category['category_id'] ?>"><i class="icon-pencil"></i></a></td>
<?php if (!empty($parent_category['category_allow_subcat_delete']) || (empty($parent_category) && !empty($category['category_allow_delete']))) { ?>
      <td><a href="index.php?parent_id=<?php echo $_GET['parent_id'] ?>&delete=<?php echo $category['category_id']?>" onclick="return confirm('Are you sure you want to delete this Category?');"><i class="icon-trash"></i></a></td>
<?php } else { ?>
      <td>&nbsp;</td>
<?php } ?>
    </tr>
<?php if (!empty($category['category_show_subcats'])) {
			while ($sub_category = mysql_fetch_assoc ($get_subcategories)) {
				
				$check_subsubcategories = 0;
				if (!empty($sub_category['category_allow_subcats'])) {
					$check_subsubcategories = mysql_num_rows(mysql_query("select category_id from cms_categories where category_parent_id = ".(int)$sub_category['category_id']." limit 1"));
				} ?>
    <tr class="sub-category">
<?php if (!empty($sub_category['category_allow_content'])) { ?>
      <td class="category-name"><i class="icon-chevron-right"></i><a href="<?php echo (!empty($sub_category['category_allow_subcats']) || $check_subsubcategories > 0?'index.php?parent_id':'content_manage.php?category_id') ?>=<?php echo $sub_category['category_id'] ?>"><?php echo stripslashes($sub_category['category_name']) ?></a><?php echo (!empty($sub_category['category_allow_subcats'])?'<span class="add-sub"><a href="category_edit.php?parent_id='.$sub_category['category_id'].'"><i class="icon-plus"></i></a></span>':'') ?></td>
<?php } else { ?>
      <td class="category-name"><i class="icon-chevron-right"></i><?php echo (!empty($sub_category['category_allow_subcats']) || $check_subsubcategories > 0?'<a href="index.php?parent_id='.$sub_category['category_id'].'">':'') . stripslashes($sub_category['category_name']) . (!empty($sub_category['category_allow_subcats']) || $check_subsubcategories > 0?'</a>':'') . (!empty($sub_category['category_allow_subcats'])?'<span class="add-sub"><a href="category_edit.php?parent_id='.$sub_category['category_id'].'"><i class="icon-plus"></i></a></span>':'') ?></td>
<?php } ?>
      <td><?php echo (!empty($sub_category['category_active'])?'<i class="icon-ok"></i>':'<i class="icon-remove">') ?></td>
<?php if (!empty($sub_category['category_allow_content'])) { ?>
      <td><a href="content_manage.php?category_id=<?php echo $sub_category['category_id'] ?>"><i class="icon-folder-open"></i></a></td>
<?php } else { ?>
      <td>&nbsp;</td>
<?php } ?>
      <td><a href="category_edit.php?category_id=<?php echo $sub_category['category_id'] ?>"><i class="icon-pencil"></i></a></td>
<?php		if ($category['category_allow_subcat_delete']) { ?>
      <td><a href="index.php?parent_id=<?php echo $_GET['parent_id'] ?>&delete=<?php echo $sub_category['category_id']?>" onclick="return confirm('Are you sure you want to delete this Category?');"><i class="icon-trash"></i></a></td>
<?php 		} else { ?>
      <td>&nbsp;</td>
<?php		} ?>
    </tr>
<?php		}
		}
	}
}
if (mysql_num_rows($get_categories) < 1) { ?>
    <tr>
      <td colspan="5" class="category-name">There are no categories.<br /><a href="category_edit.php?parent_id=<?php echo $_GET['parent_id'] ?>">Click here to add one.</a></td>
    </tr>
<?php } ?>
  </table>
</div>
<?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?> */