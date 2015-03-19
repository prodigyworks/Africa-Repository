<?php
	require('creditreportlib.php');
	
	$pdf = new CreditReport( 'P', 'mm', 'A4', $_GET['id']);
	$pdf->Output();
?>