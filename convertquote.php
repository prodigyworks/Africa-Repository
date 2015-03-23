<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	
	$caseid = $_POST['id'];
	$invoicenumber = $_POST['invoicenumber'];
	
	$qry = "SELECT *  " .
			"FROM {$_SESSION['DB_PREFIX']}quotes " .
			"WHERE caseid = $caseid";
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$paymentnumber = $member['paymentnumber'];
			$paid = $member['paid'];
			$shippinghandling = $member['shippinghandling'];
			$paymentdate = $member['paymentdate'];
			$total = $member['total'];
			$toaddress = $member['toaddress'];
			$deladdress = $member['deladdress'];
			$description = $member['description'];
			$termsid = getSiteConfigData()->defaultpaymenttermsforcourt;
			$contactid = $member['contactid'];
			$quotedate = $member['createddate'];
			$officeid = $member['officeid'];
					
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}invoices " .
					"(caseid, invoicenumber, paymentnumber, paid, shippinghandling, paymentdate, total, " .
					"toaddress, deladdress, termsid, contactid, createddate, officeid, description, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
					"VALUES " .
					"($caseid, '$invoicenumber', '$paymentnumber', '$paid', $shippinghandling, '$paymentdate', $total, " .
					"'$toaddress', '$deladdress', '$termsid', $contactid, '$quotedate', $officeid, '$description', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
			$itemresult = mysql_query($qry);
			
			$invoiceid = mysql_insert_id();
			
			if (! $itemresult) {
				logError($qry . " - " . mysql_error());
			}
	
			$qry = "SELECT *  " .
					"FROM {$_SESSION['DB_PREFIX']}quoteitems " .
					"WHERE quoteid = " . $member['id'];
			$itemresult = mysql_query($qry);
			
			if ($itemresult) {
				while (($itemmember = mysql_fetch_assoc($itemresult))) {
					$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}invoiceitems " .
							"(invoiceid, description, notes, qty, templateid, unitprice, vatrate, " .
							"vat, total, itemcode, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
							"VALUES " .
							"(" . $invoiceid . ", " .
							"'" . mysql_escape_string($itemmember['description']) . "', " .
							"'" . mysql_escape_string($itemmember['notes']) . "', " .
							"'" . mysql_escape_string($itemmember['qty']) . "', " .
							"'" . mysql_escape_string($itemmember['templateid']) . "', " .
							"'" . mysql_escape_string($itemmember['unitprice']) . "', " .
							"'" . mysql_escape_string($itemmember['vatrate']) . "', " .
							"'" . mysql_escape_string($itemmember['vat']) . "', " .
							"'" . mysql_escape_string($itemmember['total']) . "', " .
							"'" . mysql_escape_string($itemmember['itemcode']) . "'" .
							", NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ") ";
					$insertresult = mysql_query($qry);
					
					if (! $insertresult) {
						logError($qry . " - " . mysql_error());
					}
				}
				
			} else {
				logError($qry . " - " . mysql_error());
			}
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
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
					
	
	
	array_push($json, array("ok" => "1"));
	
	echo json_encode($json); 
?>