<?php
	require_once("system-header.php");
?>
<form id="reportform" class="reportform" name="reportform" method="POST" action="<?php if (isUserInRole("COURTUSER")) echo "courtuserinvoicereportsummary.php"; else echo "invoicereportsummary.php"; ?>" target="_new">
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
					<OPTION value="A">All</OPTION>
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
				<?php 
					if (isUserInRole("COURTUSER")) {
						createCombo("courtid", "id", "name", "{$_SESSION['DB_PREFIX']}allcourts", "WHERE id IN (SELECT courtid FROM {$_SESSION['DB_PREFIX']}usercourts WHERE memberid = " . getLoggedOnMemberID() . ")"); 
						
					} else {
						createCombo("courtid", "id", "name", "{$_SESSION['DB_PREFIX']}allcourts");
					}
				?>
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
