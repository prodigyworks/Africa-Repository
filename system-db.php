<?php
require_once('datafilter.php');

class SiteConfigClass {
	public $domainurl;
	public $emailfooter;
	public $lastschedulerun;
	public $vatrate;
	public $runscheduledays;
	public $defaultpaymenttermsforclient;
	public $defaultpaymenttermsforcourt;
	public $defaultcontactid;
	public $privateclientprovinceid;
}

function start_db() {
	if(!isset($_SESSION)) {
		session_start();
	}
	
	date_default_timezone_set('Africa/Johannesburg');	
	error_reporting(0);

	if (! isset($_SESSION['PRODIGYWORKS.INI'])) {
		$_SESSION['PRODIGYWORKS.INI'] = parse_ini_file("prodigyworks.ini");
		$_SESSION['DB_PREFIX'] = $_SESSION['PRODIGYWORKS.INI']['DB_PREFIX']; 
		$_SESSION['CACHING'] = $_SESSION['PRODIGYWORKS.INI']['CACHING']; 
	}
	
	if (! defined('DB_HOST')) {
		$iniFile = $_SESSION['PRODIGYWORKS.INI'];
		
		define('DB_HOST', $iniFile['DB_HOST']);
	    define('DB_USER', $iniFile['DB_USER']);
	    define('DB_PASSWORD', $iniFile['DB_PASSWORD']);
	    define('DB_DATABASE', $iniFile['DB_DATABASE']);
	    define('DEV_ENV', $iniFile['DEV_ENV']);
	    
		//Connect to mysql server
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		
		if (!$link) {
			logError('Failed to connect to server: ' . mysql_error());
		}
		
		//Select database
		$db = mysql_select_db(DB_DATABASE);
		
		if(!$db) {
			logError("Unable to select database:" . DB_DATABASE);
		}
		
		mysql_query("BEGIN");
	
		if (! isset($_SESSION['SITE_CONFIG'])) {
			$qry = "SELECT * FROM {$_SESSION['DB_PREFIX']}siteconfig";
			$result = mysql_query($qry);
	
			//Check whether the query was successful or not
			if ($result) {
				if (mysql_num_rows($result) == 1) {
					$member = mysql_fetch_assoc($result);
					
					$data = new SiteConfigClass();
					$data->domainurl = $member['domainurl'];
					$data->vatrate = $member['vatrate'];
					$data->emailfooter = $member['emailfooter'];
					$data->lastschedulerun = $member['lastschedulerun'];
					$data->runscheduledays = $member['runscheduledays'];
					$data->defaultpaymenttermsforcourt = $member['defaultpaymenttermsforcourt'];
					$data->defaultpaymenttermsforclient = $member['defaultpaymenttermsforclient'];
					$data->defaultcontactid = $member['defaultcontactid'];
					$data->privateclientprovinceid = $member['privateclientprovinceid'];
					
					$_SESSION['SITE_CONFIG'] = $data;
				}
					
			} else {
				header("location: system-access-denied.php");
			}
		}
	    
	}
}

function GetOfficeID($userid) {
		$qry = "SELECT officeid FROM {$_SESSION['DB_PREFIX']}members A " .
				"WHERE A.member_id = $userid ";
		$result = mysql_query($qry);
		$name = "0";
	
		//Check whether the query was successful or not
		if($result) {
			while (($member = mysql_fetch_assoc($result))) {
				$name = $member['officeid'];
			}
		}
		
		return $name;
}

function GetOfficeName($userid) {
		$qry = "SELECT B.name FROM {$_SESSION['DB_PREFIX']}members A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}offices B " .
				"ON B.id = A.officeid " .
				"WHERE A.member_id = $userid ";
		$result = mysql_query($qry);
		$name = "0";
	
		//Check whether the query was successful or not
		if($result) {
			while (($member = mysql_fetch_assoc($result))) {
				$name = $member['name'];
			}
		}
		
		return $name;
}

