<?php
	require_once("system-db.php");
	
	start_db();
	
	$sql = "ALTER TABLE africatranscriptions_courts ADD COLUMN telephone VARCHAR(20) NULL AFTER address, ADD COLUMN cellphone VARCHAR(20) NULL AFTER telephone, ADD COLUMN fax VARCHAR(20) NULL AFTER cellphone, ADD COLUMN email VARCHAR(60) NULL AFTER fax, ADD COLUMN contact VARCHAR(60) NULL AFTER email";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
	
	$sql = "CREATE TABLE africatranscriptions_offices (	id INT(10) NOT NULL AUTO_INCREMENT,	name VARCHAR(50) NULL DEFAULT NULL,	bankingdetails VARCHAR(100) NULL, telephone VARCHAR(20) NULL DEFAULT NULL, cellphone VARCHAR(20) NULL DEFAULT NULL, fax VARCHAR(20) NULL DEFAULT NULL, email VARCHAR(60) NULL DEFAULT NULL, contact VARCHAR(60) NULL DEFAULT NULL, address TEXT NULL,	PRIMARY KEY (id),	UNIQUE INDEX provinceid_name (name)) COLLATE='latin1_swedish_ci' ENGINE=MyISAM AUTO_INCREMENT=31";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
	
	$sql = "ALTER TABLE africatranscriptions_invoices ADD COLUMN officeid INT(10) NULL DEFAULT NULL AFTER contactid;";
	$result = mysql_query($sql);
	
	if (! $result) {
		logError(mysql_error());
	}
	
	
	
	
	
?>
