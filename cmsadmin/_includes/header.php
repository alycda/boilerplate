<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Content Management System Admin</title>

    <meta name="robots" content="noindex">

	<meta name="viewport" content="width=device-width">    
	
    <!-- CSS -->
    <link href="../css/bootstrap.css" rel="stylesheet">
    <link href="../css/bootstrap-responsive.css" rel="stylesheet">
    <link href="../css/bootstrap-datepicker.css" rel="stylesheet">
    <link href="../css/bootstrap-wysihtml5.css" rel="stylesheet">
 	<link href="../css/style.css" rel="stylesheet">
 	<link href="../css/cms.css" rel="stylesheet">
 
    <style type="text/css">

      /* Sticky footer styles
      -------------------------------------------------- */

      html,
      body {
        height: 100%;
        /* The html and body elements cannot have any padding or margin. */
      }

      /* Wrapper for page content to push down footer */
      #wrap {
        min-height: 100%;
        height: auto !important;
        height: 100%;
        /* Negative indent footer by it's height */
        margin: 0 auto -60px;
      }

      /* Set the fixed height of the footer here */
      #push,
      #footer {
        height: 60px;
      } #footer {
        background-color: #f5f5f5;
      }

      /* Lastly, apply responsive CSS fixes as necessary */
      @media (max-width: 767px) {
        #footer {
          margin-left: -20px;
          margin-right: -20px;
          padding-left: 20px;
          padding-right: 20px;
        }
      }

      /* Custom page CSS
      -------------------------------------------------- */
      /* Not required for template or sticky footer method. */

      #wrap > .container {
        padding-top: 60px;
      }
      .container .credit {
        margin: 20px 0;
      }
      
    </style>

	<base href="/cmsadmin/">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>

    <!-- Part 1: Wrap all page content here -->
    <div id="wrap">

      <!-- Fixed navbar -->
      <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
          <div class="container">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="brand" href="/cmsadmin">Project name</a>
            <div class="nav-collapse collapse">
              <ul class="nav">
                <li<?php echo (strstr($_SERVER['PHP_SELF'], 'content') || strstr($_SERVER['PHP_SELF'], 'category') || strstr($_SERVER['PHP_SELF'], 'index.php')?'  class="active"':'') ?>><a href="/cmsadmin">Content Management</a></li>
                <li<?php echo (strstr($_SERVER['PHP_SELF'], 'user')?' class="active"':'') ?>><a href="users_manage.php">User Management</a></li>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                    <li><a href="http://phpmyadmin/?db=<?=DB_NAME;?>" target="_blank">PhpMyAdmin</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li class="nav-header">Nav header</li>
                    <li><a href="#">Separated link</a></li>
                    <li><a href="#">One more separated link</a></li>
                  </ul>
                </li>
              </ul>
            </div><!--/.nav-collapse -->
          </div>
        </div>
      </div>
      
  <?php	if (!empty($_message_name)) {
      		if (count($_SESSION['messages'][$_message_name]) > 0) { ?>
      <div class="top-message">
        <?php echo implode('<br />', $_SESSION['messages'][$_message_name]) ?>
      </div>
      <?php		$_SESSION['messages'][$_message_name] = array();
      		}
      	} ?>
  <?php	if (!empty($_message_name)) {
      		if (count($_SESSION['errors'][$_message_name]) > 0) { ?>
      <div class="top-errors">
        <?php echo implode('<br />', $_SESSION['errors'][$_message_name]) ?>
      </div>
      <?php		$_SESSION['errors'][$_message_name] = array();
      		}
      	} ?>
      	
      	<!-- Begin page content -->
      	<div class="container">