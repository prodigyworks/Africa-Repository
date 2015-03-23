<?php
	require_once("system-db.php");
	
	start_db();
	
	function alter($sql) {
		$result = mysql_query($sql);
		
		if (! $result) {
			logError(mysql_error());
		}
	}
	 
	alter("ALTER TABLE africatranscriptions_cases ADD COLUMN emailtoclient VARCHAR(1) NULL DEFAULT NULL AFTER readytoinvoice;");
	
?>
