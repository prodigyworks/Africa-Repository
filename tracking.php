<?php
	require_once("system-db.php");
	
	start_db();
	
	if (! isAuthenticated()) {
		login("public", "public", false);
	}
	
	require_once("system-header.php");
?>
<form id="trackingform" class="reportform" name="trackingform" method="POST" action="trackingreport.php">
	<table>
		<tr>
			<td>J33 Number</td>
			<td>
				<input type="text" cols=40 id="j33number" name="j33number" />
			</td>
		</tr>
		<tr>
			<td>Case Number</td>
			<td>
				<input type="text" cols=40 id="casenumber" name="casenumber" />
			</td>
		</tr>
		<tr>
			<td>Estimate Number</td>
			<td>
				<input type="text" cols=40 id="quotenumber" name="quotenumber" />
			</td>
		</tr>
		<tr>
			<td>Invoice Number</td>
			<td>
				<input type="text" cols=40 id="invoicenumber" name="invoicenumber" />
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<a class="link1" href="javascript: runreport();"><em><b>Run Report</b></em></a>
			</td>
		</tr>
	</table>	
</form>
<script>
	$(document).ready(
			function() {
				$("#j33number").change(
						function() {
							if ($(this).val() != "") {
								$("#casenumber").val("");
							}
						}
					);
					
				$("#casenumber").change(
						function() {
							if ($(this).val() != "") {
								$("#j33number").val("");
							}
						}
					);
			}
		);
	function runreport(e) {
		$('#trackingform').submit();
		
		if (e)
			e.preventDefault();
	}
</script>
<?php
	require_once("system-footer.php");
?>