function GetUserName($userid = "") {
	if ($userid == "") {
		return $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME'];
		
	} else {
		$qry = "SELECT * FROM {$_SESSION['DB_PREFIX']}members A " .
				"WHERE A.member_id = $userid ";
		$result = mysql_query($qry);
		$name = "Unknown";
	
		//Check whether the query was successful or not
		if($result) {
			while (($member = mysql_fetch_assoc($result))) {
				$name = $member['firstname'] . " " . $member['lastname'];
			}
		}
		
		return $name;
	}
}

function GetEmail($userid) {
	$qry = "SELECT email FROM {$_SESSION['DB_PREFIX']}members A " .
			"WHERE A.member_id = $userid ";
	$result = mysql_query($qry);
	$name = "Unknown";

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$name = $member['email'];
		}
	}
	
	return $name;
}

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")  
{ 
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue; 
 
  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue); 
 
  switch ($theType) { 
    case "text": 
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL"; 
      break;     
    case "long": 
    case "int": 
      $theValue = ($theValue != "") ? intval($theValue) : "NULL"; 
      break; 
    case "double": 
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL"; 
      break; 
    case "date": 
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL"; 
      break; 
    case "defined": 
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue; 
      break; 
  } 
  return $theValue; 
} 

function initialise_db() {
}
	
function dateStampString($oldnotes, $newnotes, $prefix = "") {
	if ($newnotes == $oldnotes) {
		return $oldnotes;
	}
	
	return 
		mysql_escape_string (
				$oldnotes . "\n\n" .
				$prefix . " - " . 
				date("F j, Y, g:i a") . " : " . 
				$_SESSION['SESS_FIRST_NAME'] . " " . 
				$_SESSION['SESS_LAST_NAME'] . "\n" . 
				$newnotes
			);
}
	
	
function smtpmailer2($to, $from, $from_name, $subject, $body, $attachments = array()) { 
	global $error;
	
	
	
	
	
	
	
	
	
//define the receiver of the email 
//define the subject of the email 
//create a boundary string. It must be unique 
//so we use the MD5 algorithm to generate a random hash 
$random_hash = md5(date('r', time())); 
//define the headers we want passed. Note that they are separated with \r\n 
$headers = "From: $from_name <$from>\r\nReply-To: $from"; 
//add boundary string and mime type specification 
$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\""; 
//read the atachment file contents into a string,
//encode it with MIME base64,
//and split it into smaller chunks
//define the body of the message. 
ob_start(); //Turn on output buffering 
?> 
--PHP-mixed-<?php echo $random_hash; ?>  
Content-Type: multipart/alternative; boundary="PHP-alt-<?php echo $random_hash; ?>" 

--PHP-alt-<?php echo $random_hash; ?>  
Content-Type: text/html; charset="iso-8859-1" 
Content-Transfer-Encoding: 7bit

<?php echo $body; ?>

--PHP-alt-<?php echo $random_hash; ?>-- 

--PHP-mixed-<?php echo $random_hash; ?>  
<?php
		for($x=0;$x<count($attachments);$x++){
			$file = fopen($attachments[$x],"rb");
			$data = fread($file,filesize($attachments[$x]));
			fclose($file);
//$attachment = chunk_split(base64_encode(file_get_contents($attachments[$x]))); 
?>
Content-Type: application/octet-stream; name="<?php echo basename($attachments[$x]); ?>"  
Content-Transfer-Encoding: base64  
Content-Disposition: attachment;

<?php echo base64_encode($data); ?> 
--PHP-mixed-<?php echo $random_hash; ?>-- 
<?php
		}
?>

<?php 
//copy current buffer contents into $message variable and delete current output buffer 
$message = ob_get_clean(); 
//send the email 
$mail_sent = @mail( $to, $subject, $message, $headers ); 

}

