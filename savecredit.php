<?php
	//Include database connection details
	require_once('system-db.php');
	require_once("sqlprocesstoarray.php");
	
	start_db();
	
	$caseid = $_POST['caseid'];
	$cred_ref = mysql_escape_string($_POST['creditref']);
	$cred_date = convertStringToDate($_POST['creditdate']);
	$cred_reason = mysql_escape_string($_POST['creditreason']);
	$cred_officeid = mysql_escape_string($_POST['creditofficeid']);
	$cred_contactid = mysql_escape_string($_POST['creditcontactid']);
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}invoices SET " .
			"creditnumber = '$cred_ref', " .
			"creditdate = '$cred_date', " .
			"creditreason = '$cred_reason', " .
			"creditofficeid = $cred_officeid, " .
			"creditcontactid = $cred_contactid, " .
			"metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
			"WHERE caseid = $caseid";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}

	addAuditLog("I", "U", $caseid);
	
	$qry = "SELECT id  " .
			"FROM {$_SESSION['DB_PREFIX']}invoices " .
			"WHERE caseid = $caseid";
	
	$json = new SQLProcessToArray();
	
	echo json_encode($json->fetch($qry));
?>