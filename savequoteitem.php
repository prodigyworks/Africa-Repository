<?php
	//Include database connection details
	require_once('system-db.php');
	require_once("sqlprocesstoarray.php");
	
	start_db();
	
	$quoteid = $_POST['quoteid'];
	$id = $_POST['id'];
	$qty = ($_POST['qty']);
	$unitprice = ($_POST['unitprice']);
	$vatrate = $_POST['vatrate'];
	$vat = $_POST['vat'];
	$total = $_POST['total'];
	$templateid = ($_POST['templateid']);
	
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
	
	if ($id == "") {
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}quoteitems " .
				"(quoteid, qty, unitprice, vatrate, vat, total, " .
				"templateid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
				"VALUES " .
				"($quoteid, '$qty', '$unitprice', $vatrate, '$vat', $total, " .
				"'$templateid', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
		
	} else {
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}quoteitems SET " .
				"qty = '$qty', " .
				"unitprice = '$unitprice', " .
				"vatrate = '$vatrate', " .
				"vat = '$vat', " .
				"total = $total, " .
				"templateid = '$templateid', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
				"WHERE id = $id";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
	}
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}quotes SET " .
			"total = (SELECT SUM(B.total) FROM {$_SESSION['DB_PREFIX']}quoteitems B WHERE B.quoteid = $quoteid), " .
			"depositrequired = ((SELECT SUM(B.total) FROM {$_SESSION['DB_PREFIX']}quoteitems B WHERE B.quoteid = $quoteid)), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
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