<?php
	require_once("system-header.php");
?>
<form id="reportform" class="entryform reportform" name="reportform" method="POST" action="recalculateconfirm.php">
	<table>
		<tr>
			<td>
				Date From
			</td>
			<td>
				<input class="datepicker" id="datefrom" name="datefrom" />
			</td>
		</tr>
		<tr>
			<td>
				Date To
			</td>
			<td>
				<input class="datepicker" id="dateto" name="dateto" />
			</td>
		</tr>
		<tr>
			<td>
				Status
			</td>
			<td>
				<SELECT id="status" name="status">
					<OPTION value="">All</OPTION>
					<OPTION value="Y">Paid</OPTION>
					<OPTION value="N">Outstanding</OPTION>
				</SELECT>
			</td>
		</tr>
		<tr>
			<td>
				Court / Client
			</td>
			<td>
				<?php createCombo("courtid", "id", "name", "{$_SESSION['DB_PREFIX']}courts", "", false); ?>
			</td>
		</tr>
		<tr>
			<td>
				Percentage Increase
			</td>
			<td>
				<input id="percentage" name="percentage" required="true" width='30px' />
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;
			</td>
			<td>
				<a class="link1" href="javascript: runreport();"><em><b>Re-calculate</b></em></a>
			</td>
		</tr>
	</table>	
</form>
<script>
	function runreport(e) {
		if (verifyStandardForm("#reportform")) {
			$('#reportform').submit();
		}
			
		e.preventDefault();
	}
</script>
<?php
	require_once("system-footer.php");
?>
