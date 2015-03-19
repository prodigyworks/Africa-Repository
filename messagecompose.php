<?php
	require_once("system-db.php");
	
	start_db();
	
	if (isset($_POST['mailcommand'])) {
		
		foreach($_POST['ticked'] as $chkval) {
			sendInternalUserMessage($chkval, $_POST['subject'], $_POST['composemessage']);
        }
		
		header("location: messages.php");
	}
	
	require_once("system-header.php");
	require_once("tinymce.php");
	
	include("mailtabstrip.php");
?>

<SCRIPT>
	function send() {
		$("#mailcommand").val("X");
		$("#frmpost").submit();
	}
	
</SCRIPT>
<div class="viewmessage">
	<form method="POST" id="frmpost" name="frmpost">
		<input type="hidden" name="mailcommand" id="mailcommand" />
		<table class="composetable" cellspacing=4>
			<tr>
				<td valign=top>
					Send To
				</td>
				<td valign=top>
					<div class="userselector">
						<table>
						
						<?php
							$qry = "SELECT member_id, imageid, firstname, lastname " .
									"FROM {$_SESSION['DB_PREFIX']}members " .
									"ORDER BY firstname, lastname";
									 
							$result = mysql_query($qry);
							
							if (! $result) logError("Error: " . mysql_error());
							
							//Check whether the query was successful or not
							if ($result) {
								while (($member = mysql_fetch_assoc($result))) {
								?>
									<tr>
										<td width='16px'>	
											<input type="checkbox" id="ticked" name="ticked[]" class="ticked" value="<?php echo $member['member_id']; ?>" />
										</td>
										<td width='30px'>	
											<?php
												if ($member['imageid'] != null && $member['imageid'] != 0) {
											?>
												<img height=32 src="system-imageviewer.php?id=<?php echo $member['imageid']; ?>" />
											<?php
												}
											?>
										</td>
										<td>	
											<?php echo $member['firstname'] . " " . $member['lastname']; ?>
										</td>
									</tr>	
								<?php
								}
							}
						?>
						</table>
					</div>
				</td>
			</tr>
			<tr>
				<td valign=top>
					Subject
				</td>
				<td valign=top>
					<input type="text" name="subject" id="subject" cols=80 style='width:400px' />
				</td>
			</tr>
			<tr>
				<td valign=top>
					Message
				</td>
				<td valign=top>
					<textarea id="composemessage" name="composemessage" class="tinyMCE"></textarea>
				</td>
			</tr>
			<tr>
				<td valign=top>
					&nbsp;
				</td>
				<td valign=top>
					<a class='link1 rgap5' href="javascript: send()"><em><b>Send</b></em></a>
					<a class='link1' href='messages.php'><em><b>Cancel</b></em></a>
				</td>
			</tr>
		</table>
		<script>
			$(document).ready(
					function() {
						<?php
							if (isset($_GET['id'])) {
								$id = $_GET['id'];
								$qry = "SELECT subject, message, from_member_id " .
										"FROM {$_SESSION['DB_PREFIX']}messages " .
										"WHERE id = $id";
										 
								$result = mysql_query($qry);
								
								if (! $result) logError("Error: " . mysql_error());
								
								//Check whether the query was successful or not
								if ($result) {
									while (($member = mysql_fetch_assoc($result))) {
									?>
										$("#composemessage").val("<br><hr /><?php echo mysql_escape_string($member['message']); ?>");
										$("#composemessage").focus();
										$("#subject").val("Re: <?php echo mysql_escape_string($member['subject']); ?>");
										$(".ticked[value=<?php echo $member['from_member_id']; ?>]").attr("checked", true);
									<?php
									}
								}
							}
							
							if (isset($_GET['forwardid'])) {
								$id = $_GET['forwardid'];
								$qry = "SELECT subject, message, from_member_id " .
										"FROM {$_SESSION['DB_PREFIX']}messages " .
										"WHERE id = $id";
										 
								$result = mysql_query($qry);
								
								if (! $result) logError("Error: " . mysql_error());
								
								//Check whether the query was successful or not
								if ($result) {
									while (($member = mysql_fetch_assoc($result))) {
									?>
										$("#composemessage").val("<?php echo mysql_escape_string($member['message']); ?>");
										$("#composemessage").focus();
										$("#subject").val("<?php echo mysql_escape_string($member['subject']); ?>");
									<?php
									}
								}
							}
						?>
					}
				);
		</script>
	</form>
</div>
<?php
	require_once("system-footer.php");
?>
