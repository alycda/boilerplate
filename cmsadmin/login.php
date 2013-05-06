<?php require('_includes/application_top.php');

$error = array();
if (!empty($_POST)) {
	
	if (empty($_POST['email'])) {
		$error[] = 'Please enter your Email Address.';
	}
	if (empty($_POST['password'])) {
		$error[] = 'Please enter your Password.';
	}
	
	if (empty($error)) {
		$check_user = mysql_query("select user_id, user_name from cms_users where user_email like '".mysql_real_escape_string(trim($_POST['email']))."' and user_password = '".md5($_POST['password'])."' limit 1");
		if (mysql_num_rows($check_user) > 0) {
			
			$user = mysql_fetch_assoc($check_user);
			
			$_SESSION['user_id'] = $user['user_id'];
			$_SESSION['user_name'] = $user['user_name'];
			
			$redirect = 'index.php';
			if (!empty($_SESSION['redir'])) {
				$redirect = $_SESSION['redir'];
				unset($_SESSION['redir']);
			}
			
			header("Location: ".$redirect, true, 301);
			exit(0);
			
		} else {
			$error[] = 'Your Email Address/Password combination was wrong.';
		}
	}
	
	$_SESSION['errors']['login'] = $error;
	
	header("Location: login.php", true, 302);
	exit(0);
}

////
// The name for the Messages Session Array
$_message_name = 'login';

require(DIR_ADMININCLUDES . 'header.php'); ?>

<div class="page-header"><h1>Please log in</h1></div>

<form class="well span3 offset4" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
        <input name="email" type="text" class="input-block-level" placeholder="Email address">
        <input name="password" type="password" class="input-block-level" placeholder="Password">
        <label class="checkbox">
          <input type="checkbox" value="remember-me"> Remember me
        </label>
        <button class="btn btn-large btn-primary" type="submit">Sign in</button>
      </form>

<?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?>
*/