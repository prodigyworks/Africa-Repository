<?php
	/** Error reporting */
	error_reporting(E_ALL);

	include 'system-db.php';
	include 'PHPExcel.php';
	include 'PHPExcel/Writer/Excel2007.php';
	require_once("simple_html_dom.php");
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	
	// Set properties
	$objPHPExcel->getProperties()->setCreator("I Africa Transcriptions (PTY) LTD");
	$objPHPExcel->getProperties()->setLastModifiedBy("I Africa Transcriptions (PTY) LTD");
	$objPHPExcel->getProperties()->setTitle("Typist Statement");
	$objPHPExcel->getProperties()->setSubject("Typist Statement");
	$objPHPExcel->getProperties()->setDescription("Typist Statement");

	$normalLJ = array(
			'font' => array(		'bold' => false),
			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
	);
	
	$normalRJ = array(
			'font' => array(		'bold' => false),
			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
			)
	);

	$boldLJ = array(
			'font' => array(		'bold' => true),
			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
	);
	
	$boldRJ = array(
			'font' => array(		'bold' => true),
			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
			)
	);
	
	$boldCJ = array(
			'font' => array(		'bold' => true),
			'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
			)
	);

	$topBorder = array(
			'borders' => array(
					'top' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
					)
			)
	);

	$rightBorder = array(
			'borders' => array(
					'right' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
					)
			)
	);

	$leftBorder = array(
			'borders' => array(
					'left' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
					)
			)
	);
	
	$topRightBorder = array(
			'borders' => array(
					'top' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
					),
					'right' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				)
	);

	$topLeftBorder = array(
			'borders' => array(
					'top' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
					),
					'left' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
					)
			)
	);
	
	header('Content-type: application/excel');
	header('Content-disposition: attachment; filename=statement.xlsx;');

	start_db();
		
	$qry = "SELECT statementnumber " .
			"FROM {$_SESSION['DB_PREFIX']}quotenumbers";
	
	$result = mysql_query($qry);
		
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$statementnumber = $member['statementnumber'] + 1;
				
			$sql = "UPDATE {$_SESSION['DB_PREFIX']}quotenumbers SET statementnumber = statementnumber + 1, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID();
			$itemresult = mysql_query($sql);
		}
	}
	
	$objPHPExcel->createSheet(NULL, 0);
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
	
	$and = "";
		
	if (isset($_POST['datefrom']) && $_POST['datefrom'] != "") {
		$and .= " AND A.createddate >= '" . convertStringToDate($_POST['datefrom']) . "' ";
	}
		
	if (isset($_POST['dateto']) && $_POST['dateto'] != "") {
		$and .= " AND A.createddate <= '" . convertStringToDate($_POST['dateto']) . "' ";
	}
		
	if (isset($_POST['typistid']) && $_POST['typistid'] != "0") {
		$and .= " AND AA.typistid = " . $_POST['typistid'] . " ";
	}
	
	$sql = "SELECT A.*, DATE_FORMAT(AA.datebacktooffice, '%d/%m/%Y') AS datebacktooffice, " .
			"DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate, " .
			"(SELECT SUM(VV.pages) FROM {$_SESSION['DB_PREFIX']}typistinvoices VV INNER JOIN {$_SESSION['DB_PREFIX']}casetypist VW ON VW.id = VV.casetypistid WHERE VW.caseid = B.id) AS casepages, " .
			"(SELECT SUM(XV.pages) FROM {$_SESSION['DB_PREFIX']}casetypistsessions XV INNER JOIN {$_SESSION['DB_PREFIX']}casetypist XW ON XW.id = XV.casetypistid WHERE XW.caseid = B.id) AS casesessionpages, " .
			"(SELECT SUM(BII.qty) FROM {$_SESSION['DB_PREFIX']}invoiceitems BII WHERE BII.invoiceid = BI.id) AS invoicedpages, " .
			"BI.invoicenumber AS clientinvoicenumber, BI.total, F.name AS ratename, F.typistprice, B.transcripttype, B.j33number, B.casenumber, D.address AS courtaddress, D.name AS courtname, D.telephone AS courttelephone, D.fax AS courtfax, D.email AS courtemail, " .
			"AAC.pages AS sessionpages, AAC.sessionid, D.accountnumber, D.vatapplicable, B.plaintiff, E.name AS provincename, G.firstname, G.lastname, G.email, G.landline " .
			"FROM {$_SESSION['DB_PREFIX']}typistinvoices A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}casetypist AA " .
			"ON AA.id = A.casetypistid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}casetypistsessions AAC " .
			"ON AAC.casetypistid = AA.id " .
			"AND AAC.pages != 0 " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
			"ON B.id = AA.caseid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices BI " .
			"ON BI.caseid = B.id " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
			"ON D.id = B.courtid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
			"ON E.id = D.provinceid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates F " .
			"ON F.id = B.rate " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members G " .
			"ON G.member_id = AA.typistid " .
			"WHERE 1 = 1 $and " .
			"ORDER BY A.id DESC";
	$result = mysql_query(getFilteredData($sql));
	
	$totalamount = 0;
	$totalpages = 0;
	$totalclientamount = 0;
	$totalclientpages = 0;
	$row = 12;
	
	if ($result) {
		$first = true;
		
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(8);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
		
		
		
		while (($member = mysql_fetch_assoc($result))) {
			$id = $member['id'];
			
			if ($first) {
				$first = false;
						
				$objPHPExcel->getActiveSheet()->setTitle($member['firstname'] . " " . $member['lastname']);
				
				for ($i = 2; $i <= 9; $i++) {
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A$i:C$i");
				}
				
				$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($topLeftBorder);
				$objPHPExcel->getActiveSheet()->getStyle('L2')->applyFromArray($topRightBorder);
				$objPHPExcel->getActiveSheet()->getStyle('B2:F2')->applyFromArray($topBorder);
				$objPHPExcel->getActiveSheet()->getStyle('H2:K2')->applyFromArray($topBorder);
				$objPHPExcel->getActiveSheet()->getStyle('G2')->applyFromArray($topRightBorder);
				$objPHPExcel->getActiveSheet()->getStyle('G3')->applyFromArray($rightBorder);
				$objPHPExcel->getActiveSheet()->getStyle('G4')->applyFromArray($rightBorder);
				$objPHPExcel->getActiveSheet()->getStyle('G5')->applyFromArray($rightBorder);
				$objPHPExcel->getActiveSheet()->SetCellValue('A2', 'iAfrica Transcriptions (Pty) LTD');
				$objPHPExcel->getActiveSheet()->SetCellValue('A3', '5th Floor, Schreiner Chambers');
				$objPHPExcel->getActiveSheet()->SetCellValue('A4', '94 Pritchard Street');
				$objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Johannesburg');
				$objPHPExcel->getActiveSheet()->getStyle('A2:A5')->applyFromArray($normalLJ);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A7', $member['firstname'] . " " . $member['lastname']);
				$objPHPExcel->getActiveSheet()->SetCellValue('A8', "Email : " .$member['email']);
				$objPHPExcel->getActiveSheet()->SetCellValue('A9', "Tel : " . $member['landline']);
				$objPHPExcel->getActiveSheet()->getStyle('A7:A9')->applyFromArray($normalLJ);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('D2', "Co Reg : ");
				$objPHPExcel->getActiveSheet()->SetCellValue('D3', "VAT Reg : ");
				$objPHPExcel->getActiveSheet()->SetCellValue('D4', "Tel No :");
				$objPHPExcel->getActiveSheet()->SetCellValue('D5', "Fax No : ");
				$objPHPExcel->getActiveSheet()->getStyle('D2:D5')->applyFromArray($boldLJ);
				
				for ($i = 2; $i <= 5; $i++) {
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("E$i:G$i");
				}
				
				$objPHPExcel->getActiveSheet()->SetCellValue('E2', "1997/00931/07");
				$objPHPExcel->getActiveSheet()->SetCellValue('E3', "4750166706");
				$objPHPExcel->getActiveSheet()->SetCellValue('E4', "(011) 336-1455");
				$objPHPExcel->getActiveSheet()->SetCellValue('E5', "(011) 336-2403");
				$objPHPExcel->getActiveSheet()->getStyle('E2:E5')->applyFromArray($normalLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H2:J2");
				$objPHPExcel->getActiveSheet()->SetCellValue('H2', "TYPIST STATEMENT");
				$objPHPExcel->getActiveSheet()->getStyle('H2')->applyFromArray($boldLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K2:L2");
				$objPHPExcel->getActiveSheet()->SetCellValue('K2', "Statement Date");
				$objPHPExcel->getActiveSheet()->getStyle('K2')->applyFromArray($boldLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H3:J3");
				$objPHPExcel->getActiveSheet()->SetCellValue('H3', "Statement Number :", $member['accountnumber'] . $statementnumber);
				$objPHPExcel->getActiveSheet()->getStyle('H3')->applyFromArray($boldLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K3:L3");
				$objPHPExcel->getActiveSheet()->SetCellValue('K3', date("d-M-Y"));
				$objPHPExcel->getActiveSheet()->getStyle('K3')->applyFromArray($normalLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H4:J4");
				$objPHPExcel->getActiveSheet()->SetCellValue('H4', "Date Range");
				$objPHPExcel->getActiveSheet()->getStyle('H4')->applyFromArray($boldLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K4:L4");
				$objPHPExcel->getActiveSheet()->SetCellValue('K4', $_POST['datefrom'] . " - " . $_POST['dateto']);

				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K5:L5");
				$objPHPExcel->getActiveSheet()->SetCellValue('K5', "Total Amount");
				$objPHPExcel->getActiveSheet()->getStyle('K5')->applyFromArray($boldLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H5:J5");
				$objPHPExcel->getActiveSheet()->SetCellValue('H5', "Total Pages Typed");
				$objPHPExcel->getActiveSheet()->getStyle('H5')->applyFromArray($boldLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A10:G10");
				$objPHPExcel->getActiveSheet()->SetCellValue('A10', "Typist Invoices");
				$objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($boldCJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H10:L10");
				$objPHPExcel->getActiveSheet()->SetCellValue('H10', "Client Invoices");
				$objPHPExcel->getActiveSheet()->getStyle('H10')->applyFromArray($boldCJ);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A11', "Date Back");
				$objPHPExcel->getActiveSheet()->SetCellValue('B11', "Case Number");
				$objPHPExcel->getActiveSheet()->SetCellValue('C11', "Court / J33");
				$objPHPExcel->getActiveSheet()->SetCellValue('D11', "Rate");
				$objPHPExcel->getActiveSheet()->SetCellValue('E11', "P.P.P");
				$objPHPExcel->getActiveSheet()->SetCellValue('F11', "Pages");
				$objPHPExcel->getActiveSheet()->SetCellValue('G11', "Amount");
				$objPHPExcel->getActiveSheet()->SetCellValue('H11', "Date");
				$objPHPExcel->getActiveSheet()->SetCellValue('I11', "Client Invoice No");
				$objPHPExcel->getActiveSheet()->SetCellValue('J11', "Pages Invoiced");
				$objPHPExcel->getActiveSheet()->SetCellValue('K11', "Total Pages Typed");
				$objPHPExcel->getActiveSheet()->SetCellValue('L11', "Amount");
				$objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle('B11')->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle('C11')->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle('D11')->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle('E11')->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle('F11')->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle('G11')->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle('H11')->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle('I11')->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle('J11')->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle('K11')->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle('L11')->applyFromArray($boldRJ);
			}
				
			if ($member['sessionid'] == null) {
				$page = $member['pages'];
				 
			} else {
				$page = $member['sessionpages'];
			}
				
			$totalpages += $page;
			$totalamount += ($member['typistprice'] * $page);
			$totalclientpages += ($member['casesessionpages'] + $member['casepages']);
			$totalclientamount += $member['total'];
				
			$objPHPExcel->getActiveSheet()->SetCellValue("A" . $row, $member['datebacktooffice']);
			$objPHPExcel->getActiveSheet()->SetCellValue("B" . $row, $member['casenumber']);
			$objPHPExcel->getActiveSheet()->SetCellValue("C" . $row, ($member['courtname'] . " / " . $member['j33number']));
			$objPHPExcel->getActiveSheet()->SetCellValue("D" . $row, $member['ratename']);
			$objPHPExcel->getActiveSheet()->SetCellValue("E" . $row, "R " . number_format($member['typistprice'], 2));
			$objPHPExcel->getActiveSheet()->SetCellValue("F" . $row, $page);
			$objPHPExcel->getActiveSheet()->SetCellValue("G" . $row, "R " . number_format($member['typistprice'] * $page, 2));
			$objPHPExcel->getActiveSheet()->SetCellValue("H" . $row, $member['createddate']);
			$objPHPExcel->getActiveSheet()->SetCellValue("I" . $row, $member['clientinvoicenumber']);
			$objPHPExcel->getActiveSheet()->SetCellValue("J" . $row, $member['invoicedpages']);
			$objPHPExcel->getActiveSheet()->SetCellValue("K" . $row, ($member['casesessionpages'] + $member['casepages']));
			$objPHPExcel->getActiveSheet()->SetCellValue("L" . $row, "R " . number_format($member['total'], 2));
			
			$objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('B' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('C' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('D' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('E' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('G' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('H' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('I' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('J' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('K' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('L' . $row)->applyFromArray($normalRJ);
			
			if (($member['casesessionpages'] + $member['casepages']) > $member['invoicedpages']) {
				$objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('B' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('C' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('E' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('G' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('H' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('I' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('J' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('K' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
				$objPHPExcel->getActiveSheet()->getStyle('L' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
			}			
			
			$row++;
		}
	
	} else {
		logError($sql . " - " . mysql_error());
	}
	
	$objPHPExcel->getActiveSheet()->SetCellValue("E" . $row, "Total");
	$objPHPExcel->getActiveSheet()->SetCellValue("F" . $row, $totalpages);
	$objPHPExcel->getActiveSheet()->SetCellValue("G" . $row, "R " . number_format($totalamount, 2));
	$objPHPExcel->getActiveSheet()->getStyle('E' . $row . ":" . 'G' . $row)->applyFromArray($boldRJ);
	
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H6:J6");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K6:L6");
	$objPHPExcel->getActiveSheet()->SetCellValue("H6", $totalpages);
	$objPHPExcel->getActiveSheet()->getStyle('H6')->applyFromArray($normalLJ);
	$objPHPExcel->getActiveSheet()->SetCellValue("K6", "R " . number_format($totalamount, 2));
	$objPHPExcel->getActiveSheet()->getStyle('K6')->applyFromArray($normalLJ);
	
	
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save('php://output');
	
?>