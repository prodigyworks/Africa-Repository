<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	
	$provinceid = $_POST['provinceid'];
	
	createComboOptions("id", "displayname", "{$_SESSION['DB_PREFIX']}invoiceitemtemplates", "WHERE provinceid = $provinceid AND type = 'T'", true);
?>