function smtpmailer($to, $from, $from_name, $subject, $body, $attachments = array()) { 
	if (DEV_ENV == "true") {
		return;
	}
	
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
		
// 		$mail->IsSMTP(); 								// telling the class to use SMTP
// 		$mail->Host       = "smtpcorp.com"; 			// sets the SMTP server
// 		$mail->Port       = 2525;                   		// set the SMTP port for the GMAIL server
// 		$mail->SMTPAuth   = true;                  		// enable SMTP authentication
// 		$mail->SMTPDebug  = 1;                     		// enables SMTP debug information (for testing)
// 		// 1 = errors and messages
// 		// 2 = messages only
// 		$mail->Username   = "danie@drdcomputers.net"; 			// SMTP account username
// 		$mail->Password   = "jeepcj5";        			// SMTP account password
        
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

function sendRoleMessage($role, $subject, $message, $attachments = array()) {
	$qry = "SELECT B.email, B.firstname, B.member_id FROM {$_SESSION['DB_PREFIX']}userroles A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
			"ON B.member_id = A.memberid " .
			"WHERE A.roleid = '$role' ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], 'support@iafricatranscriptions.co.za', 'I Africa Transcriptions (PTY) LTD', $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(), $attachments);
			
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			sendMessage($subject, $message, $member['member_id']);
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
}


function sendInternalRoleMessage($role, $subject, $message, $attachments = array()) {
	$from = "support@iafricatranscriptions.co.za";
	$fromName = "I Africa Transcriptions (PTY) LTD";
	$qry = "SELECT B.email, B.firstname, B.lastname FROM {$_SESSION['DB_PREFIX']}members B " .
			"WHERE B.member_id = " . getLoggedOnMemberID();
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$from = $member['email'];
			$fromName = $member['firstname'] . " " . $member['lastname'];
		}
	}

	$qry = "SELECT B.email, B.firstname, B.member_id FROM {$_SESSION['DB_PREFIX']}userroles A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
			"ON B.member_id = A.memberid " .
			"WHERE A.roleid = '$role' ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], $from, $fromName, $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(), $attachments);
			
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			sendMessage($subject, $message, $member['member_id']);
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
}

function sendTeamMessage($id, $subject, $message, $footer = "") {
	$qry = "SELECT C.member_id, C.email, C.firstname " .
			"FROM {$_SESSION['DB_PREFIX']}members A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members C " .
			"ON C.teamid = A.teamid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}userroles D " .
			"ON D.memberid = C.member_id " .
			"AND D.roleid = 'TEAMLEADER' " .
			"WHERE A.member_id = $id ";
	$result = mysql_query($qry);
	

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], 'support@iafricatranscriptions.co.za', 'I Africa Transcriptions (PTY) LTD', $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(). $footer);
			
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			sendMessage($subject, $message, $id);
		}
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
	
//	sendRoleMessage("ADMIN", $subject, $message);
//	sendUserMessage($id, $subject, $message, $footer);
}
	
function endsWith( $str, $sub ) {
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

function isAuthenticated() {
	return ! (!isset($_SESSION['SESS_MEMBER_ID']) || (trim($_SESSION['SESS_MEMBER_ID']) == ''));
}

function sendUserMessage($id, $subject, $message, $footer = "", $attachments = array(), $action = "") {
	$qry = "SELECT B.email, B.firstname FROM {$_SESSION['DB_PREFIX']}members B " .
	"WHERE B.member_id = $id ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], 'support@iafricatranscriptions.co.za', 'I Africa Transcriptions (PTY) LTD', $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(). $footer, $attachments);
				
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
				
			sendMessage($subject, $message, $id, $action);
		}

	} else {
		logError($qry . " - " . mysql_error());
	}

	if (!empty($error)) echo $error;
}

function sendCourtMessage($id, $subject, $message, $footer = "", $attachments = array(), $action = "") {
	$qry = "SELECT B.email, B.firstname FROM {$_SESSION['DB_PREFIX']}contacts B " .
	"WHERE B.courtid = $id ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], 'support@iafricatranscriptions.co.za', 'I Africa Transcriptions (PTY) LTD', $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(). $footer, $attachments);
				
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
				
			sendMessage($subject, $message, $id, $action);
		}

	} else {
		logError($qry . " - " . mysql_error());
	}

	if (!empty($error)) echo $error;
}

