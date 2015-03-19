<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	$expertsonline = 0;
	$myarticles = 0;
	$awaitingpublish = 0;
	$myopenquestions = 0;
	$servicerequests = 0;
	$myansweredquestions = 0;
	$unpublishedquestions = 0;
	
	if (isAuthenticated()) {
		$memberid = $_SESSION['SESS_MEMBER_ID'];
		
		$qry = "SELECT COUNT(*) AS myarticles " .
				"FROM horizon_article " .
				"WHERE memberid = $memberid";
		$result = mysql_query($qry);
		
		if (! $result) {
			die($qry . " = " . mysql_error());
			
		} else {
			while (($member = mysql_fetch_assoc($result))) {
				$myarticles = $member['myarticles'];
			}
		}
		
		$memberid = $_SESSION['SESS_MEMBER_ID'];
		
		$qry = "SELECT COUNT(*) AS myopenquestions " .
				"FROM horizon_question A " .
				"WHERE A.memberid = $memberid " .
				"AND A.id NOT IN (SELECT B.questionid FROM horizon_questionanswers B WHERE B.questionid = A.id)";
		$result = mysql_query($qry);
		
		if (! $result) {
			die($qry . " = " . mysql_error());
			
		} else {
			while (($member = mysql_fetch_assoc($result))) {
				$myopenquestions = $member['myopenquestions'];
			}
		}
		
		$qry = "SELECT COUNT(*) AS servicerequests " .
				"FROM horizon_servicerequest " .
				"WHERE status = 'N' ";
		$result = mysql_query($qry);
		
		if (! $result) {
			die($qry . " = " . mysql_error());
			
		} else {
			while (($member = mysql_fetch_assoc($result))) {
				$servicerequests = $member['servicerequests'];
			}
		}
		
		$qry = "SELECT COUNT(*) AS myansweredquestions " .
				"FROM horizon_question A " .
				"WHERE A.memberid = $memberid " .
				"AND A.id IN (SELECT B.questionid FROM horizon_questionanswers B WHERE B.questionid = A.id)";
		$result = mysql_query($qry);
		
		if (! $result) {
			die($qry . " = " . mysql_error());
			
		} else {
			while (($member = mysql_fetch_assoc($result))) {
				$myansweredquestions = $member['myansweredquestions'];
			}
		}
		
		$qry = "SELECT COUNT(*) AS awaitingpublish " .
				"FROM horizon_article " .
				"WHERE memberid = $memberid " .
				"AND published = 'N'";
		$result = mysql_query($qry);
		
		if (! $result) {
			die($qry . " = " . mysql_error());
			
		} else {
			while (($member = mysql_fetch_assoc($result))) {
				$awaitingpublish = $member['awaitingpublish'];
			}
		}
		
		$qry = "SELECT COUNT(*) AS unpublishedquestions " .
				"FROM horizon_question " .
				"WHERE memberid = $memberid " .
				"AND published = 'N'";
		$result = mysql_query($qry);
		
		if (! $result) {
			die($qry . " = " . mysql_error());
			
		} else {
			while (($member = mysql_fetch_assoc($result))) {
				$unpublishedquestions = $member['unpublishedquestions'];
			}
		}
	}
	
	$qry = "SELECT COUNT(DISTINCT A.member_id) AS expertsonline " .
			"FROM horizon_members A " .
			"INNER JOIN horizon_userroles B " .
			"ON B.memberid = A.member_id " .
			"WHERE A.status = 'Y' " .
			"AND A.lastaccessdate >= (NOW() - INTERVAL 5 MINUTE) " .
			"AND B.roleid = 'CONSULTANT'";
	$result = mysql_query($qry);
	
	if (! $result) {
		die($qry . " = " . mysql_error());
		
	} else {
		while (($member = mysql_fetch_assoc($result))) {
			$expertsonline = $member['expertsonline'];
		}
	}
	
	$qry = "SELECT COUNT(*) AS requestcount " .
			"FROM horizon_chatsession " .
			"WHERE status = 'O' " .
			"AND lastaccessdate >= (NOW() - INTERVAL 5 MINUTE) " .
			"AND responsesessionid IS NULL";
	$result = mysql_query($qry);
	
	if (! $result) {
		die($qry . " = " . mysql_error());
	}
	
	echo "[\n";
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			echo "{\"chatrequests\": \"" . $member['requestcount'] . "\",\n";
			echo " \"servicerequests\": \"" . $servicerequests . "\",\n";
			echo " \"myopenquestions\": \"" . $myopenquestions . "\",\n";
			echo " \"myansweredquestions\": \"" . $myansweredquestions . "\",\n";
			echo " \"myarticles\": \"" . $myarticles . "\",\n";
			echo " \"awaitingpublish\": \"" . $awaitingpublish . "\",\n";
			echo " \"unpublishedquestions\": \"" . $unpublishedquestions . "\",\n";
			echo " \"expertsonline\": \"" . $expertsonline . "\"}";
		}
	}

	echo "\n]\n";
?>


	
