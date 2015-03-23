<?php
	//Include database connection details
	require_once('system-db.php');
	require_once("sqlprocesstoarray.php");
	
	start_db();
	
	$caseid = $_POST['caseid'];
	$description = mysql_escape_string($_POST['description']);
	$invoicenumber = strtoupper( mysql_escape_string($_POST['invoicenumber']));
	$paymentnumber = mysql_escape_string($_POST['paymentnumber']);
	$shippinghandling = $_POST['shippinghandling'];
	$paymentdate = convertStringToDate($_POST['paymentdate']);
	$invoicedate = convertStringToDate($_POST['invoicedate']);
	$total = $_POST['total'];
	$penalty = $_POST['penalty'];
	$paid = $_POST['paid'];
	$toaddress = mysql_escape_string($_POST['toaddress']);
	$deladdress = mysql_escape_string($_POST['deladdress']);
	$termsid = $_POST['termsid'];
	$contactid = $_POST['contactid'];
	$officeid = $_POST['officeid'];
	$refundref = $_POST['refundref'];
	$refundamount = $_POST['refundamount'];
	$refunddate = convertStringToDate($_POST['refunddate']);
	
	if ($refundamount == null || $refundamount == "") {
		$refundamount = "0.00";
	}
	
	$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}invoices " .
			"(caseid, invoicenumber, paymentnumber, paid, shippinghandling, paymentdate, penalty, total, " .
			"toaddress, deladdress, termsid, contactid, createddate, officeid, description, " .
			"refundref, refundamount, refunddate, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
			"VALUES " .
			"($caseid, '$invoicenumber', '$paymentnumber', '$paid', $shippinghandling, '$paymentdate', '$penalty', $total, " .
			"'$toaddress', '$deladdress', '$termsid', $contactid, '$invoicedate', $officeid, '$description', " .
			"'$refundref', $refundamount, '$refunddate', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
	$result = mysql_query($qry);
	
	if (! $result) {
		if (mysql_errno() == 1062) {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}invoices SET " .
					"invoicenumber = '$invoicenumber', " .
					"paymentnumber = '$paymentnumber', " .
					"shippinghandling = '$shippinghandling', " .
					"paymentdate = '$paymentdate', " .
					"penalty = '$penalty', " .
					"createddate = '$invoicedate', " .
					"paid = '$paid', " .
					"total = $total, " .
					"description = '$description', " .
					"toaddress = '$toaddress', " .
					"deladdress = '$deladdress', " .
					"refundref = '$refundref', " .
					"refundamount = '$refundamount', " .
					"refunddate = '$refunddate', " .
					"termsid = $termsid, " .
					"contactid = $contactid, " .
					"officeid = $officeid, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE caseid = $caseid";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
			
			addAuditLog("I", "U", $caseid);
			
		} else {
			logError($qry . " - " . mysql_error());
		}
		
	} else {
		$id = mysql_insert_id();
		
		$qry = "SELECT A.emailtoclient, A.casenumber, D.email, D.fullname, D.firstname FROM 
				{$_SESSION['DB_PREFIX']}cases A
				INNER JOIN {$_SESSION['DB_PREFIX']}casecontacts B
				ON B.caseid = A.id
				INNER JOIN {$_SESSION['DB_PREFIX']}contacts D
				ON D.id = B.contactid
				WHERE A.id = $caseid";
	
		$result = mysql_query($qry);
		
		if (! $result) logError("Error: " . mysql_error());
		
		while (($member = mysql_fetch_assoc($result))) {
			$emailtoclient = $member['emailtoclient'];
			$contactemail = $member['email'];
			$contactfullname = $member['fullname'];
			$contactfirstname = $member['firstname'];
			$casenumber = $member['casenumber'];
			$message = "Your transcription request for case number $casenumber has been finalized and an invoice created, please check your emails as you will receive the invoice from one of our office users soon.";

			if ($emailtoclient == "Y") {
				smtpmailer(
						$contactemail, 
						"support@iafricatranscriptions.co.za", 
						"I Africa Transcriptions (PTY) LTD", 
						"Invoice Created", 
						getEmailHeader() . "<h4>Dear $contactfirstname,</h4><p>" . $message . "</p>" . getEmailFooter()
					);
			}
		}
					
		addAuditLog("I", "I", $caseid);
	}
	
	$qry = "SELECT id  " .
			"FROM {$_SESSION['DB_PREFIX']}invoices " .
			"WHERE caseid = $caseid";
	
	$json = new SQLProcessToArray();
	
	echo json_encode($json->fetch($qry));
?>