function sendMessage($subject, $message, $id, $action = "") {
	$message = mysql_escape_string($message);
	$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}messages " .
			"(from_member_id, to_member_id, subject, message, createddate, status, action, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
			"VALUES " .
			"(1, ". $id . ", '$subject', '$message', NOW(), 'N', '$action', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ") ";
	
	if (! mysql_query($qry)) {
		logError($qry . " - " . mysql_error());
	}
}

function addAuditLog($table, $type, $id) {
	$memberid = getLoggedOnMemberID();
	$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}caseauditlogs (" .
			"tablename, pkvalue, type, auditmemberid, auditdate, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid" .
			") " .
			"VALUES " .
			"(" .
			"'$table', $id, '$type', $memberid, NOW(), NOW(), $memberid, NOW(), $memberid" .
			")";
	$result = mysql_query($qry);

	if (! $result) {
		logError($qry . " - " . mysql_error());
	}			
}

function sendInternalUserMessage($id, $subject, $message, $footer = "", $attachments = array(), $action = "") {
	$from = "support@iafricatranscriptions.co.za";
	$fromName = "Support - I Africa Transcriptions (PTY) LTD";
	$qry = "SELECT B.email, B.firstname, B.lastname FROM {$_SESSION['DB_PREFIX']}members B " .
			"WHERE B.member_id = " . getLoggedOnMemberID();
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$from = $member['email'];
			$fromName = $member['firstname'] . " " . $member['lastname'];
		}
	}

	$qry = "SELECT B.email, B.firstname FROM {$_SESSION['DB_PREFIX']}members B " .
			"WHERE B.member_id = $id ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], $from, $fromName, $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(). $footer, $attachments);
			
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			sendMessage($subject, $message, $id, $action);
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
}

function sendInternalTypistMessage($id, $subject, $message, $footer = "", $attachments = array(), $action = "") {
	$from = "support@iafricatranscriptions.co.za";
	$fromName = "Support - I Africa Transcriptions (PTY) LTD";
	$qry = "SELECT B.email, B.fullname FROM {$_SESSION['DB_PREFIX']}members B " .
		   "WHERE B.member_id = $id";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer(
					$member['email'], 
					$from, 
					$fromName, 
					$subject, 
					getEmailHeader() . "<h4>Hi,</h4><p>" . $message . "</p>" . getEmailFooter(). $footer, 
					$attachments
				);
				
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
				
			sendMessage($subject, getEmailHeader() . "<h4>Hi,</h4><p>" . $message . "</p>" . getEmailFooter(). $footer, $id, $action);
		}

	} else {
		logError($qry . " - " . mysql_error());
	}

	if (!empty($error)) echo $error;
}

function createCombo($id, $value, $name, $table, $where = " ", $required = true, $isarray = false, $attributeArray = array(), $blank = true) {
	
	if (! $required) {
		echo "<select id='" . $id . "' ";
	
	} else {
		echo "<select required='true' id='" . $id . "' ";
	}
	
	foreach ($attributeArray as $i => $val) {
	    echo "$i='$val' ";
	}
	
	if (! $isarray) {
		echo "name='" . $id . "'>";

	} else {
		echo "name='" . $id . "[]'>";
	}
	
	createComboOptions($value, $name, $table, $where, $blank);
	
	echo "</select>";
}
	


function createComboOptions($value, $name, $table, $where = " ", $blank = true) {
	if ($blank) {
		echo "<option value='0'></option>";
	}
		
	$qry = "SELECT A.* " .
			"FROM $table A " .
			$where . " " . 
			"ORDER BY A.$name";
	$result = mysql_query(getFilteredData($qry));
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			echo "<option value=" . $member[$value] . ">" . $member[$name] . "</option>";
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
}
	
function escape_notes($notes) {
	return str_replace("\r", "", str_replace("'", "\\'", str_replace("\n", "\\n", str_replace("\"", "\\\"", str_replace("\\", "\\\\", $notes)))));
}

