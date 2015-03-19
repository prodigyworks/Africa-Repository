<?php 
	require_once("system-header.php"); 
?>
<script src="js/jquery.multiselect.filter.min.js" type="text/javascript"></script>
<script src="js/jquery.multiselect.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.multiselect.css" />
<link rel="stylesheet" type="text/css" href="css/jquery.multiselect.filter.css" />
<form method="POST" id="manualeditform" action="typistreportlib.php" target="_new">
	<table cellspacing=10 class="entryform">
		<tr>
			<td>Typist</td>
			<td>
				<?php
				createCombo("typistid", "member_id", "fullname", "{$_SESSION['DB_PREFIX']}members", " WHERE member_id IN (SELECT memberid from {$_SESSION['DB_PREFIX']}userroles WHERE roleid = 'TYPIST') ", true, true, array("class" => "multiselect", "size" => "1", "multiple" => "true"), false);
				?>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<a href="javascript: runreport();" class="link1"><em><b>Search</b></em></a>
			</td>
		</tr>
	</table>
</form>
<script>
	function runreport(e) {
		$('#manualeditform').submit();
		
		try {
			e.preventDefault();
			
		} catch (e) {
			
		}
	}
	
	$(document).ready(
			function() {
				 
			   	$(".multiselect").multiselect({
			   			multiple: true
				   }); 
			}
		);
</script>
<?php 
	include("system-footer.php"); 
?>