<?php
	require_once("system-db.php");
	
	start_db();
	
	$sql = "ALTER TABLE africatranscriptions_invoices ADD COLUMN readytoinvoice VARCHAR(1) NULL DEFAULT NULL AFTER paid;";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
?>