function isUserAccessPermitted($action, $description = "") {
	require_once("constants.php");
	
	if ($description == "") {
		$desc = ActionConstants::getActionDescription($action);
		
	} else {
		$desc = $description;
	}
	
	$pageid = $_SESSION['pageid'];
	$found = 0;
	$actionid = 0;
	$qry = "SELECT A.id " .
			"FROM {$_SESSION['DB_PREFIX']}applicationactions A  " .
			"WHERE A.pageid = $pageid " .
			"AND A.code = '$action'";
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$found = 1;
			$actionid = $member['id'];
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if ($found == 0) {
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}applicationactions (pageid, code, description, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUES($pageid, '$action', '$desc', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
		
		$actionid = mysql_insert_id();
		
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}applicationactionroles (actionid, roleid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUES($actionid, 'PUBLIC', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
	}
	
	$found = 0;
	$qry = "SELECT A.* " .
			"FROM {$_SESSION['DB_PREFIX']}applicationactionroles A  " .
			"WHERE A.actionid = $actionid " .
			"AND A.roleid IN (" . ArrayToInClause($_SESSION['ROLES']) . ")";
	$result = mysql_query($qry);

	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$found = 1;
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
		
	return $found == 1;
}

function ArrayToInClause($arr) {
	$count = count($arr);
	$str = "";
	
	for ($i = 0; $i < $count; $i++) {
		if ($i > 0) {
			$str = $str . ", ";
		}
		
		$str = $str . "\"" . $arr[$i] . "\"";
	}
	
	return $str;
}

function isUserInRole($roleid) {
	for ($i = 0; $i < count($_SESSION['ROLES']); $i++) {
		if ($roleid == $_SESSION['ROLES'][$i]) {
			return true;
		}
	}
	
	return false;
}

function lastIndexOf($string, $item) {
	$index = strpos(strrev($string), strrev($item));

	if ($index) {
		$index = strlen($string) - strlen($item) - $index;
		
		return $index;
		
	} else {
		return -1;
	}
}

function getSiteConfigData() {
	return $_SESSION['SITE_CONFIG'];
}

function redirectWithoutRole($role, $location) {
	start_db();
	initialise_db();
	
	if (! isUserInRole($role)) {
		header("location: $location");
	}
}

function getEmailHeader() {
	return "<img src='" . getSiteConfigData()->domainurl . "/images/SmallLogo.png' />";
}

function getEmailFooter() {
	return getSiteConfigData()->emailfooter;
}

function getLoggedOnMemberID() {
	start_db();
	
	if (! isset($_SESSION['SESS_MEMBER_ID'])) {
		return 0;
	}
	
	return $_SESSION['SESS_MEMBER_ID'];
}

function authenticate() {
	start_db();
	initialise_db();
	
	if (! isAuthenticated()) {
		header("location: system-login.php?callback=" . base64_encode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
		exit();
	}
}

function networkdays($s, $e, $holidays = array()) {
    // If the start and end dates are given in the wrong order, flip them.    
    if ($s > $e)
        return networkdays($e, $s, $holidays);

    // Find the ISO-8601 day of the week for the two dates.
    $sd = date("N", $s);
    $ed = date("N", $e);

    // Find the number of weeks between the dates.
    $w = floor(($e - $s)/(86400*7));    # Divide the difference in the two times by seven days to get the number of weeks.
    if ($ed >= $sd) { $w--; }        # If the end date falls on the same day of the week or a later day of the week than the start date, subtract a week.

    // Calculate net working days.
    $nwd = max(6 - $sd, 0);    # If the start day is Saturday or Sunday, add zero, otherewise add six minus the weekday number.
    $nwd += min($ed, 5);    # If the end day is Saturday or Sunday, add five, otherwise add the weekday number.
    $nwd += $w * 5;        # Add five days for each week in between.

    // Iterate through the array of holidays. For each holiday between the start and end dates that isn't a Saturday or a Sunday, remove one day.
    foreach ($holidays as $h) {
        $h = strtotime($h);
        if ($h > $s && $h < $e && date("N", $h) < 6)
            $nwd--;
    }

    return $nwd;
}

function logError($description, $kill = true) {
	if ($kill) {
		mysql_query("ROLLBACK");
	}
	
	if (isset($_SESSION['pageid'])) {
		$pageid = $_SESSION['pageid'];
		
	} else {
		$pageid = 1;
	}
	
	$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}errors (pageid, memberid, description, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUES ($pageid, " . getLoggedOnMemberID() . ", '" . mysql_escape_string($description) . "', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
	$result = mysql_query($qry);
	
	if ($kill) {
		die($description);
	}
}

function convertStringToDate($str) {
	if (trim($str) == "") {
		return "";
	}
	
	return substr($str, 6, 4 ) . "-" . substr($str, 3, 2 ) . "-" . substr($str, 0, 2 );
}

function cms() {
	$pageid = $_SESSION['pageid'];
	$qry = "SELECT content FROM {$_SESSION['DB_PREFIX']}pages " .
			"WHERE pageid = $pageid";
	$result = mysql_query($qry);

	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			echo $member['content'];
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
}


function week_start_date($wk_num, $yr, $first = 1, $format = 'Y,m,d') {
	$dt = date(strtotime($yr . '/0/1'));
	$dt = strtotime('+' . ($wk_num - 1) . ' weeks', $dt);
	
	return $dt;
}

function createUserCombo($id, $where = " ", $required = true, $isarray = false, $uniqueclass = "") {
	$qry = "";
	
	if (! $isarray) {
		echo "<select class='$uniqueclass' " . ($required == true ? "required='true'" : "") . " id='" . $id . "'  name='" . $id . "'>";

	} else {
		echo "<select class='$uniqueclass' " . ($required == true ? "required='true'" : "") . " id='" . $id . "'  name='" . $id . "[]'>";
	}
	
	echo "<option value='0'></option>";
		
	if (trim($where) != "") {
		$qry = "SELECT A.member_id, A.firstname, A.lastname " .
				"FROM {$_SESSION['DB_PREFIX']}members A " .
				$where . " " .
				"AND A.status = 'Y' " . 
				"ORDER BY A.firstname, A.lastname";
		
	} else {
		$qry = "SELECT A.member_id, A.firstname, A.lastname " .
				"FROM {$_SESSION['DB_PREFIX']}members A " .
				"WHERE A.status = 'Y' " . 
				"ORDER BY A.firstname, A.lastname";
	}
	
	$result = mysql_query($qry );
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			echo "<option value=" . $member['member_id'] . ">" . $member['firstname'] . " " . $member['lastname'] . "</option>";
		}
		
	} else {
		logError($qry  . " - " . mysql_error());
	}
	?>
	
	</select>
	<?php
}

function createTypistCombo($id, $where = " ", $required = true, $isarray = false, $uniqueclass = "") {
	$qry = "";
	
	if (! $isarray) {
		echo "<select class='$uniqueclass' " . ($required == true ? "required='true'" : "") . " id='" . $id . "'  name='" . $id . "'>";

	} else {
		echo "<select class='$uniqueclass' " . ($required == true ? "required='true'" : "") . " id='" . $id . "'  name='" . $id . "[]'>";
	}
	
	echo "<option value='0'></option>";
		
	if (trim($where) != "") {
		$qry = "SELECT A.member_id, A.firstname, A.lastname " .
				"FROM {$_SESSION['DB_PREFIX']}members A " .
				$where . " " .
				"AND A.status = 'Y' " .
				"AND A.member_id in (SELECT AA.memberid FROM {$_SESSION['DB_PREFIX']}userroles AA WHERE AA.roleid = 'TYPIST') " . 
				"ORDER BY A.firstname, A.lastname";
		
	} else {
		$qry = "SELECT A.member_id, A.firstname, A.lastname " .
				"FROM {$_SESSION['DB_PREFIX']}members A " .
				"WHERE A.status = 'Y' " . 
				"AND A.member_id in (SELECT AA.memberid FROM {$_SESSION['DB_PREFIX']}userroles AA WHERE AA.roleid = 'TYPIST') " . 
				"ORDER BY A.firstname, A.lastname";
	}
	
	$result = mysql_query($qry );
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			echo "<option value=" . $member['member_id'] . ">" . $member['firstname'] . " " . $member['lastname'] . "</option>";
		}
		
	} else {
		logError($qry  . " - " . mysql_error());
	}
	?>
	
	</select>
	<?php
}


