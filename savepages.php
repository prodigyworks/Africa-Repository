<?php
	//Include database connection details
	require_once('system-db.php');
	require_once("sqlprocesstoarray.php");
	
	start_db();
	
	$pages = explode(", ", $_POST['pages']);
	$casetypistids = explode(", ", $_POST['casetypistids']);
	$pageids = explode(", ", $_POST['pageids']);
	$totalpage = 0;
	$json = array();
	
	for ($i = 0; $i < count($pages); $i++) {
		$page = $pages[$i];
		$pageid = $pageids[$i];
		$casetypistid = $casetypistids[$i];
		$totalpage += $page;
		
		if ($pageid == 0) {
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}typistinvoices " .
					"(casetypistid, pages, createddate, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
					"VALUES " .
					"($casetypistid, $page, NOW(), NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
					
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}casetypist SET " .
					"datebacktooffice = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE id = $casetypistid";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
			
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}cases SET " .
					"datebackfromtypist = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE id = (SELECT caseid FROM {$_SESSION['DB_PREFIX']}casetypist WHERE id = $casetypistid)";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}

			$qry = "SELECT C.j33number, C.casenumber, C.plaintiff, A.pages " .
					"FROM  {$_SESSION['DB_PREFIX']}typistinvoices A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}casetypist B " .
					"ON B.id = A.casetypistid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}cases C " .
					"ON C.id = B.caseid " .
					"WHERE A.casetypistid = $casetypistid";
				 
			$result = mysql_query($qry);
			$j33number = "";
			$casenumber = "";
			$parties = "";

			if (! $result) logError("Error: " . mysql_error());
			
			//Check whether the query was successful or not
			while (($member = mysql_fetch_assoc($result))) {
				$j33number = $member['j33number'];
				$casenumber = $member['casenumber'];
				$parties = $member['plaintiff'];
			}
			
			for ($ix = 0; $ix < count($_POST["notificationid"]); $ix++) {
				$description = "<h3>Typist Invoice Upload.</h3><table>";
				$description .= "<tr><td><b>J33 Number : </b></td><td>$j33number</td></tr>";
				$description .= "<tr><td><b>Case Number : </b></td><td>$casenumber</td></tr>";
				$description .= "<tr><td><b>Parties : </b></td><td>$parties</td></tr>";
				$description .= "<tr><td><b>Pages : </b></td><td>$page</td></tr>";
				$description .= "</table><h4>Invoice has been uploaded by " . GetUserName() . "</h4>";
				
				sendInternalUserMessage($_POST["notificationid"][$ix], "Typist Invoice", $description);
			}					
			
		} else {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}typistinvoices SET " .
					"pages = $page, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE id = $pageid";
		
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry);
			} 
		}
	}
	
	array_push($json, array("pages" => $totalpage));
	
	echo json_encode($json); 
?>