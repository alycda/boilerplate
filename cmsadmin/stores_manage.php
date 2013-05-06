<?php require('_includes/application_top.php');

if (!empty($_GET['delete'])) {
	
	mysql_query("delete from cms_stores where store_id = ".(int)$_GET['delete']." limit 1");
	
	$_SESSION['messages']['stores_manage'][] = 'Store Deleted Successfully';

	header("Location: stores_manage.php", true, 301);
	exit();
}

$per_page = 50;

if (empty($_GET['page']) || (int)$_GET['page'] < 1) {
	if (empty($_SESSION['stores_last_visited_page'])) {
		$_GET['page'] = 1;
	} else {
		$_GET['page'] = (int)$_SESSION['stores_last_visited_page'];
	}
} else {
	$_GET['page'] = (int)$_GET['page'];
}

$where = array();
if (!empty($_POST['search'])) {
	
	$where[] = "store_name like '%".mysql_real_escape_string($_POST['search'])."%'";
	$where[] = "store_address like '%".mysql_real_escape_string($_POST['search'])."%'";
	$where[] = "store_city like '%".mysql_real_escape_string($_POST['search'])."%'";
	$where[] = "store_zip like '%".mysql_real_escape_string($_POST['search'])."%'";
	$where[] = "store_phone like '%".mysql_real_escape_string($_POST['search'])."%'";
	
	$_SESSION['stores_last_search'] = $_POST['search'];
	
	////
	// Reset the pagination, when they research
	$_GET['page'] = 1;
	
} elseif (!empty($_POST)) {
	
	////
	// Clear the stored last search, if they send an empty field
	$_SESSION['stores_last_search'] = '';
	
	$_GET['page'] = 1;
	
} elseif (!empty($_SESSION['stores_last_search'])) {
	
	$where[] = "store_name like '%".mysql_real_escape_string($_SESSION['stores_last_search'])."%'";
	$where[] = "store_address like '%".mysql_real_escape_string($_SESSION['stores_last_search'])."%'";
	$where[] = "store_city like '%".mysql_real_escape_string($_SESSION['stores_last_search'])."%'";
	$where[] = "store_zip like '%".mysql_real_escape_string($_SESSION['stores_last_search'])."%'";
	$where[] = "store_phone like '%".mysql_real_escape_string($_SESSION['stores_last_search'])."%'";
}

$total_stores = @mysql_result(mysql_query("select count(store_id) from cms_stores".(!empty($where)?" where ".implode(" or ", $where):'')), 0);
$total_pages = ceil($total_stores / $per_page);

if ($_GET['page'] > $total_pages) {
	$_GET['page'] = $total_pages;
}

////
// Store the last visited page in a session
$_SESSION['stores_last_visited_page'] = $_GET['page'];

$start = (($per_page * $_GET['page']) - $per_page);
if ($start < 0) $start = 0;

$get_stores = mysql_query("select store_id, store_name, store_zip, store_active from cms_stores".(!empty($where)?" where ".implode(" or ", $where):'')." order by store_name asc limit ".$start.", ".$per_page) or die (mysql_error());

////
// The name for the Messages Session Array
$_message_name = 'stores_manage';

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1>Stores Management</h1></div>
  <div style="margin: 0 0 20px;"><i class="icon-plus"></i><a href="store_edit.php">Add Store</a></div>
  <div style="margin: 0 0 20px;"><i class="icon-plus"></i><a href="store_import.php">Import Stores</a></div>
  <div style="margin: 0 0 20px;">
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
      <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td>Search Stores:&nbsp;</td>
          <td><input type="text" name="search" value="<?php echo stripslashes($_SESSION['stores_last_search']) ?>" />&nbsp;<button class="btn" type="submit" name="submit">Search</button></td>
        </tr>
      </table>
    </form>
  </div>
  <table class="table table-bordered table-striped table-hover">
<?php if ($total_pages > 1) { ?>
    <tr>
      <td colspan="5">
        <div style="overflow: auto;">
<?php if ($_GET['page'] > 1 && $total_pages > 1) { ?>
          <a href="stores_manage.php?page=<?php echo ($_GET['page']-1) ?>" style="float: left;">&laquo;&nbsp;Previous Page</a>
<?php }
		if (($_GET['page']+1) <= $total_pages) { ?>
          <a href="stores_manage.php?page=<?php echo ($_GET['page']+1) ?>" style="float: right;">Next Page&nbsp;&raquo;</a>
<?php } ?>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="5">
        <div style="padding: 0 0 5px; text-align:center;">
          <strong>Viewing</strong> <?php echo ($start+1) ?> - <?php echo (($start+$per_page)>$total_stores?$total_stores:($start+$per_page)) ?> <strong>of</strong> <?php echo $total_stores ?>
        </div>
      </td>
    </tr>
<?php } ?>
    <tr>
      <th>Store Name</th>
      <th>Zip Code</th>
      <th>Active?</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
<?php while ($store = mysql_fetch_assoc ($get_stores)) { ?>
    <tr>
      <td class="content-name"><a href="store_edit.php?store_id=<?php echo $store['store_id'] ?>"><?php echo stripslashes($store['store_name']) ?></a></td>
      <td><?php echo $store['store_zip'] ?></td>
      <td><?php echo (!empty($store['store_active'])?'Yes':'No') ?></td>
      <td><a href="store_edit.php?store_id=<?php echo $store['store_id']?>"><i class="icon-pencil"></i></a></td>
      <td><a href="stores_manage.php?delete=<?php echo $store['store_id']?>" onclick="return confirm('Are you sure you want to delete this Store?');"><i class="icon-trash"></i></a></td>
    </tr>
<?php }
if (mysql_num_rows($get_stores) < 1) { ?>
    <tr>
      <td colspan="5">There are no stores.</td>
    </tr>
<?php } ?>
  </table>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?> */