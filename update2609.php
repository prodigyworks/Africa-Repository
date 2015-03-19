<?php
	require_once("system-db.php");
	
	start_db();
	
	$sql = "ALTER TABLE drdcomputers_invoices CHANGE COLUMN createddate createddate DATE NULL DEFAULT NULL AFTER contactid";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
	$sql = "UPDATE drdcomputers_pagenavigation SET target = NULL WHERE childpageid = 7218";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
?>
