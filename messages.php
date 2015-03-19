<?php
	require_once("system-db.php");
	
	start_db();
	
	if (isset($_POST['mailcommand'])) {
		$inSQL = "(";
		$first = true;
		
		foreach($_POST['ticked'] as $chkval) {
			if (! $first) {
				$inSQL .= ", ";
				
			} else {
				$first = false;	
			}
			
            $inSQL .= $chkval;
        }
        
        $inSQL .= ")";

		if ($_POST['mailcommand'] == "delete") {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages SET deleted = 'Y', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE id IN $inSQL ";
			
		} else if ($_POST['mailcommand'] == "markread") {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages SET status = 'R', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE id IN $inSQL ";
			
		} else if ($_POST['mailcommand'] == "markunread") {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages SET status = 'N', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE id IN $inSQL ";
		}
		
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		} 
		
		header("location: " . $_SERVER['REQUEST_URI']);
	}
	
	require_once("system-header.php");
	
	include("mailtabstrip.php");
?>

<div class="messageheader">	
	<span>Select </span>
	<a href='javascript: selectAll()'>All</a>, 
	<a href='javascript: selectNone()'>None</a>, 
	<a href='javascript: selectRead()'>Read</a>, 
	<a href='javascript: selectUnread()'>Unread</a> 
	<SELECT id='actions' name="actions">
		<OPTION value="">Actions ...</OPTION>
		<OPTION value="D">Delete</OPTION>
		<OPTION value="R">Mark as read</OPTION>
		<OPTION value="U">Mark as unread</OPTION>
	</SELECT>
</div>
<SCRIPT>
	function selectUnread() {
		$(".mailtable").each(
				function() {
					if ($(this).attr("status") == "R") {
						$(this).find(".ticked").attr("checked", false);
						
					} else {
						$(this).find(".ticked").attr("checked", true);
					}
				}
			);
	}
	
	function selectRead() {
		$(".mailtable").each(
				function() {
					if ($(this).attr("status") == "N") {
						$(this).find(".ticked").attr("checked", false);
						
					} else {
						$(this).find(".ticked").attr("checked", true);
					}
				}
			);
	}
	
	function selectAll() {
		$(".ticked").attr("checked", true);
	}
	
	function selectNone() {
		$(".ticked").attr("checked", false);
	}
	
	function deleteMessages() {
		postCommand("delete");
	}
	
	function markRead() {
		postCommand("markread");
	}
	
	function markUnread() {
		postCommand("markunread");
	}
	
	function postCommand(command) {
		$("#mailcommand").val(command);
		$("#frmpost").submit();
	}
	
