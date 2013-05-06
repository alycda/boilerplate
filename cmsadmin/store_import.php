<?php set_time_limit(0);

require('_includes/application_top.php');

$_error = array();
if (!empty($_POST)) {
	
	include(DIR_ADMININCLUDES . 'zipcode.class.php');
	$z = new zipcode_class;
	
	ini_set('auto_detect_line_endings',TRUE);
	
	$i=0;
	
	$handle = fopen($_FILES['stores']['tmp_name'], "r");
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				
		foreach($data as $data_k=>$data_v) {
			$data[$data_k] = trim($data_v);
			if (substr($data_v, -1) == ',') {
				$data[$data_k] = substr($data_v, 0, -1);
			}
		}
		
		$_POST['store_name'] = $data[0];
		$_POST['store_address'] = $data[1];
		$_POST['store_city'] = $data[2];
		$_POST['store_state'] = strtoupper($data[3]);
		$_POST['store_zip'] = (int)$data[4];
		$_POST['store_phone'] = $data[5];
		//$_POST['store_website'] = $data[6];
				
		if (!empty($_POST['store_name'])) {
				
			$geolocation = geolocate_address($_POST['store_address'].', '. $_POST['store_city'].', '.$_POST['store_state'].', '.$_POST['store_zip'].', US');
			$latitude = $geolocation->Placemark[0]->Point->coordinates[1];
			$longitude = $geolocation->Placemark[0]->Point->coordinates[0];
			
			if (empty($latitude)) {
							
				$details = $z->get_zip_details($_POST['store_zip']);
				$latitude = $details['lattitude'];
				$longitude = $details['longitude'];
				
			}
						
			mysql_query("insert into cms_stores set store_name = '".mysql_real_escape_string($_POST['store_name'])."',
																							store_address = '".mysql_real_escape_string($_POST['store_address'])."',
																							store_city = '".mysql_real_escape_string($_POST['store_city'])."',
																							store_state = '".mysql_real_escape_string($_POST['store_state'])."',
																							store_zip = ".(int)$_POST['store_zip'].",
																							store_phone = '".mysql_real_escape_string($_POST['store_phone'])."',
																							store_latitude = '".$latitude."',
																							store_longitude = '".$longitude."',
																							store_active = 1") or die (mysql_error());
			
			$store_id = mysql_insert_id();
			
			$i++;
		
			sleep(1);	
		}
	}
	
	$_SESSION['messages']['stores_manage'][] = $i.' Stores Successfully Imported';
	
	header("Location: stores_manage.php", true, 301);
	exit();	
}

require(DIR_ADMININCLUDES . 'header.php'); ?>
  <div class="page-header"><h1>Import Stores</h1></div>
  
  <?php show_error(); ?>
  
<?php //if (count($_error) > 0) { ?>
  <!--div class="error"-->
    <?php //echo implode('<br />', $_error) ?>
  <!--/div-->
<?php //} ?>
  <form action="store_import.php" enctype="multipart/form-data" method="post">
    <table class="table table-bordered table-striped">
      <tr>
        <th colspan="2"><?php echo $type ?> Store</th>
      </tr>
      <tr>
        <th class="span2">Import CSV:</td>
        <td><input type="file" name="stores" /></td>
      </tr>
      <tr>
        <th colspan="2">Store Name, Address Line 1, City, State, Zip, Phone</td>
      </tr>
      <tr>
        <th>&nbsp;</td>
        <td><button class="btn" type="submit" name="submit">Import Stores</button></td>
      </tr>
    </table>
  </form>
  <?php require(DIR_ADMININCLUDES . 'footer.php');

/* ?>
*/