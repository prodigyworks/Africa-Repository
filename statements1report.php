<?php
	require_once('system-db.php');
	require_once('fpdf.php');
	require_once('simple_html_dom.php');
	
	class Statement1Report extends FPDF
	{
		// private variables
		var $colonnes;
		var $format;
		var $angle=0;
		var $member;
		var $top = 0;
		var $statementnumber = 0;
		var $amountpaid  = 0;
		var $amountdue = 0;
		
		function newPage() {
			global $member;
			global $statementnumber;
			global $top;
			global $amountpaid;
			global $amountdue;
			
			$this->AddPage();
			
		    if ($member['vatapplicable'] == "Y") {
			    $heading = $member['privatebankingdetails'];
			    
		    } else {
			    $heading = $member['bankingdetails'];
		    }
		    
		    $this->Rect( 5, 5, 	200, 285, "D");
	        $this->Line( 5, 30, 205, 30);
	        $this->Line( 115, 5, 115, 290);
	        $this->Line( 115, 20, 205, 20);
	        $this->Line( 115, 40, 205, 40);
	        $this->Line( 5, 62, 205, 62);
	        $this->Line( 5, 67, 205, 67);
	        $this->Line( 5, 77, 205, 77);
	        $this->Line( 5, 82, 205, 82);
	        $this->Line( 5, 260, 205, 260);
	        $this->Line( 5, 270, 205, 270);
	        $this->Line( 5, 280, 205, 280);
	        $this->Line( 25, 260, 25, 280);
	        $this->Line( 45, 260, 45, 280);
	        $this->Line( 70, 260, 70, 280);
	        $this->Line( 95, 260, 95, 290);
	        $this->Line( 95, 285, 115, 285);
		    $this->AddCell(97, 282, "Total Due", "B");
		    $this->AddCell(7, 264, "150 Days", "B");
		    $this->AddCell(27, 264, "120 Days", "B");
		    $this->AddCell(51, 264, "90 Days", "B");
		    $this->AddCell(78, 264, "60 Days", "B");
		    $this->AddCell(98, 264, "30 Days", "B");
		    
		    $this->SetXY( 10, 283 );
		    
		    $this->SetXY(142, 8);
		    $this->SetFont('Arial','B',13);
		    $length = $this->GetStringWidth( "STATEMENT" );
		    $this->MultiCell($length * 2, 6, "STATEMENT");
		    
		    $this->addHeading( 117, 283, "Comments :", $_POST['comment'], 20);
		    
		    
		    $this->addHeading( 117, 15, "Statement Number :", $member['accountnumber'] . $statementnumber);
		    $this->addHeading( 117, 24, "Date :", $_POST['datefrom'] . " - " . $_POST['dateto']);
		    $this->addHeading( 117, 34, "Page :", $this->page);
		    $this->addHeading( 117, 48, "Account Number :", $member['accountnumber']);
		    
			$this->addAddress(" ", "iAfrica Transcriptions (Pty) LTD\n5th Floor, Schreiner Chambers\n94 Pritchard Street\nJohannesburg", 7, 7);
			
			$this->addAddress(" ", $member['courtname'] . "\n" .$member['courtaddress'] . "\n" . "Tel : " . $member['courttelephone'] . "\n" . "Fax : " . $member['courtfax'] . "\n" . "Email : " . $member['courtemail'] , 7, 29);
			
		    $this->addHeading( 70, 10, "Co Reg : ", "1997/00931/07", 20);
		    $this->addHeading( 70, 14, "VAT Reg : ", "4750166706", 20);
		    $this->addHeading( 70, 18, "Tel No :", "(011) 336-1455", 20);
		    $this->addHeading( 70, 22, "Fax No : ", "(011) 336-2403", 20);
		    
			
		    $this->SetFont('Arial','B',9);
		    
			
			$top = 67;

		    $this->SetFont('Arial','', 9);
				
			$cols=array( "Account"    => 28,
			             "Date"  => 22,
			             "Page"  => 77,
			             "Account "  => 27,
			             "Date " => 20,
			             "Page " => 16);
		
			$this->addHeaderCols( $cols);
			$cols=array( "Account"    => "L",
			             "Date"  => "L",
			             "Page"  => "L",
			             "Account "  => "L",
			             "Date " => "L",
			             "Page " => "L");
			$this->addLineFormat( $cols);
			
			$line=array( 
					 "Account"    => $member['accountnumber'],
		             "Date"  => date("d/m/Y"),
		             "Page"  => $this->page,
					 "Account " => $member['accountnumber'],
		             "Date "  => date("d/m/Y"),
		             "Page "  => $this->page
		         );
			             
			$size = $this->addLine2( $top +4, $line );
			
			$cols=array( "Date"    => 18,
			             "Reference"  => 26,
			             "Description"  => 46,
			             "Debit"  => 20,
			             "Penalty"  => 10,
						 "Credit"  => 18,
			             "Date " => 16,
			             "Reference "  => 27,
			             "Amount" => 18);
		
			$this->addCols( $cols);
			$cols=array( "Date"    => "L",
			             "Reference"  => "L",
			             "Description"  => "L",
			             "Debit"  => "R",
			             "Penalty"  => 10,
						 "Credit"  => "R",
			             "Date "  => "L",
			             "Reference " => "L",
			             "Amount" => "R");
			$this->addLineFormat( $cols);
						$top = 85;
		}
		
		function __construct($orientation, $metric, $size) {
			global $member;
			global $top;
			global $statementnumber;
			global $amountpaid;
			global $amountdue;
			
	        parent::__construct($orientation, $metric, $size);
	        
	        $this->SetAutoPageBreak(true, 0);
	                  
			//Include database connection details
			
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
		
			$margin = 7;
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
			
			if ($result) {
				$total = 0;
				$subtotal = 0;
				$shipping = 0;
				$totalvat = 0;
				$depositamount = 0;
				$vatapplicable = "N";
				$due150amount = 0;
				$due120amount = 0;
				$due90amount = 0;
				$due60amount = 0;
				$due30amount = 0;
				$first = true;
				
				while (($member = mysql_fetch_assoc($result))) {
					if ($first) {
						$this->newPage();
						$y = $top;
						
						$first = false;
					}
					
					$vatapplicable = $member['vatapplicable'];
//					$description = $this->stripHTML($member['description']);
					$description = $member['description'];
					$id = $member['id'];
					
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
				    
					if ($member['paid'] == "Y") {
						$amountpaid += $total;
						
						$line=array( "Date"    => $member['paymentdate2'],
						             "Reference"  => $member['invoicenumber'],
						             "Description"  => $taxdesc,
						             "Debit"  => " ",
			             			 "Penalty"  => $penalty,
									 "Credit"  => "R " . number_format($total, 2),
						             "Date "  => $member['paymentdate'],
						             "Reference " => $member['paymentnumber'],
						             "Amount" => "R " . number_format($total, 2));
						             
					} else {
						$line=array( "Date"    => $member['paymentdate2'],
						             "Reference"  => $member['invoicenumber'],
						             "Description"  => $taxdesc,
						             "Debit"  => "R " . number_format($total, 2),
			             			 "Penalty"  => $penalty,
									 "Credit"  => " ",
						             "Date "  => " ",
						             "Reference " => " ",
						             "Amount" => " ");
						             
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

					$size = $this->addLine2( $y, $line );
					$y += $size;
					
					if ($y > 255) {
						$this->newPage();
						$y = $top;
					}
		 		}
				
			} else {
				logError($sql . " - " . mysql_error());
			}
			
		    $this->AddCell(7, 274, "R " . number_format($due150amount, 2));
		    $this->AddCell(27, 274, "R " . number_format($due120amount, 2));
		    $this->AddCell(47, 274, "R " . number_format($due90amount, 2));
		    $this->AddCell(72, 274, "R " . number_format($due60amount, 2));
		    $this->AddCell(95, 274, "R " . number_format($due30amount, 2));
		    
		    $this->addHeading( 117, 263, "Amount Due :", "R " . number_format($amountdue, 2), 30);
		    $this->addHeading( 117, 273, "Amount Paid :", "R " . number_format($amountpaid, 2), 30);
		    $this->addCell( 95, 287, "R " . number_format($amountdue, 2));
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
		        
		        if ($largeur == 0) {
			        $res = 1 + floor( $length ) ;
		        	
		        } else {
			        $res = 1 + floor( $length / $largeur) ;
		        }
		        
		        $nb_lines += $res;
		    }
		    return $nb_lines;
		}
		
		// Company
		function addAddress( $nom, $adresse , $x1, $y1) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',8);
		    $length = $this->GetStringWidth( $nom );
		    $this->Cell( $length, 2, $nom);
		    $this->SetXY( $x1, $y1 + 3 );
		    $this->SetFont('Arial','',7);
		    
		    $length = $this->GetStringWidth( $adresse );
		    //Coordonnées de la société
		    $lignes = $this->sizeOfText( $adresse, $length) ;
		    $this->MultiCell(100, 3, $adresse, 0, 'L');
		}
		
		// Company
		function addSubAddress( $nom, $adresse , $x1, $y1) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','',9);
		    $this->SetTextColor(200, 200, 200);
		    $length = $this->GetStringWidth( $nom );
		    $this->Cell( $length, 2, $nom);
		    $this->SetXY( $x1, $y1 + 4 );
		    $this->SetFont('Arial','',9);
		    $this->SetTextColor(200, 200, 200);
		    
		    $length = $this->GetStringWidth( $adresse );
		    //Coordonnées de la société
		    $lignes = $this->sizeOfText( $adresse, $length) ;
		    $this->MultiCell($length, 3, $adresse);
		}
		
		function addCols( $tab ) {
		    global $colonnes;
		    
		    $r1  = 6;
		    $r2  = $this->w - ($r1 * 2) ;
		    $y1  = 77;
		    $y2  = $this->h - 35 - $y1;
		    $this->SetFont('Arial','B',6.5);
		    $this->SetXY( $r1, $y1 );
	//	    $this->Rect( $r1, $y1, $r2, $y2, "D");
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
		
		function addHeaderCols( $tab ) {
		    global $colonnes;
		    
		    $r1  = 6;
		    $r2  = $this->w - ($r1 * 2) ;
		    $y1  = 62;
		    $y2  = 62;
		    $this->SetFont('Arial','B',6.5);
		    $this->SetXY( $r1, $y1 );
//		    $this->Rect( $r1, $y1, $r2, $y2, "D");
	//	    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
		    $colX = $r1;
		    $colonnes = $tab;
		    
		    while ( list( $lib, $pos ) = each ($tab) ) {
		        $this->SetXY( $colX, $y1+2 );
		        $this->Cell( $pos, 1, $lib, 0, 0, "L");
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
		
		function addLine( $ligne, $tab, $border = 0 , $bold = "") {
		    global $colonnes, $format;
		
		    $ordonnee     = 6;
		    $maxSize      = $ligne;
		
		    reset( $colonnes );
		    while ( list( $lib, $pos ) = each ($colonnes) )
		    {
			    $this->SetFont('Arial',$bold,9);
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
		    return ( $maxSize - $ligne ) + 1;
		}
		
		function addLine2( $ligne, $tab, $border = 0 , $bold = "") {
		    global $colonnes, $format;
		
		    $ordonnee     = 6;
		    $maxSize      = $ligne;
		
		    reset( $colonnes );
		    while ( list( $lib, $pos ) = each ($colonnes) )
		    {
			    $this->SetFont('Arial',$bold,6.5);
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
		    return ( $maxSize - $ligne ) + 1;
		}
		
		function addHeading( $x1, $y1, $heading, $value, $margin = 36) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',7);
		    $length = $this->GetStringWidth( $heading ) * 2;
	        $tailleTexte = $this->sizeOfText( $heading, $length );
		    $this->MultiCell( $length, 3, $heading);
		    
			$maxY = $this->GetY();
			
		    $this->SetXY( $x1 + $margin, $y1);
		    $this->SetFont('Arial','',7);
		    $length = $this->GetStringWidth( $value . " " ) * 2;
	        $tailleTexte = $this->sizeOfText( $value, $length );
		    $this->MultiCell( $length, 3, $value);
		    
		    if ($this->GetY() > $maxY) {
			    $maxY = $this->GetY();
		    }
		    
		    
		    return $maxY;
		}
		
		function addCell($x, $y, $string, $font = "") {
		    $this->SetXY( $x, $y );
		    $this->SetFont('Arial',$font,7);
		    $length = $this->GetStringWidth( $string );
		    $this->Cell( $length, 2, $string);
		}
	}

	$pdf = new Statement1Report( 'P', 'mm', 'A4');
	$pdf->Output();
	
?>