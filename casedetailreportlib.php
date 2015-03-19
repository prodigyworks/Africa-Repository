<?php
	require_once('fpdf.php');
	require_once('system-db.php');
	
	class CaseDetailReport extends FPDF
	{
		// private variables
		var $colonnes;
		var $format;
		var $angle=0;
		var $member;
		
		function newPage() {
			global $member;
			
			$this->AddPage();
		}
		
		function __construct($orientation, $metric, $size, $id) {
			global $member;
			
	        parent::__construct($orientation, $metric, $size);
	        
	        $this->SetAutoPageBreak(true, 0);
	                  
			//Include database connection details
			
			start_db();
		
			$margin = 7;
			$sql = "SELECT " .
					"DATE_FORMAT(B.datedelivered, '%d/%m/%Y') AS datedelivered, " .
					"DATE_FORMAT(B.datereceived, '%d/%m/%Y') AS datereceived, " .
					"DATE_FORMAT(B.depositdate, '%d/%m/%Y') AS depositdate, " .
					"DATE_FORMAT(B.datetransmitted, '%d/%m/%Y') AS datetransmitted, " .
					"DATE_FORMAT(B.transcriptrequestdate, '%d/%m/%Y') AS transcriptrequestdate, " .
					"B.id, B.plaintiff, B.casenumber, B.j33number, B.depositamount, B.depositamount, B.remarks, B.time, " .
					"D.name AS courtname, D.vatapplicable, D.address, " .
					"E.name AS provincename, " .
					"I.name AS clientcourtname " .
					"FROM {$_SESSION['DB_PREFIX']}cases B " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
					"ON D.id = B.courtid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
					"ON E.id = D.provinceid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}allcourts I " .
					"ON I.id = B.clientcourtid " .
					"WHERE B.id = $id " .
					"ORDER BY B.id";
			$result = mysql_query(getFilteredData($sql));
			
			if ($result) {
				$first = true;
				$total = 0;
				$subtotal = 0;
				$shipping = 0;
				$totalvat = 0;
				$depositamount = 0;
				$vatapplicable = "N";
				
				while (($member = mysql_fetch_assoc($result))) {
					$this->newPage();
		
					$first = false;
					$y = 112;
					$vatapplicable = $member['vatapplicable'];
					
					if ($member['depositamount'] != null) {
						$depositamount = $member['depositamount'];
					}
					
				    $this->addHeading( 12, 3, "Case Detail Report", " ", 37, 12);
				    
				    $this->addHeading( 12, 10, "Case ID: ", $member['id'], 37);
				    $this->addHeading( 12, 14, "J33 Number: ", $member['j33number'], 37);
				    $this->addHeading( 12, 18, "Province: ", $member['provincename'], 37);
				    $this->addHeading( 12, 22, "Client Court: ", $member['clientcourtname'], 37);
				    $this->addHeading( 12, 26, "Estimate Payment Date: ", $member['depositdate'], 37);
				    $this->addHeading( 12, 30, "Audio Request Date: ", $member['transcriptrequestdate'], 37);
//				    $this->addHeading( 12, 34, "Typist: ", $member['typistname'], 37);
				    $this->addHeading( 12, 38, "Time: ", $member['time'], 37);

				    $this->addHeading( 112, 10, "Case Number: ", $member['casenumber'], 37);
				    $this->addHeading( 112, 14, "Plaintiff: ", $member['plaintiff'], 37);
				    $this->addHeading( 112, 18, "Court: ", $member['courtname'], 37);
				    $this->addHeading( 112, 22, "Date Received: ", $member['datereceived'], 37);
				    $this->addHeading( 112, 26, "Estimate Amount: ", number_format($depositamount, 2), 37);
				    $this->addHeading( 112, 30, "Date Transmitted: ", $member['datetransmitted'], 37);
				    $this->addHeading( 112, 34, "Remarks: ", $member['remarks'], 37);
		 		}
				
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
		    $this->SetFont('Arial','',8);
		    
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
		    $y1  = 105;
		    $y2  = $this->h - 25 - $y1;
		    $this->SetFont('Arial','B',8);
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
			    $this->SetFont('Arial',$bold,8);
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
		
		function addHeading( $x1, $y1, $heading, $value, $margin = 36, $fontsize = 8) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',$fontsize);
		    $length = $this->GetStringWidth( $heading ) * 2;
	        $tailleTexte = $this->sizeOfText( $heading, $length );
		    $this->MultiCell( $length, 3, $heading);
		    
			$maxY = $this->GetY();
			
		    $this->SetXY( $x1 + $margin, $y1);
		    $this->SetFont('Arial','',$fontsize - 1);
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