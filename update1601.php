<?php
	require_once("system-db.php");
	
	start_db();
	
	$sql = "update africatranscriptions_contacts set fullname = CONCAT(firstname, CONCAT(\" \", lastname))";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
?>
