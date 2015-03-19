<?php
	require_once('system-db.php');
	require_once('fpdf.php');
	require_once('simple_html_dom.php');
	
	class Statement2Report extends FPDF
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
		    
		    $this->Rect( 5, 5, 	285, 190, "D");
	        $this->Line( 5, 30, 290, 30);
	        $this->Line( 175, 5, 175, 205);
	        $this->Line( 175, 13, 290, 13);
	        $this->Line( 175, 20, 290, 20);
	        $this->Line( 175, 36, 290, 36);
	        $this->Line( 237, 5, 237, 42);
	        $this->Line( 5, 42, 290, 42);
	        $this->Line( 5, 48, 290, 48);
	        $this->Line( 5, 53, 290, 53);
	        $this->Line( 5, 195, 290, 195);
	        $this->Line( 120, 195, 120, 205);
	        $this->Line( 5, 205, 175, 205);
	        $this->Line( 5, 195, 5, 205);
	        $this->Line( 120, 195, 120, 205);
	        $this->Line( 130, 195, 130, 205);
	        $this->Line( 150, 195, 150, 205);
	        
			$this->SetDash(0.4, 0.4); //5mm on, 5mm off
	        $this->SetDrawColor(200, 200, 200);
	        $this->SetLineWidth(0.01);
	        $this->Line(23, 53, 23, 195);
	        $this->Line(47, 53, 47, 195);
	        $this->Line(95, 53, 95, 195);
	        $this->Line(130, 53, 130, 195);
	        $this->Line(147, 53, 147, 195);
	        $this->Line(158, 53, 158, 195);
	        $this->Line(194, 53, 194, 195);
	        $this->Line(223, 53, 223, 195);
	        $this->Line(245, 53, 245, 195);
	        $this->Line(272, 53, 272, 195);
	         
	        
	        $this->AddCell(191, 32, "Total Pages Typed", "B", 10);
	        $this->AddCell(251, 32, "Total Amount", "B", 10);
	        $this->AddCell(82, 44, "Typist Invoices", "B");
	        $this->AddCell(220, 44, "Client Invoices", "B");
		    $this->AddCell(119.5, 200, "Total", "B", 10);
		    
		    $this->AddCell(177, 8, "TYPIST STATEMENT", "B", 13);
		    $this->AddCell(242, 8, "Statement Date", "", 13);
		    
		    
		    $this->addCell( 177, 16, "Statement Number : " . $member['accountnumber'] . $statementnumber, "B", 8);
		    $this->AddCell(242, 16, date("d-M-Y"), "", 10);
		    $this->AddCell(177, 24, "Date Range", "B", 10);
		    $this->AddCell(242, 24, $_POST['datefrom'] . " - " . $_POST['dateto'], "", 10);
		    
			$this->addAddress(" ", "iAfrica Transcriptions (Pty) LTD\n5th Floor, Schreiner Chambers\n94 Pritchard Street\nJohannesburg", 7, 7);
			
		    $this->AddCell(7, 33, $member['firstname'] . " " . $member['lastname'], "B", 11);
			$this->AddCell(7, 38, "Email : " .$member['email'] . " Tel : " . $member['landline'], "B", 11);
		    //			$this->addAddress(" ", $member['firstname'] . " " . $member['lastname'] . "\nEmail : " .$member['email'] . "\n" . "Tel : " . $member['landline'], 7, 29);
			
		    $this->addHeading( 110, 10, "Co Reg : ", "1997/00931/07", 20);
		    $this->addHeading( 110, 14, "VAT Reg : ", "4750166706", 20);
		    $this->addHeading( 110, 18, "Tel No :", "(011) 336-1455", 20);
		    $this->addHeading( 110, 22, "Fax No : ", "(011) 336-2403", 20);
		    
			
		    $this->SetFont('Arial','B',9);
		    
			
			$top = 39;

		    $this->SetFont('Arial','', 9);
				
			$cols=array( "Date Back"    => 20,
			             "Case Number"  => 23,
			             "Court / J33"  => 48,
			             "Rate"  => 33,
			             "P.P.P."  => 18,
			             "Pages" => 10,
			             "Amount"  => 18,
			             "Date" => 20,
						 "Client Invoice No" => 25,
 						 "Pages Invoiced" => 25,
 						 "Total Pages Typed" => 27,
						 "Amount " => 18
			);
		
			$this->addCols( $cols);
			$cols=array( "Date Back"    => "L",
			             "Case Number"  => "L",
			             "Court / J33"  => "L",
			             "Rate"  => "L",
			             "P.P.P."  => "R",
			             "Pages"  => "R",
			             "Amount" => "R",
			             "Date" => "L",
						 "Client Invoice No" => "L",
						 "Pages Invoiced" => "R",
 						 "Total Pages Typed" => "R",
						 "Amount " => "R"
			);
			$this->addLineFormat( $cols);
			$top = 55;
		}
		
		function __construct($orientation, $metric, $size) {
			global $member;
			global $top;
			global $statementnumber;
			global $amountpaid;
			global $amountdue;
			
			start_db();

			parent::__construct($orientation, $metric, $size);
	        
	        $this->SetAutoPageBreak(true, 0);
	                  
			
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
			
			if ($result) {
				$vatapplicable = "N";
				$totalamount = 0;
				$totalpages = 0;
				$totalclientamount = 0;
				$totalclientpages = 0;
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
					
				    if ($member['sessionid'] == null) {
				    	$page = $member['pages'];
				    	
				    } else {
				    	$page = $member['sessionpages'];
					}
					
					$totalpages += $page;
					$totalamount += ($member['typistprice'] * $page);
					$totalclientpages += ($member['casesessionpages'] + $member['casepages']);
					$totalclientamount += $member['total'];
						
					$line=array( "Date Back"    => $member['datebacktooffice'],
					             "Case Number"  => $member['casenumber'],
					             "Court / J33"  => ($member['courtname'] . " / " . $member['j33number']),
					             "Rate"  => $member['ratename'],
					             "P.P.P."  => "R " . number_format($member['typistprice'], 2),
					             "Pages"  => $page,
					             "Amount" => "R " . number_format($member['typistprice'] * $page, 2),
					             "Date" => $member['createddate'],
								 "Client Invoice No" => $member['clientinvoicenumber'],
								 "Pages Invoiced" => $member['invoicedpages'],
 								 "Total Pages Typed" => ($member['casesessionpages'] + $member['casepages']),
						 		 "Amount " => "R " . number_format($member['total'], 2)
					);
					
					if (($member['casesessionpages'] + $member['casepages']) > $member['invoicedpages']) {
						$this->SetTextColor(255, 0, 0);
						$size = $this->addLine2( $y, $line, 0, "B" );
						
					} else {
						$this->SetTextColor(0, 0, 0);
						$size = $this->addLine2( $y, $line, 0, "" );
					}

					$y += $size;
					
					$this->SetDrawColor(200, 200, 200);
					$this->SetLineWidth(0.01);
					$this->SetDash(0.4, 0.4); //5mm on, 5mm off
					$this->Line(5, $y - 1, 290, $y - 1);
						
					if ($y > 190) {
						$this->newPage();
						$y = $top;
					}
		 		}
				
			} else {
				logError($sql . " - " . mysql_error());
			}
			
		    $this->AddCell(135, 200, $totalpages, "B", 10);
		    $this->AddCell(151, 200, "R " . number_format($totalamount, 2), "B", 10);
	        $this->AddCell(202, 38.5, $totalpages, "B", 10);
	        $this->AddCell(256, 38.5, "R " . number_format($totalamount, 2), "B", 10);
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
		    $y1  = 48.5;
		    $y2  = $this->h - 35 - $y1;
		    $this->SetFont('Arial','B',7);
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
		    $this->SetFont('Arial','B',7);
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
			    $this->SetFont('Arial',$bold,7);
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
		
		function addCell($x, $y, $string, $font = "", $fontsize = 7) {
		    $this->SetXY( $x, $y );
		    $this->SetFont('Arial',$font,$fontsize);
		    $length = $this->GetStringWidth( $string );
		    $this->Cell( $length, 2, $string);
		}
		
		function addMultiCell($x, $y, $string, $font = "", $fontsize = 7) {
		    $this->SetXY( $x, $y );
		    $this->SetFont('Arial',$font,$fontsize);
		    $length = $this->GetStringWidth( $string );
		    $this->MultiCell( $length, 2, $string);
		}
	}

	$pdf = new Statement2Report( 'L', 'mm', 'A4');
	$pdf->Output();
	
?>