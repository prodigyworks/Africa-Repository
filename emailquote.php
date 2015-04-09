<?php
	require_once('system-db.php');
	require_once('quotereportlib.php');
	
	start_db();
	
	error_reporting(0);

	$id = $_POST['id'];
	$emailaddress = $_POST['emailaddress'];
	$quotenumber = $id;
	
	if(!isset($id)){
	     logError("Please select your image!");
	     
	} else {
		ob_start();
		
		$pdf = new QuoteReport( 'P', 'mm', 'A4', $id);
		$pdf->Output();
		$imgstring = ob_get_contents();
        ob_end_clean();
        
		$sql = "SELECT quotenumber FROM {$_SESSION['DB_PREFIX']}quotes B " .
				"WHERE id = $id";
		$result = mysql_query($sql);
		
		if ($result) {
			while (($member = mysql_fetch_assoc($result))) {                
				$quotenumber = $member['quotenumber'];
			}
		}
		
		$subject = "Quote : $quotenumber";
		$body = "Quote $quotenumber has been attached";
		
		$file = "uploads/quote" . str_replace("/", "-", $quotenumber) . ".pdf";
//			logError($file, false);
		
		try {
			$out = fopen($file, "wb");
			fwrite($out, $imgstring);
			fclose($out);
			
//			logError("Sending : [$emailaddress]", false);
			
			smtpmailer($emailaddress, "no-reply@iafricatranscriptions.co.za", "I Africa Transcriptions (PTY) LTD", $subject, $body . "<br>". getSiteConfigData()->emailfooter, array($file));
//			smtpmailer($emailaddress, "no-reply@iafricatranscriptions.co.za", "I Africa Transcriptions (PTY) LTD", $subject, $body . "<br>". getSiteConfigData()->emailfooter);
//			logError("Sent : [$emailaddress]", false);
			
		} catch (Exception $ex) {
			logError($e->getMessage(), false);
		}
		
	}
	
	echo json_encode(array("root => 'ok'"));
?> 