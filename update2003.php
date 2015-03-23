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
	
	alter(
			"ALTER TABLE africatranscriptions_cases
			 ADD COLUMN prevdatereceived DATE NULL DEFAULT NULL AFTER datesenttotypist,
			 ADD COLUMN prevdataexpectedbackfromtypist DATE NULL DEFAULT NULL AFTER prevdatereceived,
			 ADD COLUMN datedatebackfromtypist DATE NULL DEFAULT NULL AFTER prevdataexpectedbackfromtypist"
		);
		
	alter(
			"UPDATE africatranscriptions_cases SET 
			 prevdatereceived = datereceived,
			 prevdataexpectedbackfromtypist = dataexpectedbackfromtypist,
			 datedatebackfromtypist = datebackfromtypist"
		)
?>
