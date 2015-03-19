<?php
	//Include database connection details
	require_once('system-db.php');
	require_once("sqlprocesstoarray.php");
	
	start_db();
	
	$caseid = $_POST['caseid'];
	$quotenumber = strtoupper( mysql_escape_string($_POST['quotenumber']));
	$paymentnumber = mysql_escape_string($_POST['paymentnumber']);
	$description = mysql_escape_string($_POST['description']);
	$shippinghandling = $_POST['shippinghandling'];
	$paymentdate = convertStringToDate($_POST['paymentdate']);
	$quotedate = convertStringToDate($_POST['quotedate']);
	$total = $_POST['total'];
	$paid = $_POST['paid'];
	$toaddress = mysql_escape_string($_POST['toaddress']);
	$deladdress = mysql_escape_string($_POST['deladdress']);
	$ourref = mysql_escape_string($_POST['ourref']);
	$yourref = mysql_escape_string($_POST['yourref']);
	$termsid = $_POST['termsid'];
	$contactid = $_POST['contactid'];
	$officeid = $_POST['officeid'];
	$depositamount = $_POST['depositamount'];
	$na = $_POST['na'];
	
	$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}quotes " .
			"(caseid, quotenumber, paymentnumber, paid, shippinghandling, paymentdate, total, " .
			"toaddress, deladdress, termsid, contactid, createddate, officeid, ourref, yourref, depositrequired," .
			"description, na, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
			"VALUES " .
			"($caseid, '$quotenumber', '$paymentnumber', '$paid', $shippinghandling, '$paymentdate', $total, " .
			"'$toaddress', '$deladdress', '$termsid', $contactid, '$quotedate', $officeid, '$ourref', '$yourref', $depositamount," .
			"'$description', '$na', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
	$result = mysql_query($qry);
	
	if (! $result) {
		if (mysql_errno() == 1062) {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}quotes SET " .
					"quotenumber = '$quotenumber', " .
					"paymentnumber = '$paymentnumber', " .
					"ourref = '$ourref', " .
					"yourref = '$yourref', " .
					"shippinghandling = '$shippinghandling', " .
					"paymentdate = '$paymentdate', " .
					"depositrequired = $depositamount, " .
					"createddate = '$quotedate', " .
					"paid = '$paid', " .
					"total = $total, " .
					"toaddress = '$toaddress', " .
					"description = '$description', " .
					"deladdress = '$deladdress', " .
					"na = '$na', " .
					"termsid = $termsid, " .
					"contactid = $contactid, " .
					"officeid = $officeid, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE caseid = $caseid";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
			
			addAuditLog("Q", "U", $caseid);
			
		} else {
			logError($qry . " - " . mysql_error());
		}
		
	} else {
		$id = mysql_insert_id();
			
		addAuditLog("Q", "I", $caseid);
	}
	
	$qry = "SELECT id  " .
			"FROM {$_SESSION['DB_PREFIX']}quotes " .
			"WHERE caseid = $caseid";
	
	$json = new SQLProcessToArray();
	
	echo json_encode($json->fetch($qry));
?>