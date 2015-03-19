<?php
	require_once("system-header.php");
?>
<form id="reportform" class="reportform" name="reportform" method="POST" action="statements1report.php" target="_new">
	<table>
		<tr>
			<td>
				Court / Client
			</td>
			<td>
				<?php createCombo("courtid", "id", "name", "{$_SESSION['DB_PREFIX']}courts"); ?>
			</td>
		</tr>
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
				Comments
			</td>
			<td>
				<INPUT type="text" id="comment" name="comment" size=130></TEXTAREA>
			</td>
		</tr>
		<tr>
			<td>
				Export To
			</td>
			<td>
				<SELECT id="mode" name="mode">
					<OPTION value="PDF">PDF</OPTION>
					<OPTION value="EXCEL">Excel</OPTION>
				</SELECT>
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;
			</td>
			<td>
				<a class="link1" href="javascript: runreport();"><em><b>Run Report</b></em></a>
			</td>
		</tr>
	</table>	
</form>
<script>
	function runreport(e) {
		if (! verifyStandardForm('#reportform')) {
			return;
		}

		if ($("#mode").val() == "PDF") {
			$("#reportform").attr("action", "statements1report.php");
					
		} else {
			$("#reportform").attr("action", "statements1reportexcel.php");
		}
		
		$('#reportform').submit();
		
		try {
			e.preventDefault();
			
		} catch (e) {
			
		}
	}
</script>
<?php
	require_once("system-footer.php");
?>
