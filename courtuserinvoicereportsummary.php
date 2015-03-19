<?php
	require_once('fpdf.php');
	require_once('system-db.php');
	
	class InvoiceSummaryReport extends FPDF
	{
		// private variables
		var $colonnes;
		var $format;
		var $angle=0;
		var $y = 35;
		private $multipage = false;
		
		function newPage($member) {
			global $y;
			
			$this->addPage();
			
			$this->addHeading( 10, 2, "Invoice Report");
			$this->addMidHeading(10, 8.5, "Court", $member['courtname']);
			$this->addSubHeading(10, 14, "From Date", (isset($_POST['datefrom'])) ? $_POST['datefrom'] : "");
			$this->addSubHeading(10, 18, "To Date", (isset($_POST['dateto'])) ? $_POST['dateto'] : "");
			
			if ($_POST['status'] == "Y") {
				$this->addSubHeading(10, 22, "Status", "Paid");

			} else if ($_POST['status'] == "N") {
				$this->addSubHeading(10, 22, "Status", "Outstanding");
			
			} else {
				$this->addSubHeading(10, 22, "Status", "All");
			}
			
		    $this->SetFont('Arial','', 6);
			$cols=array( "Date Received"    => 18,
			             "Date Returned"  => 18,
						 "Invoice Number"  => 23,
						 "J33 Number"  => 21,
			             "Case Number"  => 21,
			             "Rate"  => 31,
			             "Status"  => 12,
			             "Penalty"  => 30,
			             "Total"  => 18);
		
			$this->addCols( $cols);
			$cols=array( "Date Received"    => "L",
			             "Date Returned"  => "L",
						 "J33 Number"  => "L",
			             "Case Number"  => "L",
			             "Rate"  => "L",
			             "Status"  => "L",
			             "Penalty"  => "L",
			             "Total"  => "R");
			$this->addLineFormat( $cols);
			$y = 35;
		}
		
		function boxes($y) {
			$this->Line( 153, $y + 5, 200, $y + 5);
			$this->Line( 153, $y + 10, 200, $y + 10);
			$this->Line( 10, 32, 200, 32);
			
			$this->Line( 27, 27, 27, $y);
			$this->Line( 45, 27, 45, $y);
			$this->Line( 69, 27, 69, $y);
			$this->Line( 89, 27, 89, $y);
			$this->Line( 110, 27, 110, $y);
			$this->Line( 140, 27, 140, $y);
			$this->Line( 153, 27, 153, $y + 10);
			$this->Line( 180, 27, 180, $y + 10);
			$this->Line( 200, 27, 200, $y + 10);
			
			$this->Rect( 10, 27, 190, $y - 27, "D");
		}
		
		function __construct($orientation, $metric, $size) {
	        parent::__construct($orientation, $metric, $size);
	                  
			//Include database connection details
			
			start_db();
			
			global $y;
			
			$and = "";
			
			if (isset($_POST['status']) && $_POST['status'] != "A") {
				$and .= " AND A.paid = '" . $_POST['status'] . "' ";
			}
			
			if (isset($_POST['datefrom']) && $_POST['datefrom'] != "") {
				$and .= " AND A.createddate >= '" . convertStringToDate($_POST['datefrom']) . "' ";
			}
			
			if (isset($_POST['dateto']) && $_POST['dateto'] != "") {
				$and .= " AND A.createddate <= '" . convertStringToDate($_POST['dateto']) . "' ";
			}
			
			if (isset($_POST['courtid']) && $_POST['courtid'] != "0") {
				$and .= " AND D.id = " . $_POST['courtid'] . " ";
			}
		
			$sql = "SELECT DISTINCT A.*, B.courtid, B.j33number, B.casenumber, " .
					"DATE_FORMAT(A.createddate, '%d/%m/%Y') AS paymentdate2, " .
					"DATE_FORMAT(B.datereceived, '%d/%m/%Y') AS datereceived, " .
					"D.name AS courtname, E.name AS provincename, F.name AS terms, G.firstname, G.lastname, H.name AS ratename " .
					"FROM {$_SESSION['DB_PREFIX']}invoices A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
					"ON B.id = A.caseid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
					"ON D.id = B.courtid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}usercourts DD " .
					"ON DD.memberid = " . getLoggedOnMemberID() . " " .
					"AND DD.courtid = D.id " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
					"ON E.id = D.provinceid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}caseterms F " .
					"ON F.id = A.termsid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}members G " .
					"ON G.member_id = A.contactid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates H " .
					"ON H.id = B.rate " .
					"WHERE 1 = 1 $and " .
					"ORDER BY A.id DESC";
			$result = mysql_query($sql);
			
			if ($result) {
				$first = true;
				$totalpaid = 0;
				$totalunpaid = 0;
				$courtunpaidtotal = 0;
				$courtpaidtotal = 0;
				$oldcourt = 0;
				
				while (($member = mysql_fetch_assoc($result))) {
					if ($oldcourt != $member['courtid']) {
						if ($oldcourt != 0) {
							$this->boxes($y);
								
							$line=array(
									"Date Received"    => " ",
			             			"Date Returned"  => " ",
									"Invoice Number"    => " ",
									"J33 Number"  => " ",
									"Case Number"  => " ",
									"Rate"  => " ",
									"Status"  => " ",
									"Penalty"  => "Total Outstanding : ",
									"Total"  => "R " . number_format($courtunpaidtotal, 2)
							);
							
							$this->SetFont('Arial','B',7);
							$y += $this->addLine( $y + 2, $line ) + 2;
							
								
							$line=array(
									"Date Received"    => " ",
			             			"Date Returned"  => " ",
									"Invoice Number"    => " ",
									"J33 Number"  => " ",
									"Case Number"  => " ",
									"Rate"  => " ",
									"Status"  => " ",
									"Penalty"  => "Total Paid : ",
									"Total"  => "R " . number_format($courtpaidtotal, 2)
							);
							
							$this->SetFont('Arial','B',7);
							$y += $this->addLine( $y + 2, $line );
							
							$courtpaidtotal = 0;
							$courtunpaidtotal = 0;
							$this->multipage = true;
						}
						
						$this->newPage($member);
						$oldcourt = $member['courtid'];
					}
					
					if ($member['penalty'] == "T") {
						$penalty = "10%";
						$subtotal = $member['total'] * 0.9;
						 
					} else if ($member['penalty'] == "F") {
						$penalty = "15%";
						$subtotal = $member['total'] * 0.85;
						
					} else if ($member['penalty'] == "Y") {
						$penalty = "50%";
						$subtotal = $member['total'] * 0.5;
						
					} else {
						$penalty = "None";
						$subtotal = $member['total'];
					}
					
						
					$line=array( 
							 "Date Received"    => $member['datereceived'] . " ",
 	             			 "Date Returned"  => $member['paymentdate2'] . " ",
							 "Invoice Number"    => $member['invoicenumber'],
				             "J33 Number"  => $member['j33number'],
				             "Case Number"  => $member['casenumber'] . " ",
				             "Rate"  => $member['ratename'] . " ",
				             "Status"  => ($member['paid'] == "Y" ? "Paid" : "Unpaid") . " ",
				             "Penalty"  => $penalty,
				             "Total"  => "R " . number_format($subtotal, 2)
				         );

					$size = $this->addLine( $y, $line );
					$y += $size;
					$totalpaid += $subtotal;
					
					if ($member['paid'] == "Y") {
						$courtpaidtotal += $subtotal;
						
					} else {
						$courtunpaidtotal += $subtotal;
					}
						
					if ($y > 265) {
						$this->newPage($member);
					}
		 		}
		 		
				$this->boxes($y);
		 		 
		 		$line=array(
		 				"Date Received"    => " ",
		 				"Invoice Number"    => " ",
		 				"J33 Number"  => " ",
		 				"Case Number"  => " ",
		 				"Rate"  => " ",
		 				"Status"  => " ",
		 				"Penalty"  => "Total Outstanding : ",
		 				"Total"  => "R " . number_format($courtunpaidtotal, 2)
		 		);
		 		
		 		$this->SetFont('Arial','B',7);
		 		 
		 		$y += $this->addLine( $y + 2, $line ) + 1.5;
		 		 
				$line=array( 
						 "Date Received"    => " ",
						 "Invoice Number"    => " ",
						 "J33 Number"  => " ",
			             "Case Number"  => " ",
			             "Rate"  => " ",
			             "Status"  => " ",
			             "Penalty"  => "Total Paid : ",
			             "Total"  => "R " . number_format($courtpaidtotal, 2)
			         );

				$size = $this->addLine( $y + 2, $line );
				
			} else {
				logError($sql . " - " . mysql_error());
			}
		
		}

		// public functions
		function sizeOfText( $texte, $largeur )
		{
		    $index    = 0;
		    $nb_lines = 0;
		    $loop     = TRUE;
		    while ( $loop )
		    {
		        $pos = strpos($texte, "\n");
		        if (!$pos)
		        {
		            $loop  = FALSE;
		            $ligne = $texte;
		        }
		        else
		        {
		            $ligne  = substr( $texte, $index, $pos);
		            $texte = substr( $texte, $pos+1 );
		        }
		        $length = floor( $this->GetStringWidth( $ligne ) );
		        $res = 1 + floor( $length / $largeur) ;
		        $nb_lines += $res;
		    }
		    return $nb_lines;
		}
		
		// Company
		function addAddress( $nom, $adresse , $x1, $y1) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',10);
		    $length = $this->GetStringWidth( $nom );
		    $this->Cell( $length, 2, $nom);
		    $this->SetXY( $x1, $y1 + 4 );
		    $this->SetFont('Arial','',10);
		    
		    $length = $this->GetStringWidth( $adresse );
		    //Coordonnées de la société
		    $lignes = $this->sizeOfText( $adresse, $length) ;
		    $this->MultiCell(100, 3, $adresse, 0, 'L');
		}
		
		// Company
		function addSubAddress( $nom, $adresse , $x1, $y1) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','',6);
		    $this->SetTextColor(200, 200, 200);
		    $length = $this->GetStringWidth( $nom );
		    $this->Cell( $length, 2, $nom);
		    $this->SetXY( $x1, $y1 + 4 );
		    $this->SetFont('Arial','',6);
		    $this->SetTextColor(200, 200, 200);
		    
		    $length = $this->GetStringWidth( $adresse );
		    //Coordonnées de la société
		    $lignes = $this->sizeOfText( $adresse, $length) ;
		    $this->MultiCell($length, 3, $adresse);
		}
		
		function addCols( $tab ) {
		    global $colonnes;
		    
		    $r1  = 10;
		    $r2  = $this->w - ($r1 * 2) ;
		    $y1  = 27;
		    $y2  = $this->h - 25 - $y1;
		    $this->SetXY( $r1, $y1 );
//		    $this->Rect( $r1, $y1, $r2, $y2, "D");
//		    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
		    $colX = $r1;
		    $colonnes = $tab;
		    
		    while ( list( $lib, $pos ) = each ($tab) ) {
		        $this->SetXY( $colX, $y1+2 );
		        $this->Cell( $pos, 1, $lib, 0, 0, "C");
		        $colX += $pos;
//		        $this->Line( $colX, $y1, $colX, $y1+$y2);
		    }
		}
		
		function addLineFormat( $tab ) {
		    global $format, $colonnes;
		    
		    while ( list( $lib, $pos ) = each ($colonnes) )
		    {
		        if ( isset( $tab["$lib"] ) )
		            $format[ $lib ] = $tab["$lib"];
		    }
		}
		
		function addLine( $ligne, $tab ) {
		    global $colonnes, $format;
		
		    $ordonnee     = 10;
		    $maxSize      = $ligne;
		
		    reset( $colonnes );
		    while ( list( $lib, $pos ) = each ($colonnes) )
		    {
		        $longCell  = $pos -2;
		        $texte     = $tab[ $lib ];
		        $length    = $this->GetStringWidth( $texte );
		        $tailleTexte = $this->sizeOfText( $texte, $length );
		        $formText  = $format[ $lib ];
		        $this->SetXY( $ordonnee, $ligne-1);
		        $this->MultiCell( $longCell, 4 , $texte, 0, $formText);
		        if ( $maxSize < ($this->GetY()  ) )
		            $maxSize = $this->GetY() ;
		        $ordonnee += $pos;
		    }
		    return ( $maxSize - $ligne );
		}
		
		// Company
		function addHeading( $x1, $y1, $heading) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','BU',11);
		    $length = $this->GetStringWidth( $heading );
		    $this->Cell( $length, 2, $heading);
		}

		// Company
		function addMidHeading( $x1, $y1, $heading, $text) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',11);
    		   $length = $this->GetStringWidth( $heading );
		    $this->Cell( $length, 2, $heading);
		    
		    $this->SetXY( $x1 + 30, $y1 );
		    $this->SetFont('Arial','BI',10);
		    $length = $this->GetStringWidth( ": " .$text );
		    $this->Cell( $length, 2, ": " . $text);
		}
		
		// Company
		function addSubHeading( $x1, $y1, $heading, $text) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',7);
		    $length = $this->GetStringWidth( $heading );
		    $this->Cell( $length, 2, $heading);
		    
		    $this->SetXY( $x1 + 30, $y1 );
		    $this->SetFont('Arial','BI',6);
		    $length = $this->GetStringWidth( ": " .$text );
		    $this->Cell( $length, 2, ": " . $text);
		}
	}
	
	$pdf = new InvoiceSummaryReport( 'P', 'mm', 'A4');
	$pdf->Output();
	
?>