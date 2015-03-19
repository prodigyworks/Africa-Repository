<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	$pageid = $_POST['pageid'];
	$memberid = $_POST['memberid'];
	$column = $_POST['column'];
	$width = $_POST['width'];
	$label = $_POST['label'];
	$headerid = 0;
	$itemid = 0;
	
	$qry = "SELECT A.id " .
			"FROM {$_SESSION['DB_PREFIX']}applicationtables A " .
			"WHERE A.pageid = $pageid " .
			"AND A.memberid = $memberid ";
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$headerid = $member['id'];
		}
	}

	if ($headerid == 0) {
		$result = mysql_query("INSERT INTO {$_SESSION['DB_PREFIX']}applicationtables " .
				"(pageid, memberid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
				"VALUES " .
				"($pageid, $memberid, NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")");
				
		$headerid = mysql_insert_id();
			
		if (! $result) {   
			logError("insert applicationtables:" . mysql_error()); 
		} 
	}	
	
	
	$qry = "SELECT A.id " .
			"FROM {$_SESSION['DB_PREFIX']}applicationtablecolumns A " .
			"WHERE A.headerid = $headerid " .
			"AND A.columnindex = $column ";
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$itemid = $member['id'];
		}
	}

	if ($itemid == 0) {
		$result = mysql_query("INSERT INTO {$_SESSION['DB_PREFIX']}applicationtablecolumns " .
				"(headerid, columnindex, width, label, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
				"VALUES " .
				"($headerid, $column, $width, '$label', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")");
				
		if (! $result) {   
			logError("insert applicationtablecolumns:" . mysql_error()); 
		}
		 
	} else {
		$result = mysql_query("UPDATE {$_SESSION['DB_PREFIX']}applicationtablecolumns " .
				"SET width = $width, " .
				"label = '$label', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
				"WHERE id = $itemid");
				
		if (! $result) {   
			logError("insert applicationtablecolumns:" . mysql_error()); 
		}
	}
?>