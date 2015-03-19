<?php 
	$message = "";
	
	include("system-header.php");
	
	function confirm() {
		global $message;
		
		$id = $_POST['pk1'];
		$messageid = $_POST['pk2'];
		
		$sql = "SELECT A.weeknumber, A.memberid, A.swapmemberid  " .
				"FROM {$_SESSION['DB_PREFIX']}oncallswap A " .
				"WHERE A.id = $id";
	
		$result = mysql_query($sql);
			
		if ($result) {
			/* Show children. */
			while (($member = mysql_fetch_assoc($result))) {
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}oncallswap " .
					   "SET agreed = 'Y', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					   "WHERE id = $id";
				$itemresult = mysql_query($qry);
				
				if (! $itemresult) {
					logError($qry . " = " . mysql_error());
				}
				
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages " .
					   "SET status = 'R', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					   "WHERE id = $messageid";
				$itemresult = mysql_query($qry);
				
				if (! $itemresult) {
					logError($qry . " = " . mysql_error());
				}
				
				sendInternalUserMessage($member['memberid'], "On Call Swap Request", "Your request for on call cover for week " . $member['weeknumber'] . " has been accepted by " . GetUserName($member['swapmemberid']));
				$message = "Request has been accepted";
			}
		}
	}
	
	function reject() {
		global $message;
		
		$id = $_POST['pk1'];
		$messageid = $_POST['pk2'];
		
		$sql = "SELECT A.weeknumber, A.memberid, A.swapmemberid  " .
				"FROM {$_SESSION['DB_PREFIX']}oncallswap A " .
				"WHERE A.id = $id";
	
		$result = mysql_query($sql);
			
		if ($result) {
			/* Show children. */
			while (($member = mysql_fetch_assoc($result))) {
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}oncallswap " .
					   "SET agreed = 'X', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					   "WHERE id = $id";
				$itemresult = mysql_query($qry);
				
				if (! $itemresult) {
					logError($qry . " = " . mysql_error());
				}
				
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages " .
					   "SET status = 'R', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					   "WHERE id = $messageid";
				$itemresult = mysql_query($qry);
				
				if (! $itemresult) {
					logError($qry . " = " . mysql_error());
				}
				
				sendInternalUserMessage($member['memberid'], "On Call Swap Request", "Your request for on call cover for week " . $member['weeknumber'] . " has been rejected by " . GetUserName($member['swapmemberid']));
				$message = "Request has been rejected";
			}
		}
	}
	
	if (! isset($_POST['pk1'])) {
?>
		<script>
			function confirm() {
				call("confirm", {pk1 : <?php echo $_GET['oncallid']; ?>, pk2: <?php echo $_GET['messageid']; ?>});
			}
			
			function reject() {
				call("reject", {pk1 : <?php echo $_GET['oncallid']; ?>, pk2: <?php echo $_GET['messageid']; ?>});
			}
		</script>
<?php
	
		$id = $_GET['oncallid'];
		$sql = "SELECT A.weeknumber, B.firstname, B.lastname  " .
				"FROM {$_SESSION['DB_PREFIX']}oncallswap A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
				"ON B.member_id = A.memberid " .
				"WHERE A.id = $id";
	
		$result = mysql_query($sql);
			
		if ($result) {
			/* Show children. */
			while (($member = mysql_fetch_assoc($result))) {
?>
		<h1>Confirmation of on call swap</h1>
		<br>
		<h2>User <?php echo $member['firstname'] . " " . $member['lastname']; ?> has requested on call cover for week <?php echo $member['weeknumber']; ?></h2>
		<br>
		<span class="wrapper"><a class='link1 rgap5' href="javascript:confirm();"><em><b>Confirm</b></em></a></span>
		<span class="wrapper"><a class='link1' href="javascript:reject();"><em><b>Reject</b></em></a></span>

<?php
			}
			
		} else {
			logError($sql . " - " . mysql_error());
		}
		
	} else {
		echo "<h1>" . $message . "</h1>";
	}
?>

<?php 
	include("system-footer.php"); 
?>