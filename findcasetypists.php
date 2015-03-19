<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	$json = array();
	
	if (isset($_GET['caseid'])) {
		$caseid = $_GET['caseid'];
		$qry = "SELECT DISTINCT B.id, B.name " .
				"FROM {$_SESSION['DB_PREFIX']}casetypist A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}typist B " .
				"ON B.id = A.typistid " .
				"WHERE A.caseid = $caseid " .
				"ORDER BY B.name";
	}
	
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$line = array(
					"id" => $member['id'], 
					"name" => $member['name']
				);  
			
			array_push($json, $line);
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	echo json_encode($json); 
?>