<?php
	require_once('fpdf.php');
	require_once('system-db.php');
	
	class Case7DayReport extends FPDF
	{
		// private variables
		var $colonnes;
		var $format;
		var $angle=0;
		var $member;
		
		function newPage() {
			global $member;
			
			$this->AddPage();
		    $this->SetFont('Arial','', 6);
				
			$cols=array( "Case ID"    => 19,
			             "Case Number"  => 30,
			             "J33 Number"  => 28,
			             "Court"  => 46,
			             "Plaintiff"  => 42,
			             "Date Received"  => 25);
		
			$this->addCols( $cols);
			$cols=array( "Case ID"    => "L",
			             "Case Number"  => "L",
			             "J33 Number"  => "L",
			             "Court"  => "L",
			             "Plaintiff"  => "L",
			             "Date Received"  => "L");
			$this->addLineFormat( $cols);
		}
		
		function __construct($orientation, $metric, $size) {
			global $member;
			
	        parent::__construct($orientation, $metric, $size);
	        
	        $this->SetAutoPageBreak(true, 0);
	                  
			//Include database connection details
			
			start_db();
			
			$this->newPage();
		
			$margin = 7;
			$sql = "SELECT " .
					"DATE_FORMAT(B.datedelivered, '%d/%m/%Y') AS datedelivered, " .
					"DATE_FORMAT(B.datereceived, '%d/%m/%Y') AS datereceived, " .
					"DATE_FORMAT(B.depositdate, '%d/%m/%Y') AS depositdate, " .
					"DATE_FORMAT(B.transcriptrequestdate, '%d/%m/%Y') AS transcriptrequestdate, " .
					"B.id, B.plaintiff, B.casenumber, B.j33number, B.depositamount, B.typistname,B.depositamount, " .
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
					"WHERE B.datereceived < DATE_ADD(NOW(), INTERVAL -7 DAY) " .
					"AND (B.datetransmitted IS NULL OR B.datetransmitted = 0) " .
					"ORDER BY B.id";
			$result = mysql_query(getFilteredData($sql));
			
			if ($result) {
				$y = 13;
				
				while (($member = mysql_fetch_assoc($result))) {
					$line=array( "Case ID"    => $member['id'],
					             "Case Number"  => $member['casenumber'],
					             "J33 Number"  => $member['j33number'],
					             "Court"  => $member['courtname'],
					             "Plaintiff"  => $member['plaintiff'],
					             "Date Received"  => ($member['datereceived'] == "00/00/0000" ? " " : $member['datereceived'])
					            );
					             
					$size = $this->addLine( $y, $line );
					$y += $size;
					
					if ($y > 265) {
						$this->newPage();
						$y = 13;
					}
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
		
		
		function addCols( $tab ) {
		    global $colonnes;
		    
		    $r1  = 10;
		    $r2  = $this->w - ($r1 * 2) ;
		    $y1  = 6;
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
		
		function addHeading( $x1, $y1, $heading, $value, $margin = 36) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',8);
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