<?php
	require_once('fpdf.php');
	require_once('system-db.php');
	require_once('simple_html_dom.php');
	
	class CreditReport extends FPDF
	{
		// private variables
		var $colonnes;
		var $format;
		var $angle=0;
		var $member;
		var $top = 0;
		
		function newPage() {
			global $member;
			global $top;
			
			$this->AddPage();
			$this->Image("images/quoteheader.png", 10, 1);
			
		    if ($member['vatapplicable'] == "Y") {
			    $heading = $member['privatebankingdetails'];
			    
		    } else {
			    $heading = $member['bankingdetails'];
		    }
		    
			$this->addAddress( "Banking Details", $heading, 10, 267);
		    		    
		    $this->SetXY( 10, 283 );
		    
		    $this->SetXY(82, 20);
		    $this->SetFont('Arial','B',19);
		    $length = $this->GetStringWidth( "Credit Note" );
		    $this->MultiCell($length * 2, 6, "Credit Note");
		    
		    $this->SetXY(89, 25);
		    $this->SetFont('Arial','B',8);
		    $length = $this->GetStringWidth( "VAT : 4750166706" );
		    $this->MultiCell($length * 2, 6, "VAT : 4750166706");
		    
			$this->addAddress(" ", $member['officename'] . "\n" .$member['officeaddress'] . "\n" . "Tel : " . $member['telephone'] . "\n" . "Fax : " . $member['officefax'] . "\n" . "Email : " . $member['officeemail'] , 13, 34);
			
		    $this->addHeading( 100, 40, "Admin Clerk: ", $member['firstname'] . " " . $member['lastname'], 30);
		    $this->addHeading( 100, 44, "Date: ", $member['creditdate'], 30);
		    $this->addHeading( 100, 48, "Credit Note No: ", $member['creditnumber'], 30);
		    
		    if ($member['vatapplicable'] == "Y") {
			    $this->addHeading( 100, 52, "Terms: ", $member['terms'], 30);
		    }
		    
		    $this->addHeading( 100, 58, "Client Name : ", $member['courtname'], 30, 0, 'B');
		    $this->addHeading( 100, 62, "Contact Name  : ", $member['courtcontact'], 30);
		    
		    if ($member['courttelephone'] == null || trim($member['courttelephone']) == "" || trim($member['courttelephone']) == "-") {
			    $this->addHeading( 100, 66, "Tel : ", $member['courtmobile'], 30);
			    
		    } else {
			    $this->addHeading( 100, 66, "Tel : ", $member['courttelephone'], 30);
		    }
		    
		    $this->addHeading( 100, 70, "Fax : ", $member['courtfax'], 30);
		    $this->addHeading( 100, 74, "Email : ", $member['courtemail'], 30);
			
		    $this->SetFont('Arial','B',10);
		    
			$this->addAddress( "Billing Address :", $member['toaddress'], 13, 63);
			
			$top = 94;

			if ($member['vatapplicable'] == "N") {
			    $this->addHeading( 30, $top, "J33 No: ", $member['j33number'], 30); $top += 4;
			}
			
		    $this->addHeading( 30, $top, "Case No: ", $member['casenumber'], 30); $top += 4;
		    $this->addHeading( 30, $top, "Parties: ", $member['plaintiff'], 30); $top += 4;
		    $this->addHeading( 30, $top, "Type: ", $member['transcripttype'], 30); $top += 4;
		    $this->addHeading( 30, $top, "Our Ref No: ", $member['ourref'], 30); $top += 4;
		    $this->addHeading( 30, $top, "Your Ref No: ", $member['yourref'], 30); $top += 6;
		    
		    $this->SetFont('Arial','', 10);
				
			$cols=array( "Quantity"    => 18,
			             "Description"  => 93,
			             "Unit Price"  => 39.5,
			             "Total"  => 39.5);
		
			$this->addCols( $cols);
			$cols=array( "Quantity"    => "R",
			             "Description"  => "L",
			             "Unit Price"  => "R",
			             "Total"  => "R");
			$this->addLineFormat( $cols);
		}
		
		function __construct($orientation, $metric, $size, $id) {
			global $member;
			global $top;
			
	        parent::__construct($orientation, $metric, $size);
	        
	        $this->SetAutoPageBreak(true, 0);
	                  
			//Include database connection details
			
			start_db();
		
			$margin = 7;
			$sql = "SELECT A.*, " .
					"DATE_FORMAT(A.creditdate, '%d/%m/%Y') AS creditdate, " .
					"DATE_FORMAT(A.paymentdate, '%d/%m/%Y') AS paymentdate, " .
					"DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate, " .
					"DATE_FORMAT(B.datedelivered, '%d/%m/%Y') AS datedelivered, " .
					"A.deladdress, A.toaddress, B.plaintiff, B.casenumber, B.transcripttype, B.j33number, B.depositamount, " .
					"D.name AS courtname, D.vatapplicable, D.address, D.fax AS courtfax, D.cellphone AS courtmobile, " .
					"D.email AS courtemail, D.telephone AS courttelephone, D.contact AS courtcontact, " .
					"E.name AS provincename, F.name AS terms, G.firstname, G.lastname, G.mobile, G.landline, G.email, G.fax, " .
					"I.name AS clientcourtname, " .
					"O.privatebankingdetails, O.bankingdetails, " .
					"O.address AS officeaddress, O.email AS officeemail, O.telephone, O.contact, O.fax AS officefax, O.name AS officename  " .
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
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}allcourts I " .
					"ON I.id = B.clientcourtid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}offices J " .
					"ON J.id = A.officeid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}offices O " .
					"ON O.id = A.officeid " .
					"WHERE A.caseid = $id " .
					"ORDER BY B.id";
			$result = mysql_query(getFilteredData($sql));
			
			if ($result) {
				$total = 0;
				$subtotal = 0;
				$shipping = 0;
				$totalvat = 0;
				$depositamount = 0;
				$vatapplicable = "N";
				
				while (($member = mysql_fetch_assoc($result))) {
					$this->newPage();
		
					$first = false;
					$y = $top;
					$vatapplicable = $member['vatapplicable'];
					$invoiceid = $member['id'];
//					$description = $this->stripHTML($member['description']);
					$description = $member['description'];
					
					if ($member['depositamount'] != null) {
						$depositamount = $member['depositamount'];
					}
					
					$sql = "SELECT C.id, C.description, C.qty, C.vat, C.vatrate, C.total, C.unitprice, " .
							"H.name AS templatename " .
							"FROM {$_SESSION['DB_PREFIX']}invoiceitems C " .
							"INNER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates H " .
							"ON H.id = C.templateid " .
							"WHERE C.invoiceid = $invoiceid " .
							"ORDER BY C.id";
					$itemresult = mysql_query($sql);
					
					if ($itemresult) {
						while (($itemmember = mysql_fetch_assoc($itemresult))) {
							$line=array( 
									 "Quantity"    => number_format($itemmember['qty'], 0),
						             "Description"  => $itemmember['templatename'],
						             "Unit Price"  => "R " . number_format($itemmember['unitprice'], 2),
						             "Total"  => "R " . number_format($itemmember['total'], 2)
						         );
							             
							$size = $this->addLine( $y, $line );
							$y += $size;
							
							if ($y > 235) {
								$this->newPage();
								$y = $top;
							}
		
							$subtotal += $itemmember['total'] - $member['vat'];
							$shipping = $itemmember['shippinghandling'];
							$totalvat += $itemmember['vat'];
							
							$total += $itemmember['total'];
						}
						
						$y += 4;
						
						if ($y > 175) {
							$this->newPage();
							$y = $top;
						}
						
						$line=array(
								 "Quantity"    => " ",
					             "Description"  => $description,
					             "Unit Price"  => " ",
					             "Total"  => " "
					         );
						             
						$size = $this->addLine( $y, $line );
						$y += $size;
						
						if ($y > 235) {
							$this->newPage();
							$y = $top;
						}
						
					} else {
						logError($sql . " - " . mysql_error());
					}
		 		}
				
			} else {
				logError($sql . " - " . mysql_error());
			}
			
			
			if ($vatapplicable == "Y") {
		        $this->Line( 121, 240, 200, 240);
		        $this->Line( 121, 245, 200, 245);
		        $this->Line( 121, 250, 200, 250);
			}
			
	        $this->Line( 121, 255, 200, 255);
			
			if ($vatapplicable == "Y") {
				$line=array( 
						 "Quantity"    => " ",
			             "Description"  => " ",
			             "Unit Price"  => "VAT (" . number_format(getSiteConfigData()->vatrate, 0) . "%)",
			             "Total"  => "R " . number_format($totalvat, 2)
			         );
				             
				$size = $this->addLine(242, $line );
				
				$line=array( 
						 "Quantity"    => " ",
			             "Description"  => " ",
			             "Unit Price"  => "TOTAL",
			             "Total"  => "R " . number_format($total, 2)
			         );
				             
				$size = $this->addLine(247, $line );
				
				$line=array( 
						 "Quantity"    => " ",
			             "Description"  => " ",
			             "Unit Price"  => "DEPOSIT",
			             "Total"  => "R " . number_format($depositamount, 2)
			         );
				             
				$size = $this->addLine(252, $line );
				
				$line=array( 
						 "Quantity"    => " ",
			             "Description"  => " ",
			             "Unit Price"   => "TOTAL DUE",
			             "Total"  => "R " . number_format($total - $depositamount, 2)
			         );
				             
				$size = $this->addLine(258, $line, 0, 'B');
				
			} else {
				$line=array( 
						 "Quantity"    => " ",
			             "Description"  => " ",
			             "Unit Price"   => "TOTAL",
			             "Total"  => "R " . number_format($total, 2)
			         );
				             
				$size = $this->addLine(258, $line, 0, 'B');
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
		    $this->SetFont('Arial','B',10);
		    $length = $this->GetStringWidth( $nom );
		    $this->Cell( $length, 2, $nom);
		    $this->SetXY( $x1, $y1 + 3 );
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
		    $this->SetFont('Arial','',10);
		    $this->SetTextColor(200, 200, 200);
		    $length = $this->GetStringWidth( $nom );
		    $this->Cell( $length, 2, $nom);
		    $this->SetXY( $x1, $y1 + 4 );
		    $this->SetFont('Arial','',10);
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
		    $y1  = 85;
		    $y2  = $this->h - 35 - $y1;
		    $this->SetFont('Arial','B',10);
		    $this->SetXY( $r1, $y1 );
		    $this->Rect( $r1, $y1, $r2, $y2, "D");
		    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
		    $colX = $r1;
		    $colonnes = $tab;
		    
		    while ( list( $lib, $pos ) = each ($tab) ) {
		        $this->SetXY( $colX, $y1+2 );
		        $this->Cell( $pos, 1, $lib, 0, 0, "C");
		        $colX += $pos;
		        $this->Line( $colX, $y1, $colX, $y1+$y2);
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
		
		    $ordonnee     = 10;
		    $maxSize      = $ligne;
		
		    reset( $colonnes );
		    while ( list( $lib, $pos ) = each ($colonnes) )
		    {
			    $this->SetFont('Arial',$bold,10);
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
		    $this->SetFont('Arial','B',10);
		    $length = $this->GetStringWidth( $heading ) * 2;
	        $tailleTexte = $this->sizeOfText( $heading, $length );
		    $this->MultiCell( $length, 3, $heading);
		    
			$maxY = $this->GetY();
			
		    $this->SetXY( $x1 + $margin, $y1);
		    $this->SetFont('Arial','',10);
		    $length = $this->GetStringWidth( $value . " " ) * 2;
	        $tailleTexte = $this->sizeOfText( $value, $length );
		    $this->MultiCell( $length, 3, $value);
		    
		    if ($this->GetY() > $maxY) {
			    $maxY = $this->GetY();
		    }
		    
		    
			if ($maxY > 260) {
				$maxY = $this->newPage();
			}
		    
		    return $maxY;
		}
		
		function addCell($x, $y, $w, $h, $string) {
		    $this->Rect( $x, $y, $w, $h, "D");
		    $this->SetXY( $x + 1, $y + 1);
		    $length = $this->GetStringWidth($string);
		    $lignes = $this->sizeOfText( $string, $length) ;
		    $this->MultiCell( $w - 2, 3, $string, 0, 'C');
		}
	}
?>