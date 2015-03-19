<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	
	$session_caseid = $_POST['session_caseid'];
	$session_typistid = $_POST['session_typistid'];
	$session_sessionid = $_POST['session_sessionid'];

	$json = array();
	$error = "";

	if ($session_sessionid != "" && $session_sessionid) {
		$qry = "SELECT A.id " .
				"FROM  {$_SESSION['DB_PREFIX']}casetypistsessions A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}casetypist B " .
				"ON B.id = A.casetypistid " .
				"WHERE B.caseid = $session_caseid " .
				"AND B.typistid = $session_typistid " .
				"AND A.sessionid = '$session_sessionid'";
		$result = mysql_query($qry);
		logError($qry, false);
		if (! $result) logError("Error: " . mysql_error());
		
		//Check whether the query was successful or not
		while (($member = mysql_fetch_assoc($result))) {
			$error = "Session '$session_sessionid' already exists";
		}
	}	
	
	array_push($json, array("error" => $error));
	
	echo json_encode($json); 
?>