function createCourtCombo($id, $where = " ", $required = true, $isarray = false, $uniqueclass = "") {
	$qry = "";

	if (! $isarray) {
		echo "<select class='$uniqueclass' " . ($required == true ? "required='true'" : "") . " id='" . $id . "'  name='" . $id . "'>";

	} else {
		echo "<select class='$uniqueclass' " . ($required == true ? "required='true'" : "") . " id='" . $id . "'  name='" . $id . "[]'>";
	}

	echo "<option value='0'></option>";
	echo "<option disabled>_______________ Courts _______________</option>";
	
	$qry = "SELECT A.id, A.vatapplicable, A.name " .
			"FROM {$_SESSION['DB_PREFIX']}courts A " .
			$where . " " .
			"ORDER BY A.vatapplicable, A.name";

	$result = mysql_query(getFilteredData($qry));

	if ($result) {
		$vatapplicable = "";
		
		while (($member = mysql_fetch_assoc($result))) {
			if ($vatapplicable != $member['vatapplicable'] && $vatapplicable != "") {
				echo "<option disabled>____________ Private Clients ____________</option>";
			}
			
			$vatapplicable = $member['vatapplicable'];
			
			echo "<option value=" . $member['id'] . ">" . $member['name'] . "</option>";
		}
	
	} else {
		logError($qry  . " - " . mysql_error());
	}
	?>
	
	</select>
	<?php
}