</SCRIPT>
<form method="POST" id="frmpost" name="frmpost">
	<input type="hidden" name="mailcommand" id="mailcommand" />
	<table width='100%' cellspacing=4>
	<?php
		if (! isset($_GET['mode']) || $_GET['mode'] == "I") {
			$qry = "SELECT A.id, A.replied, A.status, A.subject, A.message, A.from_member_id, A.to_member_id, " .
				 	 "DATE_FORMAT(A.createddate, '%m/%d/%Y') AS createddate, A.action, " .
					 "B.firstname AS fromfirstname, B.lastname AS fromlastname, B.imageid AS fromimageid,  " .
					 "C.firstname AS tofirstname, C.lastname AS tolastname, C.imageid AS toimageid  " .
					 "FROM  {$_SESSION['DB_PREFIX']}messages A " .
					 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members C " .
					 "ON C.member_id = A.to_member_id " .
					 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members B " .
					 "ON B.member_id = A.from_member_id " .
					 "WHERE A.to_member_id = " . getLoggedOnMemberID() . " " .
					 "AND (A.deleted != 'Y' OR A.deleted IS NULL) " .
					 "ORDER BY A.createddate DESC";
					 
		} else if (isset($_GET['mode']) && $_GET['mode'] == "S") {
			$qry = "SELECT A.id, A.replied, A.status, A.subject, A.message, A.from_member_id, A.to_member_id, " .
				 	 "DATE_FORMAT(A.createddate, '%m/%d/%Y') AS createddate, A.action, " .
					 "B.firstname AS fromfirstname, B.lastname AS fromlastname, B.imageid AS fromimageid,  " .
					 "C.firstname AS tofirstname, C.lastname AS tolastname, C.imageid AS toimageid  " .
					 "FROM  {$_SESSION['DB_PREFIX']}messages A " .
					 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members C " .
					 "ON C.member_id = A.to_member_id " .
					 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members B " .
					 "ON B.member_id = A.from_member_id " .
					 "WHERE A.from_member_id = " . getLoggedOnMemberID() . " " .
					 "AND (A.deleted != 'Y' OR A.deleted IS NULL) " .
					 "ORDER BY A.createddate DESC";
					 
		} else if (isset($_GET['mode']) && $_GET['mode'] == "D") {
			$qry = "SELECT A.id, A.replied, A.status, A.subject, A.message, A.from_member_id, A.to_member_id, " .
				 	 "DATE_FORMAT(A.createddate, '%m/%d/%Y') AS createddate, A.action, " .
					 "B.firstname AS fromfirstname, B.lastname AS fromlastname, B.imageid AS fromimageid,  " .
					 "C.firstname AS tofirstname, C.lastname AS tolastname, C.imageid AS toimageid  " .
					 "FROM  {$_SESSION['DB_PREFIX']}messages A " .
					 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members C " .
					 "ON C.member_id = A.to_member_id " .
					 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members B " .
					 "ON B.member_id = A.from_member_id " .
					 "WHERE A.to_member_id = " . getLoggedOnMemberID() . " " .
					 "AND A.deleted = 'Y' " .
					 "ORDER BY A.createddate DESC";
		}				 
		$result = mysql_query($qry);
		
		if (! $result) logError("Error: " . mysql_error());
		
		//Check whether the query was successful or not
		if ($result) {
			while (($member = mysql_fetch_assoc($result))) {
	?>
		<tr class='mailtable' status='<?php echo $member['status']; ?>'>
			<td>
				<input type="checkbox" id="ticked" name="ticked[]" class="ticked" value="<?php echo $member['id']; ?>" />
			</td>
			<td class='clickable'>
				<?php
				if ($member['replied'] == "Y") {
					echo "<img src='images/replied.png' />";
					
				} else if ($member['status'] == "N") {
					echo "<img src='images/unread.png' />";
					
				} else if ($member['status'] == "R") {
					echo "<img src='images/read.png' />";
				}
				?>
				
			</td>
			<td class='clickable' valign=center>
				<?php
					if ($member['fromimageid'] != null && $member['fromimageid'] != 0) {
				?>
					<img height=32 src="system-imageviewer.php?id=<?php echo $member['fromimageid']; ?>" />
				<?php
					}
				?>
			</td>
			<td class='clickable' valign=top>
				<?php
					if (isset($_GET['mode']) && $_GET['mode'] == "S") {
				?>
				<h4><?php echo $member['tofirstname'] . " " . $member['tolastname']; ?></h4>
				<?php
					} else {
				?>
				<h4><?php echo $member['fromfirstname'] . " " . $member['fromlastname']; ?></h4>
					
				<?php
					}
				?>
				<div><?php echo $member['createddate']; ?></div>
			</td>
			<td class='clickable' valign=top nowrap>
				<h4><?php echo $member['subject']; ?></h4>
				<div style='height:20px; overflow: hidden'><?php echo $member['message']; ?></div>
			</td>
		</tr>
	<?php
			}
		}
	?>
	</table>
	<script>
		$(document).ready(
				function() {
					$(".ticked").change(
							function(e) {
								e.stopPropogation();
							}
						);
					
					$(".clickable").click(
							function() {
								navigate("messageview.php?id=" + $(this).parent().find(".ticked").val());
							}
						);
					
					$("#actions").change(
							function() {
								if ($(this).val() == "D") {
									deleteMessages();
								
								} else if ($(this).val() == "R") {
									markRead();
									
								} else if ($(this).val() == "U") {
									markUnread();
								}
							}
						);
				}
			);
	</script>
</form>
<?php
	require_once("system-footer.php");
?>
