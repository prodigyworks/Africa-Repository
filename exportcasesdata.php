<?php
	/** Error reporting */
	error_reporting(E_ALL);
	
	/** Include path **/
	/** PHPExcel */
	include 'system-db.php';
	include 'PHPExcel.php';
	include 'PHPExcel/Writer/Excel2007.php';
	require_once("simple_html_dom.php");
	
	start_db();
	initialise_db();
	
	$fromdate = convertStringToDate($_POST['fromdate']);
	$todate = convertStringToDate($_POST['todate']);

	header('Content-type: application/excel');
	header('Content-disposition: attachment; filename=cases.xlsx;');
	
	$list = "";
	
	for ($i = 0; $i < count($_POST['courtid']); $i++) {
		if ($i > 0) {
			$list .= ", ";
		}
		
		$list .= $_POST['courtid'][$i];
	}
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	
	// Set properties
	$objPHPExcel->getProperties()->setCreator("I Africa Transcriptions (PTY) LTD");
	$objPHPExcel->getProperties()->setLastModifiedBy("I Africa Transcriptions (PTY) LTD");
	$objPHPExcel->getProperties()->setTitle("Cases");
	$objPHPExcel->getProperties()->setSubject("Cases");
	$objPHPExcel->getProperties()->setDescription("Cases");
		
	$headerArray = array(	
			'font' => array(		'bold' => true),
			'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		    )
		  )
		);
	
	$styleArray = array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		    )
		  )
		);
		
	$qry = "SELECT A.* " .
			"FROM {$_SESSION['DB_PREFIX']}courts A " .
			"WHERE A.id IN ($list) " .
			"ORDER BY A.name";
	$courtresult = mysql_query($qry);
	
	if (! $courtresult) {
		logError($qry . " - " . mysql_error());
	}
	
	$sheet = 0;
	
	try {
		while (($courts = mysql_fetch_assoc($courtresult))) {
			$objPHPExcel->createSheet(NULL, $sheet);
			$objPHPExcel->setActiveSheetIndex($sheet++);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
			
			if (strlen($courts['name']) > 30) {
				$objPHPExcel->getActiveSheet()->setTitle(substr($courts['name'], 0, 30));
				
			} else {
				$objPHPExcel->getActiveSheet()->setTitle($courts['name']);
			}
			
			$courtid = $courts['id'];
			
			$qry = "SELECT A.*, " .
					"DATE_FORMAT(A.datereceived, '%d/%m/%Y') AS datereceived, " .
					"DATE_FORMAT(A.datetransmitted, '%d/%m/%Y') AS datetransmitted, " .
					"DATE_FORMAT(A.datehardcopyretcourt, '%d/%m/%Y') AS datehardcopyretcourt, " .
					"DATE_FORMAT(A.dateelectroniccopysubcourt, '%d/%m/%Y') AS dateelectroniccopysubcourt, " .
					"DATE_FORMAT(A.transcriptrequestdate, '%d/%m/%Y') AS transcriptrequestdate, " .
					"DATE_FORMAT(A.datetransmitted, '%d/%m/%Y') AS datetransmitted, " .
					"DATE_FORMAT(C.createddate, '%d/%m/%Y') AS invoicedate, " .
					"B.name, C.penalty, C.paymentnumber, C.invoicenumber, C.total " .
					"FROM {$_SESSION['DB_PREFIX']}cases A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}courts B " .
					"ON B.id = A.courtid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices C " .
					"ON C.caseid = A.id " .
					"WHERE A.courtid = $courtid " .
					"AND A.datereceived >= '$fromdate' " .
					"AND A.datereceived <= '$todate' " .
					"ORDER BY A.id";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
			
			$row = 1;
			$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(8);
			
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(38);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(60);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(66);
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(60);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(62);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(62);
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(32);
			$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(32);
			$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(32);
			
			$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Nr');
			$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Date and time J33 received');
			$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'J33 Number');
			$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Case Number');
			$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Parties Names');
			$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Instruction');
			$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Nr Received cassettes/ CD/ court documents');
			$objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Date: Bound appeal record and copies returned to court');
			$objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Date: hardcopy transcription returned to court');
			$objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Date: electronic copy of transcription submitted to court');
			$objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Invoice Date');
			$objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Invoice Number');
			$objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Invoice Amount');
			
			while (($member = mysql_fetch_assoc($result))) {
				$row++;
				$colour = 0;
				$total = $member['total'];
				
				if ($member['penalty'] == "T") {
					$total = $total * 0.9;
					
				} else  if ($member['penalty'] == "F") {
					$total = $total * 0.85;
					
				} else  if ($member['penalty'] == "Y") {
					$total = $total * 0.5;
				}
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A' . $row, $row);
				$objPHPExcel->getActiveSheet()->SetCellValue('B' . $row, $member['datereceived']);
				$objPHPExcel->getActiveSheet()->SetCellValue('C' . $row, $member['j33number']);
				$objPHPExcel->getActiveSheet()->SetCellValue('D' . $row, $member['casenumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue('E' . $row, $member['plaintiff']);
				$objPHPExcel->getActiveSheet()->SetCellValue('F' . $row, stripHTML($member['instructions']));
				$objPHPExcel->getActiveSheet()->SetCellValue('G' . $row, $member['nrreceivedmedia']);
				$objPHPExcel->getActiveSheet()->SetCellValue('H' . $row, ($member['datetransmitted'] == "00/00/0000" ? "" : $member['datetransmitted']));
				$objPHPExcel->getActiveSheet()->SetCellValue('I' . $row, ($member['datehardcopyretcourt'] == "00/00/0000" ? "" : $member['datehardcopyretcourt']));
				$objPHPExcel->getActiveSheet()->SetCellValue('J' . $row, ($member['dateelectroniccopysubcourt'] == "00/00/0000" ? "" : $member['dateelectroniccopysubcourt']));
				$objPHPExcel->getActiveSheet()->SetCellValue('K' . $row, ($member['invoicedate'] == "00/00/0000" ? "" : $member['invoicedate']));
				$objPHPExcel->getActiveSheet()->SetCellValue('L' . $row, $member['invoicenumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue('M' . $row, number_format($total, 2));
				
				if ($row > 1000) {
					break;
				}
			}
		
		
			$objPHPExcel->getActiveSheet()->getStyle('A1:M' . $row)->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A1:M1')->applyFromArray($headerArray);
		}
		
	} catch (Exception $e) {
		logError($e->getMessage());
	}
	
			
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save('php://output');
	
	function stripHTML($html) {
		$txt = str_get_html("<html>" . $html . "</html>")->plaintext;
		$txt= str_replace("&pound;", "£", str_replace("&amp;", "&", str_replace("&lt;", "<", str_replace("&gt;", "<",  str_replace("&ndash;", "-", str_replace("&nbsp;", " ", $txt))))));
		
		return $txt;
	}
?>
