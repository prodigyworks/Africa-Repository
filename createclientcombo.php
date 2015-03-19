<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	
	createComboOptions("id", "name", "{$_SESSION['DB_PREFIX']}courts", "", true);
?>