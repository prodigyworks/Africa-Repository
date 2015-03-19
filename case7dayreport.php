<?php
	require('case7dayreportlib.php');
	
	$pdf = new Case7DayReport( 'P', 'mm', 'A4');
	$pdf->Output();
?>