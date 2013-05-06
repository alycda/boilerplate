<?php require('_includes/application_top.php');

$type = 'add';
$content = array();

if (!empty($_GET['store_id'])) {
	
	////
	// Get Store
	$get_store = mysql_query("select * from cms_stores where store_id = ".(int)$_GET['store_id']." limit 1");
	$store = mysql_fetch_assoc ($get_store);
		
	$type = 'edit';
}

$_error = array();
if (!empty($_POST)) {
	
	if (empty($_POST['store_name'])) {
		$_error[] = 'Please enter "Store Name"';
	}
	
	if (count($_error) < 1) {
		
		$_POST['store_zip'] = (int)$_POST['store_zip'];
		
		$geolocation = geolocate_address($_POST['store_address'].', '. $_POST['store_city'].', '.$_POST['store_state'].', '.$_POST['store_zip'].', US');
		$latitude = $geolocation->Placemark[0]->Point->coordinates[1];
		$longitude = $geolocation->Placemark[0]->Point->coordinates[0];
		
		if (empty($latitude)) {
			
			$_SESSION['messages']['stores_manage'][] = 'Geolocation Failed. Using Zipcode Class instead.';
			
			include(DIR_ADMININCLUDES . 'zipcode.class.php');
			$z = new zipcode_class;
			$details = $z->get_zip_details($_POST['store_zip']);
			$latitude = $details['lattitude'];
			$longitude = $details['longitude'];
			
		}
		
		if ($type == 'add') {
			
			mysql_query("insert into cms_stores set store_name = '".mysql_real_escape_string($_POST['store_name'])."',
																							store_address = '".mysql_real_escape_string($_POST['store_address'])."',
																							store_city = '".mysql_real_escape_string($_POST['store_city'])."',
																							store_state = '".mysql_real_escape_string($_POST['store_state'])."',
																							store_zip = ".(int)$_POST['store_zip'].",
																							store_phone = '".mysql_real_escape_string($_POST['store_phone'])."',
																							store_latitude = '".$latitude."',
																							store_longitude = '".$longitude."',
																							store_active = ".(int)$_POST['store_active']) or die (mysql_error());
			
			$store_id = mysql_insert_id();
			
			$_SESSION['messages']['stores_manage'][] = 'Store Added Successfully';
			
		} else {
			
			mysql_query("update cms_stores set store_name = '".mysql_real_escape_string($_POST['store_name'])."',
																				 store_address = '".mysql_real_escape_string($_POST['store_address'])."',
																				 store_city = '".mysql_real_escape_string($_POST['store_city'])."',
																				 store_state = '".mysql_real_escape_string($_POST['store_state'])."',
																				 store_zip = ".(int)$_POST['store_zip'].",
																				 store_phone = '".mysql_real_escape_string($_POST['store_phone'])."',
																				 store_latitude = '".$latitude."',
																				 store_longitude = '".$longitude."',
																				 store_active = ".(int)$_POST['store_active']."
																				 where store_id = ".(int)$store['store_id']." limit 1") or die (mysql_error());
			
			$_SESSION['messages']['stores_manage'][] = 'Store Updated Successfully';
			
		}
		
		header("Location: stores_manage.php", true, 301);
		exit();
		
	}
}

if ($type == 'add')
	$store['store_active'] = 1;

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1><?php echo $type ?> Store</h1></div>
  <?php show_error(); ?>
<?php //if (count($_error) > 0) { ?>
  <!--div class="error"-->
    <?php //echo implode('<br />', $_error) ?>
  <!--/div-->
<?php //} ?>
  <form action="store_edit.php?store_id=<?php echo $store['store_id'] ?>" enctype="multipart/form-data" method="post">
    <table class="table table-bordered table-striped">
      <tr>
        <th class="span1">Name:</td>
        <td><input type="text" name="store_name" value="<?php echo stripslashes(!empty($_POST['store_name'])?$_POST['store_name']:$store['store_name']) ?>" /></td>
      </tr>
      <tr>
        <th>Address:</td>
        <td><input type="text" name="store_address" value="<?php echo stripslashes(!empty($_POST['store_address'])?$_POST['store_address']:$store['store_address']) ?>" size="30" /></td>
      </tr>
      <tr>
        <th>City:</td>
        <td><input type="text" name="store_city" value="<?php echo stripslashes(!empty($_POST['store_city'])?$_POST['store_city']:$store['store_city']) ?>" size="30" /></td>
      </tr>
      <tr>
        <th>State:</td>
        <td><select name="store_state">
            <option value="">Select State</option>
<?php $get_states = mysql_query("select state_prefix, state_name from states order by state_name");
	while ($state = mysql_fetch_assoc($get_states)) { ?>
            <option value="<?php echo $state['state_prefix'] ?>"<?php echo ($store['store_state']==$state['state_prefix']?' selected="selected"':'') ?>><?php echo ucwords(strtolower($state['state_name'])) ?></option>
<?php } ?>
          </select></td>
      </tr>
      <tr>
        <th>Zip:</td>
        <td><input type="text" name="store_zip" value="<?php echo stripslashes(!empty($_POST['store_zip'])?$_POST['store_zip']:$store['store_zip']) ?>" size="7" maxlength="5" /></td>
      </tr>
      <tr>
        <th>Phone:</td>
        <td><input type="text" name="store_phone" value="<?php echo stripslashes(!empty($_POST['store_phone'])?$_POST['store_phone']:$store['store_phone']) ?>" /></td>
      </tr>
      <tr>
        <th>Active:</td>
        <td><select name="store_active">
            <option value="1">Yes</option>
            <option value="0"<?php echo (empty($store['store_active'])?' selected="selected"':'') ?>>No</option>
          </select></td>
      </tr>
      <tr>
        <th>&nbsp;</td>
        <td><button class="btn" type="submit" name="submit"><?php echo ($type=='add'?'Add':'Update') ?> Store</button></td>
      </tr>
    </table>
  </form>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?>
*/