function createContactCombo($id, $where = " ", $required = true, $isarray = false, $uniqueclass = "") {
	$qry = "";
	
	if (! $isarray) {
		echo "<select class='$uniqueclass' " . ($required == true ? "required='true'" : "") . " id='" . $id . "'  name='" . $id . "'>";

	} else {
		echo "<select class='$uniqueclass' " . ($required == true ? "required='true'" : "") . " id='" . $id . "'  name='" . $id . "[]'>";
	}
	
	echo "<option value='0'></option>";
		
	$qry = "SELECT A.id, A.firstname, A.lastname " .
			"FROM {$_SESSION['DB_PREFIX']}contacts A " .
			$where . " " .
			"ORDER BY A.firstname, A.lastname";
	
	$result = mysql_query($qry );
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			echo "<option value=" . $member['id'] . ">" . $member['firstname'] . " " . $member['lastname'] . "</option>";
		}
		
	} else {
		logError($qry  . " - " . mysql_error());
	}
	?>
	
	</select>
	<?php
}

function login($login, $password, $redirect = true) {
	//Array to store validation errors
	$errmsg_arr = array();
	
	//Validation error flag
	$errflag = false;
	unset($_SESSION['LOGIN_ERRMSG_ARR']);
	unset($_SESSION['ERR_USER']);
	unset($_SESSION['MENU_CACHE']);
			
	//Function to sanitize values received from the form. Prevents SQL injection
	//Sanitize the POST values
	$login = clean($login);
	$password = clean($password);
	
	//Input Validations
	if($login == '') {
		$errmsg_arr[] = 'Login ID missing';
		$errflag = true;
	}
	
	if($password == '') {
		$errmsg_arr[] = 'Password missing';
		$errflag = true;
	}
	
	//Create query
	$qry = "SELECT DISTINCT A.* " .
		   "FROM {$_SESSION['DB_PREFIX']}members A " .
		   "WHERE A.login = '$login' " .
		   "AND A.passwd = '" . md5($password) . "' " .
		   	"AND A.accepted = 'Y'";
	$result = mysql_query($qry);
	
	//Check whether the query was successful or not
	if($result) {
		if(mysql_num_rows($result) == 1) {
			//Login Successful
			session_regenerate_id();
			$member = mysql_fetch_assoc($result);
			
			$_SESSION['SESS_MEMBER_ID'] = $member['member_id'];
			$_SESSION['SESS_FIRST_NAME'] = $member['firstname'];
			$_SESSION['SESS_LAST_NAME'] = $member['lastname'];
			$_SESSION['SESS_PROVINCEID'] = $member['provinceid'];
			
			$qry = "SELECT * FROM {$_SESSION['DB_PREFIX']}userroles WHERE memberid = " . $_SESSION['SESS_MEMBER_ID'] . "";
			$result=mysql_query($qry);
			$index = 0;
			$status = null;
			
			$arr = array();
			$arr[$index++] = "PUBLIC";
			
			//Check whether the query was successful or not
			if($result) {
				while($member = mysql_fetch_assoc($result)) {
					$arr[$index++] = $member['roleid'];
				}
				
			} else {
				logError('Failed to connect to server: ' . mysql_error());
			}
			
			$_SESSION['ROLES'] = $arr;
			
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}loginaudit " .
					"(" .
					"memberid, timeon, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid" .
					") " .
					"VALUES " .
					"(" .
					$_SESSION['SESS_MEMBER_ID'] . ", NOW(), NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . "" .
					")";
			$auditresult = mysql_query($qry);
			$auditid = mysql_insert_id();
			
			$_SESSION['SESS_LOGIN_AUDIT'] = $auditid;
			
			if (! $auditresult) {
				logError("$qry - " . mysql_error());
			}
			
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}members SET " .
					"loginauditid = $auditid, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE member_id = " .
					$_SESSION['SESS_MEMBER_ID'];
			$auditresult = mysql_query($qry);
			
			if (! $auditresult) {
				logError("$qry - " . mysql_error());
			}
	
			//Create query
			$qry = "SELECT lastschedulerun " .
				   "FROM {$_SESSION['DB_PREFIX']}siteconfig A " .
				   "WHERE (lastschedulerun <= (DATE_ADD(CURDATE(), INTERVAL -" . getSiteConfigData()->runscheduledays . " DAY)) OR lastschedulerun IS NULL) ";
			$result = mysql_query($qry);
			
			//Check whether the query was successful or not
			if ($result) {
				if(mysql_num_rows($result) == 1) {
					require_once("runalerts.php");
				}
			}
			
			if ($redirect) {
				header("location: index.php");
				exit();
			}
			
		} else {
			//If there are input validations, redirect back to the login form
			if (! $errflag) {
//				$errmsg_arr[] = "Login not found / Not active.<br>Please register or contact portal support";
				$errmsg_arr[] = "Invalid login";
			}
			
			$_SESSION['LOGIN_ERRMSG_ARR'] = $errmsg_arr;
			
			//Login failed
			header("location: system-login.php?session=" . urlencode($_GET['session']));
			exit();
		}
		
	}else {
		logError("Query failed");
	}
}

