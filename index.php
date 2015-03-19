<?php
	require_once("system-db.php");
	
	start_db();
	
	if (isUserInRole("ADMIN")) {
		header("location: provinces.php");

	} else if (isUserInRole("COURTUSER")) {
		header("location: reports.php");
	
	} else if (isUserInRole("ACCOUNTING")) {
		header("location: managecases.php");
		
	} else if (isUserInRole("OFFICE")) {
		header("location: managecases.php");
		
	} else if (isUserInRole("TYPIST")) {
		header("location: typist.php");
		
	} else {
		header("location: provinces.php");
	}
?>
