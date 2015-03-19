<?php
	require_once("system-db.php");
	
	start_db();
	
	$id = $_GET['id'];
	
	if (isset($_POST['mailcommand'])) {
		
		if ($_POST['mailcommand'] == "delete") {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages SET deleted = 'Y', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE id = $id ";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			} 
			
			header("location: messages.php");
			
		} else if ($_POST['mailcommand'] == "reply") {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages SET replied = 'Y', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE id = $id ";
			
			header("location: messagereply.php?id=$id");
		}
		
	} else {
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages SET status = 'R', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE id = $id ";
	
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		} 
	}
	
	require_once("system-header.php");
	
	include("mailtabstrip.php");
?>
<SCRIPT>
	function deleteMessage() {
		postCommand("delete");
	}

	function replyMessage() {
		postCommand("reply");
	}

	function postCommand(command) {
		$("#mailcommand").val(command);
		$("#frmpost").submit();
	}
	
</SCRIPT>
<div class="viewmessage">
	<form method="POST" id="frmpost" name="frmpost">
		<input type="hidden" name="mailcommand" id="mailcommand" />
		<?php
			$qry = "SELECT A.id, A.replied, A.status, A.subject, A.message, A.from_member_id, A.to_member_id, " .
				 	 "DATE_FORMAT(A.createddate, '%m/%d/%Y') AS createddate, A.action, " .
					 "B.firstname AS fromfirstname, B.lastname AS fromlastname, B.imageid AS fromimageid,  " .
					 "C.firstname AS tofirstname, C.lastname AS tolastname, C.imageid AS toimageid  " .
					 "FROM  {$_SESSION['DB_PREFIX']}messages A " .
					 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members C " .
					 "ON C.member_id = A.to_member_id " .
					 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members B " .
					 "ON B.member_id = A.from_member_id " .
					 "WHERE A.id = $id";
					 
			$result = mysql_query($qry);
			
			if (! $result) logError("Error: " . mysql_error());
			
			//Check whether the query was successful or not
			if ($result) {
				while (($member = mysql_fetch_assoc($result))) {
		?>
		<input type="hidden" name="subject" id="subject" value="<?php echo $member['subject']; ?>"/>
		<input type="hidden" name="from_member_id" id="from_member_id" value="<?php echo $member['from_member_id']; ?>"/>
		<input type="hidden" name="message" id="message" value="<?php echo $member['message']; ?>"/>
		<table cellspacing=4>
			<tr>
				<td valign=top>
					<?php
						if ($member['fromimageid'] != null && $member['fromimageid'] != 0) {
					?>
						<img height=64 src="system-imageviewer.php?id=<?php echo $member['fromimageid']; ?>" />
					<?php
						}
					?>
				</td>
				<td>
					<h3><?php echo $member['subject']; ?></h3>
					<h5>From <span><?php echo $member['fromfirstname'] . " " . $member['fromlastname']; ?></span> to You</h3>
					<p>Sent <?php echo $member['createddate']; ?></p>
				</td>
			</tr>
		</table>
		<hr />
		<ul class="toolbar">
			<li><a href="javascript: replyMessage()" /><img height=16 src='images/replied.png' />&nbsp;Reply</a></li>
			<li><a href="messageforward.php?id=<?php echo $id; ?>" /><img height=16 src='images/read.png' />&nbsp;Forward</a></li>
			<li><a href="javascript: deleteMessage()" /><img height=16 src='images/delete.png' />&nbsp;Delete</a></li>
		</ul>
		<hr />
		<div class="messagecontent">
			<?php echo $member['message']; ?>
		</div>
		<?php
				}
			}
		?>
	</form>
</div>
<?php
	require_once("system-footer.php");
?>
