<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	
	$courtid = $_POST['courtid'];
	
	createComboOptions("id", "fullname", "{$_SESSION['DB_PREFIX']}contacts", "WHERE courtid = $courtid", false);
?>