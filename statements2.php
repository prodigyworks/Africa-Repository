<?php
	require_once("system-header.php");
?>
<form id="reportform" class="reportform" name="reportform" method="POST" action="statements2report.php" target="_new">
	<table>
		<tr>
			<td>
				Typist
			</td>
			<td>
				<?php  
					createUserCombo("typistid", " WHERE A.member_id in (SELECT AA.memberid FROM {$_SESSION['DB_PREFIX']}userroles AA WHERE AA.roleid = 'TYPIST') ", true);
				?>
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
			$("#reportform").attr("action", "statements2report.php");
					
		} else {
			$("#reportform").attr("action", "statements2reportexcel.php");
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
