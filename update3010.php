<?php
	require_once("system-db.php");
	
	start_db();

	$sql = "CREATE TRIGGER contact_insert BEFORE INSERT ON africatranscriptions_contacts\n" .
			" FOR EACH ROW BEGIN\n" .
			"SET NEW.fullname = CONCAT(NEW.firstname, ' ', NEW.lastname);\n" .
			"END;\n";
		
	echo str_replace("\n", "<br>", $sql);
		
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
	
	$sql = "CREATE TRIGGER contact_update BEFORE UPDATE ON africatranscriptions_contacts\n" .
			"FOR EACH ROW BEGIN\n" .
			"SET NEW.fullname = CONCAT(NEW.firstname, ' ', NEW.lastname);\n" .
			"END;\n";
			
	echo str_replace("\n", "<br>", $sql);
			
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
	?>
