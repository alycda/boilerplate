<?php require('_includes/application_top.php');

if (!empty($_GET['delete'])) {
	
	mysql_query("delete from cms_users where user_id = ".(int)$_GET['delete']." limit 1");
	$_SESSION['messages']['users_manage'][] = 'User Deleted Successfully';
	
	header("Location: users_manage.php", true, 301);
	exit();
}

////
// The name for the Messages Session Array
$_message_name = 'users_manage';

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1>User Management</h1></div>
  <div style="margin: 0 0 20px;"><i class="icon-plus"></i><a href="user_edit.php">Add User</a></div>
  <table class="table table-bordered table-striped table-hover">
    <tr>
      <th>User Name</th>
      <th>User Email</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
<?php $get_users = mysql_query("select user_id, user_name, user_email from cms_users order by user_name asc");
while ($user = mysql_fetch_assoc ($get_users)) { ?>
    <tr>
      <td class="content-name"><a href="user_edit.php?user_id=<?php echo $user['user_id'] ?>"><?php echo stripslashes($user['user_name']) ?></a></td>
      <td><a href="mailto:<?php echo $user['user_email'] ?>"><?php echo $user['user_email'] ?></a></td>
      <td><a href="user_edit.php?user_id=<?php echo $user['user_id']?>"><i class="icon-pencil"></i></a></td>
      <td><a href="users_manage.php?delete=<?php echo $user['user_id']?>" onclick="return confirm('Are you sure you want to delete this User?');"><i class="icon-trash"></i></a></td>
    </tr>
<?php } ?>
  </table>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?> */