function logout() {
	start_db();
									
	if (isAuthenticated()) {
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}loginaudit SET " .
				"timeoff = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
				"WHERE id = " . $_SESSION['SESS_LOGIN_AUDIT'] . "";
		$result = mysql_query($qry);
	}
	
	session_unset();
	
	$_SESSION['ROLES'][] = 'PUBLIC';
}

function clean($str) {
	$str = @trim($str);
	if(get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	return mysql_real_escape_string($str);
}
	
function cache_function($functionname, $arguments = array()) {
//			$stti = microtime(true);
	$encoded = md5(json_encode($arguments));
	$cachekey = 'FNC_CACHE_' . $functionname . "_" . $encoded;
	
	if (! isset($_SESSION[$cachekey]) || $_SESSION['CACHING'] == "false") {
		ob_start(); //Turn on output buffering 
		
		$functionname($arguments);
		
		$_SESSION[$cachekey] = ob_get_clean(); 
//		$fiti = number_format(microtime(true) - $stti, 6);
//		logError("<h1>NONE CACHED $cachekey - ELAPSED $fiti:</h1>", false) ;
		
//	} else {
//		$fiti = number_format(microtime(true) - $stti, 6);
//		logError("<h1>CACHED $cachekey - ELAPSED $fiti</h1>", false) ;
	}
	
	echo $_SESSION[$cachekey];
	
}
?>