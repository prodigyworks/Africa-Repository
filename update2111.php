<?php
	require_once("system-db.php");
	
	start_db();
	
	$sql = "ALTER TABLE africatranscriptions_cases ADD COLUMN judge VARCHAR(50) NULL DEFAULT NULL AFTER plaintiff";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
?>
