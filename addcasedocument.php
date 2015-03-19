<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	$caseid = $_GET['id'];
	$memberid = getLoggedOnMemberID();
	
	$qry = "SELECT A.id FROM {$_SESSION['DB_PREFIX']}documents A " .
		   "WHERE A.sessionid = '" . session_id() . "' " .
		   	"AND A.id NOT IN (SELECT documentid FROM {$_SESSION['DB_PREFIX']}casedocs WHERE documentid = A.id) " .
		   "ORDER BY A.id";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " = " . mysql_error());
	}
	
	while (($member = mysql_fetch_assoc($result))) {
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}casedocs " .
				"(caseid, documentid, createddate, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
				"VALUES " .
				"($caseid, " . $member['id'] . ", NOW(), NOW(), $memberid, NOW(), $memberid)";
				
		$itemresult = mysql_query($qry);
		
		if (! $itemresult) {
			logError($qry . " = " . mysql_error());
		}
	}
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}documents " .
		   "SET sessionid = NULL, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
		   "WHERE sessionid = '" . session_id() . "'";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " = " . mysql_error());
	}
		
	if (isset($_GET['refer'])) {
	  	header("location: " . base64_decode($_GET['refer']));
		
	} else {
	  	header("location: " . $_SERVER['HTTP_REFERER']);
	}	
?>