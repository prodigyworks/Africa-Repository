<?php
	require('casedetailreportlib.php');
	
	$pdf = new CaseDetailReport( 'P', 'mm', 'A4', $_GET['id']);
	$pdf->Output();
?>