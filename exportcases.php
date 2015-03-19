<?php 
	include("system-header.php"); 
?>
<script src="js/jquery.multiselect.filter.min.js" type="text/javascript"></script>
<script src="js/jquery.multiselect.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.multiselect.css" />
<link rel="stylesheet" type="text/css" href="css/jquery.multiselect.filter.css" />
<form class="contentform" method="post" action="exportcasesdata.php">
	<label>Courts</label>
<?php
	$qry = "SELECT id, name FROM {$_SESSION['DB_PREFIX']}courts WHERE vatapplicable = 'N' ORDER BY name";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
?>
	<select name="courtid[]" multiple="true" size=1 >
		<optgroup label="Courts">
<?php	
	while (($member = mysql_fetch_assoc($result))) {
		echo "<option value='" . $member['id'] . "'>" . $member['name'] . "</option>\n";
	}
?>
		</optgroup>
<?php
	$qry = "SELECT id, name FROM {$_SESSION['DB_PREFIX']}courts WHERE vatapplicable = 'Y'";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
?>
		<optgroup label="Private Clients">
<?php	
	while (($member = mysql_fetch_assoc($result))) {
		echo "<option value='" . $member['id'] . "'>" . $member['name'] . "</option>\n";
	}
?>
		</optgroup>
	</select>
	
	<label>From Date</label>
	<input type="text" id="fromdate" name="fromdate" class="datepicker" />
	
	<label>To Date</label>
	<input type="text" id="todate" name="todate" class="datepicker" />
	<br>
	<br>
	<a href="javascript:  submit();" class="link1"><em><b>Search</b></em></a>
</form>
<script>
	$(document).ready(
			function() {
			   	$("select").multiselect({
			   			multiple: true
				   }); 
			}
		);
	function submit(e) {
		$('.contentform').submit();
		
	}
</script>

<?php include("system-footer.php"); ?>