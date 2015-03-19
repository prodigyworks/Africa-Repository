<?php
	//Include database connection details
	require_once('system-header.php');
?>
<form method="post" id="mailform">
<label>To</label>
<input type="text" id="to" name="to" size=40 value="<?php if (isset($_POST['to'])) echo $_POST['to']; ?>" /><br><br>
<label>SMTP Server</label>
<input type="text" id="smtp" name="smtp" size=20 value="<?php if (isset($_POST['smtp'])) echo $_POST['smtp']; ?>" /><br><br>
<label>SMTP Port</label>
<input type="text" id="port" name="port" size=5 value="<?php if (isset($_POST['port'])) echo $_POST['port']; ?>" /><br><br>
<label>SMTP User</label>
<input type="text" id="user" name="user" size=40 value="<?php if (isset($_POST['user'])) echo $_POST['user']; ?>" /><br><br>
<label>SMTP Password</label>
<input type="text" id="pwd" name="pwd" size=30 value="<?php if (isset($_POST['pwd'])) echo $_POST['pwd']; ?>" /><br><br>
<input type="submit" />
</form>
<?php

	if (isset($_POST['to'])) {
		echo "<p>Testing</p>";
		
		$to = $_POST['to'];
		$from = "support@iafricatranscriptions.co.za";
		$from_name = "Support";
		$subject = "Test";
		$body = "Test email";
		$attachments = array();
	
		require_once('phpmailer/class.phpmailer.php');
	
		global $error;
	
		$array = explode(',', $to);
	
		try {
	
			$mail = new PHPMailer();  // create a new object
			$mail->AddReplyTo($from, $from_name);
			$mail->SetFrom("support@iafricatranscriptions.co.za", $from_name);
			$mail->IsHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $body;
	
			//SMTP Server: smtpcorp.com
			//SMTP Port: 2525
			//Username : danie@drdcomputers.net
			//Password : jeepcj5
	
					$mail->IsSMTP(); 								// telling the class to use SMTP
					$mail->Host       = $_POST['smtp']; 			// sets the SMTP server
					$mail->Port       = $_POST['port'];                   		// set the SMTP port for the GMAIL server
					$mail->SMTPAuth   = true;                  		// enable SMTP authentication
					$mail->SMTPDebug  = 1;                     		// enables SMTP debug information (for testing)
					// 1 = errors and messages
					// 2 = messages only
					$mail->Username   = $_POST['user']; 			// SMTP account username
					$mail->Password   = $_POST['pwd'];        			// SMTP account password
	
			for ($i = 0; $i < count($attachments); $i++) {
				$mail->AddAttachment($attachments[$i]);
			}
	
			for ($i = 0; $i < count($array); $i++) {
				$mail->AddAddress($array[$i]);
			}
	
			if(!$mail->Send()) {
				$error = 'Mail error: '.$mail->ErrorInfo;
				logError($error, false);
				return false;
					
			} else {
				$error = 'Message sent!';
				return true;
			}
	
		} catch (phpmailerException $e) {
			logError($e->errorMessage(), false);
				
		} catch (Exception $e) {
			logError($e->getMessage(), false);
		}
	}
	require_once('system-footer.php');
	
	
?>	
