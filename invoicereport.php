<?php
	require('invoicereportlib.php');
	
	$pdf = new InvoiceReport( 'P', 'mm', 'A4', $_GET['id']);
	$pdf->Output();
?>