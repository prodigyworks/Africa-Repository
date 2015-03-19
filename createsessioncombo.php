<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	
	$casetypist = $_POST['casetypist'];
	
	createCombo("sessionid", "sessionid", "sessionid", "{$_SESSION['DB_PREFIX']}casetypistsessions", "WHERE casetypistid = $casetypist AND (pages IS NULL OR pages = 0)", true, false, array(), false);
?>