<?php
	require_once("system-db.php");
	
	start_db();
	
	$sql = "ALTER TABLE africatranscriptions_cases ADD COLUMN datecdssentbacktocourt DATE NULL DEFAULT NULL AFTER transcriptrequestdate;";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
?>
