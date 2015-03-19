<?php
	require_once("system-db.php");
	
	start_db();
	
	$sql = "ALTER TABLE drdcomputers_cases ADD COLUMN remarks VARCHAR(60) NULL DEFAULT NULL AFTER depositamount, ADD COLUMN time VARCHAR(60) NULL DEFAULT NULL AFTER remarks";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
?>
