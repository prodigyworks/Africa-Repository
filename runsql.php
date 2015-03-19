<?php
	require_once("sqlprocesstoarray.php");
	require_once('php-sql-parser.php');
	require_once('php-sql-creator.php');
	require_once("system-header.php");
	
	function run() {
		$sql = ($_POST['pk1']);
		echo str_replace("\n", " ", str_replace("\r", "", $sql));
		
		if (stripos($sql, "SELECT", 0) === 0) {
			$json = new SQLProcessToArray();
	?>
	<script>
				$(document).ready(
						function() {
							$("#sqlresults").val("<?php echo str_replace("\"", "\\\"", json_encode($json->fetch($sql))); ?>");
						}
					);
	</script>
	<?php			
			
		} else {
			$result = mysql_query(mysql_escape_string(str_replace("\n", " ", str_replace("\r", "", $sql))));
			
			if (! $result) {
	?>
	<script>
				$(document).ready(
						function() {
							pwAlert("<?php echo str_replace("\n", "\\n", mysql_error()) . "\\n\\n" . str_replace("\n", " ", str_replace("\r", "", $sql)); ?>");
						}
					);
	</script>
	<?php
			}
		}
	}
?>
<script>
	function run() {
		call("run", {pk1: $("#sql").val()});
	}
</script>
<button onclick='run()'>Run</button><br><br>
<textarea id="sql" name="sql" style='width:100%; height:250px'><?php if (isset($_POST['pk1'])) echo $_POST['pk1']; ?></textarea>
<br><br>
<textarea id="sqlresults" name="sqlresults" style='width:100%; height:250px'></textarea>
<?php	
	require_once("system-footer.php");
?>
