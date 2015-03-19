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
		
		function newPage() {
			global $y;
			
			$this->addPage();
			
			$this->addHeading( 10, 2, "Report Invoices");
			
			$this->addSubHeading(10, 8, "From Date", (isset($_POST['datefrom'])) ? $_POST['datefrom'] : "");
			$this->addSubHeading(10, 12, "To Date", (isset($_POST['dateto'])) ? $_POST['dateto'] : "");
			
			if ($_POST['status'] == "Y") {
				$this->addSubHeading(10, 16, "Status", "Paid");

			} else if ($_POST['status'] == "N") {
				$this->addSubHeading(10, 16, "Status", "Outstanding");
			
			} else {
				$this->addSubHeading(10, 16, "Status", "All");
			}
			
			if (isset($_POST['courtid']) && $_POST['courtid'] != "0") {
				$sql = "SELECT name  " .
						"FROM {$_SESSION['DB_PREFIX']}courts " .
						"WHERE id = " . $_POST['courtid'] . " ";
				$result = mysql_query($sql);
				
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$this->addSubHeading(10, 20, "Court / Client", $member['name']);
					}
				}

			} else {
				$this->addSubHeading(10, 20, "Court / Client", "");
			}
			
			
		    $this->SetFont('Arial','', 6);
			$cols=array( "Invoice Date"    => 23,
			             "Contact"  => 35,
			             "Province"  => 40,
			             "Court / Client"  => 45,
			             "Status"  => 12,
			             "Penalty"  => 15,
			             "Total"  => 20);
		
			$this->addCols( $cols);
			$cols=array( "Invoice Date"    => "L",
			             "Contact"  => "L",
			             "Province"  => "L",
			             "Court / Client"  => "L",
			             "Status"  => "L",
			             "Penalty"  => "L",
			             "Total"  => "R");
			$this->addLineFormat( $cols);
			$y = 33;
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
		
			$sql = "SELECT A.*, DATE_FORMAT(A.createddate, '%d/%m/%Y') AS paymentdate2, " .
					"D.name AS courtname, E.name AS provincename, F.name AS terms, G.firstname, G.lastname " .
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
					"WHERE 1 = 1 $and " .
					"ORDER BY A.id DESC";
			$result = mysql_query(getFilteredData($sql));
			
			if ($result) {
				$first = true;
				$total = 0;
				
				while (($member = mysql_fetch_assoc($result))) {
					if ($first) {
						$this->newPage();
						$first = false;
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
							 "Invoice Date"    => $member['paymentdate2'] . " ",
				             "Contact"  => $member['firstname'] . " " . $member['lastname'],
				             "Province"  => $member['provincename'] . " ",
				             "Court / Client"  => $member['courtname'] . " ",
				             "Status"  => ($member['paid'] == "Y" ? "Paid" : "Unpaid") . " ",
				             "Penalty"  => $penalty,
				             "Total"  => "R " . number_format($subtotal, 2)
				         );

					$size = $this->addLine( $y, $line );
					$y += $size;
					$total += $subtotal;
					
					if ($y > 265) {
						$this->newPage();
					}
		 		}
		 		$this->Line( 165, $y + 0.5, 200, $y + 0.5);
		 		$this->Line( 165, $y + 5, 200, $y + 5);
		 		$this->SetFont('Arial','B',7);
		 		 
				$line=array( 
						 "Invoice Date"    => " ",
			             "Contact"  => " ",
			             "Province"  => " ",
			             "Court / Client"  => " ",
			             "Status"  => " ",
			             "Penalty"  => "Total : ",
			             "Total"  => "R " . number_format($total, 2)
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
		    $y1  = 25;
		    $y2  = $this->h - 25 - $y1;
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