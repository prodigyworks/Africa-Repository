<?php
	require('system-db.php');
	require('pdfreport.php');
	
	function newPage($pdf) {
		$pdf->AddPage();
		
		$pdf->addHeading( 15, 13, "Cases at Typists : ", date("d/m/Y"));
	    $pdf->SetFont('Arial','', 6);
			
		$cols=array( "Typist"    => 34,
		             "J33 Number"  => 24,
		             "Case Number"  => 24,
		             "Parties"  => 41,
		             "Date Received"  => 24,
		             "Rate"  => 34,
		             "Minutes"  => 24,
		             "Back From Typist"  => 30,
		             "Expecting Date Back From Typist"  => 42);
	
		$pdf->addCols( 20, $cols);
		$cols=array( "Typist"    => "L",
		             "J33 Number"  => "L",	
		             "Case Number"  => "L",
		             "Parties"  => "L",
		             "Date Received"  => "L",
		             "Rate"  => "L",
		             "Minutes"  => "L",
		             "Back From Typist"  => "L",
		             "Expecting Date Back From Typist"  => "L");
		$pdf->addLineFormat( $cols);
		
		return 29;
	}
	
	$pdf = new PDFReport( 'L', 'mm', 'A4' );
	$y = newPage($pdf);
	
	$sql = "SELECT C.j33number, C.casenumber, C.rate, C.time, C.plaintiff, A.pages, D.fullname, E.name AS ratename, " .
			"DATE_FORMAT(B.datebacktooffice, '%d/%m/%Y') AS datebacktooffice," .
			"DATE_FORMAT(C.datereceived, '%d/%m/%Y') AS datereceived," .
			"DATE_FORMAT(C.dataexpectedbackfromtypist, '%d/%m/%Y') AS dataexpectedbackfromtypist," .
			"DATE_FORMAT(B.datefromoffice , '%d/%m/%Y') AS datefromoffice " .
			"FROM  {$_SESSION['DB_PREFIX']}casetypist B " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}typistinvoices A  " .
			"ON A.casetypistid = B.id " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}cases C " .
			"ON C.id = B.caseid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members D " .
			"ON D.member_id = B.typistid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates E " .
			"ON E.id = C.rate " .
			"WHERE B.typistid IN (" . ArrayToInClause($_POST['typistid']) . ") " .
			"AND B.datebacktooffice IS NULL " .
			"ORDER BY D.fullname, C.j33number";
				 
	$result = mysql_query($sql);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$line=array( "Typist"    => $member['fullname'] . " ",
			             "J33 Number"  => $member['j33number'] . " ",
			             "Case Number"  => $member['casenumber'] . " ",
			             "Parties"  => $member['plaintiff'] . " ",
			             "Date Received"  => $member['datereceived'] . " ",
			             "Rate"  => $member['ratename'] . " ",
			             "Minutes"  => $member['time'] . " ",
			             "Back From Typist"  => $member['datebacktooffice'] . " ",
			             "Expecting Date Back From Typist"  => $member['dataexpectedbackfromtypist'] . " "
		             );
		             logError($member['datefromoffice'], false);
			             
			$size = $pdf->addLine( $y, $line );
			$y += $size;
			
			if ($y > 160) {
				$y = newPage($pdf);
			}
		}
		
	} else {
		logError("ERROR:" . $sql . " - " . mysql_error());
	}
	
	$pdf->Output();
?>