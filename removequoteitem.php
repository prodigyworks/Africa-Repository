<?php
	//Include database connection details
	require_once('system-db.php');
	require_once("sqlprocesstoarray.php");
	
	start_db();
	
	$quoteid = $_POST['quoteid'];
	$id = $_POST['id'];
	
	$qry = "SELECT caseid " .
			"FROM {$_SESSION['DB_PREFIX']}quotes B " .
			"WHERE B.id = $quoteid";
	$result = mysql_query($qry);

	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$caseid = $member['caseid'];
			
			addAuditLog("Q", "U", $caseid);
		}
	}
	
	$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}quoteitems " .
			"WHERE id = $id";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}quotes SET " .
			"total = (SELECT SUM(B.total) FROM {$_SESSION['DB_PREFIX']}quoteitems B WHERE B.quoteid = $quoteid), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
			"WHERE id = $quoteid";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
	
	$qry = "SELECT A.*, C.total AS headertotal, B.name  " .
			"FROM {$_SESSION['DB_PREFIX']}quoteitems A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}quotes C " .
			"ON C.id = A.quoteid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates B " .
			"ON B.id = A.templateid " .
			"WHERE A.quoteid = $quoteid " .
			"ORDER BY A.id";
	
	$json = new SQLProcessToArray();
	
	echo json_encode($json->fetch($qry));
?>