<?php require('_includes/application_top.php');

unset($_SESSION['user_id'], $_SESSION['user_name']);

header("Location: index.php", true, 301);
exit(0);

/* ?>
*/