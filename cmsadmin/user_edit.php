<?php require('_includes/application_top.php');

$type = 'add';
$user = array();

if (!empty($_GET['user_id'])) {
	
	$get_user = mysql_query("select * from cms_users where user_id = ".(int)$_GET['user_id']." limit 1");
	$user = mysql_fetch_assoc ($get_user);
		
	$type = 'edit';
	
	$_GET['user_id'] = $user['user_id'];
}

$_error = array();
if (!empty($_POST)) {
	
	if (empty($_POST['user_name'])) {
		$_error[] = 'Please enter "Name"';
	}
	if (empty($_POST['user_email'])) {
		$_error[] = 'Please enter "Email"';
	} else {
		$check_email = mysql_num_rows(mysql_query("select user_id from cms_users where user_email = '".mysql_real_escape_string($_POST['user_email'])."' and user_id != ".(int)$user['user_id']." limit 1"));
		if ($check_email > 0) {
			$_error[] = 'The "Email" entered is already assigned to a user.';
		}
	}
	if ($type == 'add' || ($type != 'add' && !empty($_POST['user_password']))) {
		if (empty($_POST['user_password'])) {
			$_error[] = 'Please enter "Password"';
		}
		if (empty($_POST['user_password_conf'])) {
			$_error[] = 'Please enter "Password Confirmation"';
		}
		if (!empty($_POST['user_password']) && !empty($_POST['user_password_conf']) && $_POST['user_password'] != $_POST['user_password_conf']) {
			$_error[] = 'Please make sure "Password" and "Password Confirmation" match';
		}
	}
	
	if (count($_error) < 1) {
				
		if ($type == 'add') {
			
			mysql_query("insert into cms_users set user_name = '".mysql_real_escape_string($_POST['user_name'])."',
																						 user_email = '".mysql_real_escape_string($_POST['user_email'])."',
																						 user_password = '".md5($_POST['user_password'])."'") or die (mysql_error());
			
			$user_id = mysql_insert_id();
			$user['user_id'] = $user_id;
						
			$_SESSION['messages']['users_manage'][] = 'User Added Successfully';
			
		} else {
			
			mysql_query("update cms_users set user_name = '".mysql_real_escape_string($_POST['user_name'])."',
																				user_email = '".mysql_real_escape_string($_POST['user_email'])."'".(!empty($_POST['user_password'])?",
																				user_password = '".md5($_POST['user_password'])."'":'')."
																				where user_id = ".(int)$user['user_id']."
																				limit 1") or die (mysql_error());
			
			$_SESSION['messages']['users_manage'][] = 'User Updated Successfully';
			
		}
		
		header("Location: users_manage.php", true, 301);
		exit();
	}
}

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1><?php echo $type ?> User</h1></div>
  
  <?php show_error(); ?>
  
<?php //if (count($_error) > 0) { ?>
  <!--div class="error"-->
    <?php //echo implode('<br />', $_error) ?>
  <!--/div-->
<?php //} ?>
  <form action="user_edit.php?user_id=<?php echo $user['user_id'] ?>" enctype="multipart/form-data" method="post">
    <table class="table table-bordered table-striped">
      <tr>
        <th class="span1">Name:</td>
        <td><input type="text" name="user_name" value="<?php echo stripslashes(!empty($_POST['user_name'])?$_POST['user_name']:$user['user_name']) ?>" /></td>
      </tr>
      <tr>
        <th>Email:</td>
        <td><input type="text" name="user_email" value="<?php echo stripslashes(!empty($_POST['user_email'])?$_POST['user_email']:$user['user_email']) ?>" /></td>
      </tr>
<?php if ($type!='add') { ?>
      <tr>
        <td nowrap="nowrap" class="field-title" colspan="2" style="text-align:center">Please Note: <span style="font-weight: normal;">Only enter a password if you want to change it.</span></td>
      </tr>
<?php } ?>
      <tr>
        <th>Password:</td>
        <td><input type="password" name="user_password" /></td>
      </tr>
      <tr>
        <th>Password Confirmation:</td>
        <td><input type="password" name="user_password_conf" /></td>
      </tr>
      <tr>
        <th>&nbsp;</td>
        <td><button class="btn" type="submit" name="submit"><?php echo ($type=='add'?'Add':'Update') ?></button></td>
      </tr>
    </table>
  </form>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?>
*/