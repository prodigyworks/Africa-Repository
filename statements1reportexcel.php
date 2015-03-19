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
// 					'top' => array(
// 							'style' => PHPExcel_Style_Border::BORDER_THIN
// 					)
			)
	);

	$rightBorder = array(
			'borders' => array(
// 					'right' => array(
// 							'style' => PHPExcel_Style_Border::BORDER_THIN
// 					)
			)
	);

	$leftBorder = array(
			'borders' => array(
// 					'left' => array(
// 							'style' => PHPExcel_Style_Border::BORDER_THIN
// 					)
			)
	);
	
	$topRightBorder = array(
			'borders' => array(
// 					'top' => array(
// 							'style' => PHPExcel_Style_Border::BORDER_THIN
// 					),
// 					'right' => array(
// 							'style' => PHPExcel_Style_Border::BORDER_THIN
// 					)
				)
	);

	$topLeftBorder = array(
			'borders' => array(
// 					'top' => array(
// 							'style' => PHPExcel_Style_Border::BORDER_THIN
// 					),
// 					'left' => array(
// 							'style' => PHPExcel_Style_Border::BORDER_THIN
// 					)
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
	
	if (isset($_POST['courtid']) && $_POST['courtid'] != "0") {
		$and .= " AND D.id = " . $_POST['courtid'] . " ";
	}

	$sql = "SELECT A.*, DATE_FORMAT(A.createddate, '%d/%m/%Y') AS paymentdate2, " .
			"DATE_FORMAT(A.paymentdate, '%d/%m/%Y') AS paymentdate, " .
			"B.j33number, B.casenumber, D.address AS courtaddress, D.name AS courtname, D.telephone AS courttelephone, D.fax AS courtfax, D.email AS courtemail, " .
			"D.accountnumber, D.vatapplicable, E.name AS provincename, F.name AS terms, G.firstname, G.lastname, " .
			"O.privatebankingdetails, O.bankingdetails, " .
			"O.address AS officeaddress, O.email AS officeemail, O.telephone, O.contact, O.fax AS officefax, O.name AS officename, " .
			"CASE " .
			"WHEN A.createddate <= (DATE_ADD(CURDATE(), INTERVAL -150 DAY)) THEN '150' " .
			"WHEN A.createddate <= (DATE_ADD(CURDATE(), INTERVAL -120 DAY)) THEN '120' " .
			"WHEN A.createddate <= (DATE_ADD(CURDATE(), INTERVAL -90 DAY)) THEN '90' " .
			"WHEN A.createddate <= (DATE_ADD(CURDATE(), INTERVAL -60 DAY)) THEN '60' " .
			"WHEN A.createddate <= (DATE_ADD(CURDATE(), INTERVAL -30 DAY)) THEN '30' " .
			"ELSE '0' " .
			"END AS since " .
			"FROM {$_SESSION['DB_PREFIX']}invoices A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
			"ON B.id = A.caseid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
			"ON D.id = B.courtid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
			"ON E.id = D.provinceid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}caseterms F " .
			"ON F.id = A.termsid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members G " .
			"ON G.member_id = A.contactid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}offices O " .
			"ON O.id = A.officeid " .
			"WHERE 1 = 1 $and " .
			"ORDER BY A.id DESC";
	$result = mysql_query(getFilteredData($sql));

	$totalamount = 0;
	$totalpages = 0;
	$totalclientamount = 0;
	
	if ($result) {
		$first = true;
		
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(8);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
		
		$row = 8;
		
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
				$objPHPExcel->getActiveSheet()->getStyle('H2:J2')->applyFromArray($topBorder);
				$objPHPExcel->getActiveSheet()->getStyle('G2')->applyFromArray($topRightBorder);
				$objPHPExcel->getActiveSheet()->getStyle('G3')->applyFromArray($rightBorder);
				$objPHPExcel->getActiveSheet()->getStyle('G4')->applyFromArray($rightBorder);
				$objPHPExcel->getActiveSheet()->getStyle('G5')->applyFromArray($rightBorder);
				$objPHPExcel->getActiveSheet()->SetCellValue('A2', 'iAfrica Transcriptions (Pty) LTD');
				$objPHPExcel->getActiveSheet()->SetCellValue('A3', '5th Floor, Schreiner Chambers');
				$objPHPExcel->getActiveSheet()->SetCellValue('A4', '94 Pritchard Street');
				$objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Johannesburg');
				$objPHPExcel->getActiveSheet()->getStyle('A2:A5')->applyFromArray($normalLJ);
				
//				$this->addAddress(" ", $member['courtname'] . "\n" .$member['courtaddress'] . "\n" . "Tel : " . $member['courttelephone'] . "\n" . "Fax : " . $member['courtfax'] . "\n" . "Email : " . $member['courtemail'] , 7, 29);
				$addresses = split("\n", $member['courtaddress']);
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A7', $member['courtname']);
				
				foreach ($addresses as $address) {
					$objPHPExcel->getActiveSheet()->SetCellValue('A' . ($row++), $address);
				}
				
				$objPHPExcel->getActiveSheet()->SetCellValue("A" . ($row++), $member['courttelephone']);
				$objPHPExcel->getActiveSheet()->SetCellValue("A" . ($row++), $member['courtfax']);
				$objPHPExcel->getActiveSheet()->SetCellValue("A$row", $member['courtemail']);
				
				
				$objPHPExcel->getActiveSheet()->getStyle("A7:A$row")->applyFromArray($normalLJ);
				
				$row++;
				
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
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H2:L2");
				$objPHPExcel->getActiveSheet()->SetCellValue('H2', "STATEMENT");
				$objPHPExcel->getActiveSheet()->getStyle('H2')->applyFromArray($boldCJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H3:J3");
				$objPHPExcel->getActiveSheet()->SetCellValue('H3', "Statement Number :");
				$objPHPExcel->getActiveSheet()->getStyle('H3')->applyFromArray($boldLJ);

				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K6:L6");
				$objPHPExcel->getActiveSheet()->SetCellValue("H6", "Page");
				$objPHPExcel->getActiveSheet()->getStyle('H6')->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->SetCellValue("K6", "1");
				$objPHPExcel->getActiveSheet()->getStyle('K6')->applyFromArray($normalLJ);
								
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H8:J8");
				$objPHPExcel->getActiveSheet()->SetCellValue('H8', "Account Number");
				$objPHPExcel->getActiveSheet()->getStyle('H8')->applyFromArray($boldLJ);

				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K8:L8");
 				$objPHPExcel->getActiveSheet()->SetCellValue('K8', $member['accountnumber'] . $statementnumber);
 				$objPHPExcel->getActiveSheet()->getStyle('K8')->applyFromArray($normalLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K3:L3");
				$objPHPExcel->getActiveSheet()->SetCellValue('K3', date("d-M-Y"));
				$objPHPExcel->getActiveSheet()->getStyle('K3')->applyFromArray($normalLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("H4:J4");
				$objPHPExcel->getActiveSheet()->SetCellValue('H4', "Date");
				$objPHPExcel->getActiveSheet()->getStyle('H4')->applyFromArray($boldLJ);
				
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K4:L4");
				$objPHPExcel->getActiveSheet()->SetCellValue('K4', $_POST['datefrom'] . " - " . $_POST['dateto']);
				
				$row++;
				
				$objPHPExcel->getActiveSheet()->SetCellValue("A$row", "Account");
				$objPHPExcel->getActiveSheet()->SetCellValue("B$row", "Date");
				$objPHPExcel->getActiveSheet()->SetCellValue("D$row", "Page");
				$objPHPExcel->getActiveSheet()->SetCellValue("F$row", "Account");
				$objPHPExcel->getActiveSheet()->SetCellValue("I$row", "Date");
				$objPHPExcel->getActiveSheet()->SetCellValue("J$row", "Page");
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B$row:C$row");
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("D$row:E$row");
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("F$row:H$row");
				
				$objPHPExcel->getActiveSheet()->getStyle("A$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("B$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("D$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("F$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("I$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("J$row")->applyFromArray($boldLJ);
				
				$row++;
				
				$objPHPExcel->getActiveSheet()->SetCellValue("A$row", $member['accountnumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue("B$row", date("d/m/Y"));
				$objPHPExcel->getActiveSheet()->SetCellValue("D$row", "1");
				$objPHPExcel->getActiveSheet()->SetCellValue("F$row", $member['accountnumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue("I$row", date("d/m/Y"));
				$objPHPExcel->getActiveSheet()->SetCellValue("J$row", "1");
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B$row:C$row");
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("D$row:E$row");
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("F$row:H$row");
				
				$objPHPExcel->getActiveSheet()->getStyle("A$row")->applyFromArray($normalLJ);
				$objPHPExcel->getActiveSheet()->getStyle("B$row")->applyFromArray($normalLJ);
				$objPHPExcel->getActiveSheet()->getStyle("D$row")->applyFromArray($normalLJ);
				$objPHPExcel->getActiveSheet()->getStyle("F$row")->applyFromArray($normalLJ);
				$objPHPExcel->getActiveSheet()->getStyle("I$row")->applyFromArray($normalLJ);
				$objPHPExcel->getActiveSheet()->getStyle("J$row")->applyFromArray($normalLJ);
				
				$row += 2;
								
				$objPHPExcel->getActiveSheet()->SetCellValue("A$row", "Date");
				$objPHPExcel->getActiveSheet()->SetCellValue("B$row", "Reference");
				$objPHPExcel->getActiveSheet()->SetCellValue("D$row", "Description");
				$objPHPExcel->getActiveSheet()->SetCellValue("E$row", "Debit");
				$objPHPExcel->getActiveSheet()->SetCellValue("F$row", "Penalty");
				$objPHPExcel->getActiveSheet()->SetCellValue("G$row", "Credit");
				$objPHPExcel->getActiveSheet()->SetCellValue("H$row", "Date");
				$objPHPExcel->getActiveSheet()->SetCellValue("I$row", "Reference");
				$objPHPExcel->getActiveSheet()->SetCellValue("J$row", "Amount");
				$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B$row:C$row");
				
				$objPHPExcel->getActiveSheet()->getStyle("A$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("B$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("D$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("E$row")->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle("F$row")->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle("G$row")->applyFromArray($boldRJ);
				$objPHPExcel->getActiveSheet()->getStyle("H$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("I$row")->applyFromArray($boldLJ);
				$objPHPExcel->getActiveSheet()->getStyle("J$row")->applyFromArray($boldLJ);
				
				$row++;
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
			
			if ($member['vatapplicable'] == "N") {
				$taxdesc = "Tax Invoice - J33# " . $member['j33number'];
				 
			} else {
				$taxdesc = "Tax Invoice - Case# " . $member['casenumber'];
			}
			
			if ($member['penalty'] == "T") {
				$penalty = "10%";
				$total = $member['total'] * 0.9;
				 
			} else if ($member['penalty'] == "F") {
				$penalty = "15%";
				$total = $member['total'] * 0.85;
			
			} else if ($member['penalty'] == "Y") {
				$penalty = "50%";
				$total = $member['total'] * 0.5;
			
			} else {
				$penalty = "None";
				$total = $member['total'];
			}
				
			$objPHPExcel->getActiveSheet()->SetCellValue("A" . $row, $member['paymentdate2']);
			$objPHPExcel->getActiveSheet()->SetCellValue("B" . $row, $member['invoicenumber']);
			$objPHPExcel->getActiveSheet()->SetCellValue("D" . $row, $taxdesc);

			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B$row:C$row");
				
			if ($member['paid'] == "Y") {
				$amountpaid += $total;
						
				$objPHPExcel->getActiveSheet()->SetCellValue("E" . $row, " ");
				$objPHPExcel->getActiveSheet()->SetCellValue("F" . $row, $penalty);
				$objPHPExcel->getActiveSheet()->SetCellValue("G" . $row, "R " . number_format($total, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue("H" . $row, $member['paymentdate']);
				$objPHPExcel->getActiveSheet()->SetCellValue("I" . $row, $member['paymentnumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue("J" . $row, "R " . number_format($total, 2));
				
			} else {
				$objPHPExcel->getActiveSheet()->SetCellValue("E" . $row, "R " . number_format($total, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue("F" . $row, $penalty);
				$objPHPExcel->getActiveSheet()->SetCellValue("G" . $row, " ");
				$objPHPExcel->getActiveSheet()->SetCellValue("H" . $row, " ");
				$objPHPExcel->getActiveSheet()->SetCellValue("I" . $row, " ");
				$objPHPExcel->getActiveSheet()->SetCellValue("J" . $row, " ");
				
				$amountdue += $total;
				
				if ($member['since'] == "150") {
					$due150amount = $due150amount + $total;
						
				} else if ($member['since'] == "120") {
					$due120amount = $due120amount + $total;
						
				} else if ($member['since'] == "90") {
					$due90amount = $due90amount + $total;
						
				} else if ($member['since'] == "60") {
					$due60amount = $due60amount + $total;
						
				} else if ($member['since'] == "30") {
					$due30amount = $due30amount + $total;
				}
			}
			
			$objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('B' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('C' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('D' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('E' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('G' . $row)->applyFromArray($normalRJ);
			$objPHPExcel->getActiveSheet()->getStyle('H' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('I' . $row)->applyFromArray($normalLJ);
			$objPHPExcel->getActiveSheet()->getStyle('J' . $row)->applyFromArray($normalLJ);
				
			$row++;
		}
	
	} else {
		logError($sql . " - " . mysql_error());
	}
	
	$row++;
	
	$objPHPExcel->getActiveSheet()->SetCellValue("A$row", "150 Days");
	$objPHPExcel->getActiveSheet()->SetCellValue("B$row", "120 Days");
	$objPHPExcel->getActiveSheet()->SetCellValue("C$row", "90 Days");
	$objPHPExcel->getActiveSheet()->SetCellValue("D$row", "60 Days");
	$objPHPExcel->getActiveSheet()->SetCellValue("E$row", "30 Days");
	$objPHPExcel->getActiveSheet()->SetCellValue("F$row", "Amount Due : ");
	$objPHPExcel->getActiveSheet()->SetCellValue("G$row", "R " . number_format($amountdue, 2));
	
	$objPHPExcel->getActiveSheet()->getStyle("A$row")->applyFromArray($boldLJ);
	$objPHPExcel->getActiveSheet()->getStyle("B$row")->applyFromArray($boldLJ);
	$objPHPExcel->getActiveSheet()->getStyle("C$row")->applyFromArray($boldLJ);
	$objPHPExcel->getActiveSheet()->getStyle("D$row")->applyFromArray($boldLJ);
	$objPHPExcel->getActiveSheet()->getStyle("E$row")->applyFromArray($boldLJ);
	$objPHPExcel->getActiveSheet()->getStyle("F$row")->applyFromArray($boldLJ);
	$objPHPExcel->getActiveSheet()->getStyle("G$row")->applyFromArray($normalLJ);
	
	$row++;
	
	$objPHPExcel->getActiveSheet()->SetCellValue("A$row", "R " . number_format($due150amount, 2));
	$objPHPExcel->getActiveSheet()->SetCellValue("B$row", "R " . number_format($due120amount, 2));
	$objPHPExcel->getActiveSheet()->SetCellValue("C$row", "R " . number_format($due90amount, 2));
	$objPHPExcel->getActiveSheet()->SetCellValue("D$row", "R " . number_format($due60amount, 2));
	$objPHPExcel->getActiveSheet()->SetCellValue("E$row", "R " . number_format($due30amount, 2)	);
	$objPHPExcel->getActiveSheet()->SetCellValue("F$row", "Amount Paid : ");
	$objPHPExcel->getActiveSheet()->SetCellValue("G$row", "R " . number_format($amountpaid, 2));
	
	$objPHPExcel->getActiveSheet()->getStyle("A$row")->applyFromArray($normalLJ);
	$objPHPExcel->getActiveSheet()->getStyle("B$row")->applyFromArray($normalLJ);
	$objPHPExcel->getActiveSheet()->getStyle("C$row")->applyFromArray($normalLJ);
	$objPHPExcel->getActiveSheet()->getStyle("D$row")->applyFromArray($normalLJ);
	$objPHPExcel->getActiveSheet()->getStyle("E$row")->applyFromArray($normalLJ);
	$objPHPExcel->getActiveSheet()->getStyle("F$row")->applyFromArray($boldLJ);
	$objPHPExcel->getActiveSheet()->getStyle("G$row")->applyFromArray($normalLJ);
	
	$row++;
	$objPHPExcel->getActiveSheet()->SetCellValue("D$row", "Total Due");
	$objPHPExcel->getActiveSheet()->SetCellValue("E$row", "R " . number_format($amountdue, 2));
	$objPHPExcel->getActiveSheet()->SetCellValue("F$row", "Comments : " . $_POST['comment']);
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("F$row:J$row");
	
	$objPHPExcel->getActiveSheet()->getStyle("D$row")->applyFromArray($boldLJ);
	$objPHPExcel->getActiveSheet()->getStyle("E$row")->applyFromArray($normalLJ);
	$objPHPExcel->getActiveSheet()->getStyle("F$row")->applyFromArray($boldLJ);
	
	
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save('php://output');
	
?>