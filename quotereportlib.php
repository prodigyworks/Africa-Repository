<?php
	require_once('fpdf.php');
	require_once('system-db.php');
	require_once('simple_html_dom.php');
	
	class QuoteReport extends FPDF
	{
		// private variables
		var $colonnes;
		var $format;
		var $angle=0;
		var $member;
		
		function stripHTML($html) {
			$htmlDOM = str_get_html("<html>" . $html . "</html>");
			
			foreach($htmlDOM->find('li') as $li) {
				$li->innertext = "\n*  " . $li->innertext;
			}
			
			$txt = $htmlDOM->plaintext;
			$txt= str_replace("&pound;", "£", str_replace("&amp;", "&", str_replace("&lt;", "<", str_replace("&gt;", "<",  str_replace("&ndash;", "-", str_replace("&nbsp;", " ", $txt))))));
			
			$buildingarr = array();
			$buildingstr = "";
			
			$strings = explode(" ", $txt);
			
			foreach($strings as $str) {
				if ($this->GetStringWidth( $buildingstr . " " . $str ) > 182 || strpos($str, "\n") !== FALSE) {
					$buildingarr[] = $buildingstr;
					$buildingstr = str_replace("\n", "", $str);
					
				} else {
					if ($buildingstr != "") {
						$buildingstr .= " " . $str;
						
					} else {
						$buildingstr  = $str;
					}
				}
			}
			
			$buildingarr[] = $buildingstr;
			$buildingstr = "";
			
			foreach($buildingarr as $str) {
				if ($buildingstr != "") {
					$buildingstr .= "\n" . $str;
					
				} else {
					$buildingstr  = $str;
				}
			}
			
			return $buildingstr;	 
		}
		
		function newPage() {
			global $member;
			
			$this->AddPage();
			$this->Image("images/quoteheader.png", 10, 1);
			
		    if ($member['vatapplicable'] == "Y") {
			    $heading = $member['privatebankingdetails'];
			    
		    } else {
			    $heading = $member['bankingdetails'];
		    }
		    
			$this->addAddress( "Banking Details", $heading, 10, 267);
		    		    
		    $this->SetXY( 10, 283 );
		    
		    $this->SetXY(62, 25);
		    $this->SetFont('Arial','B',19);
		    $length = $this->GetStringWidth( "Estimate Cost Projection" );
		    $this->MultiCell($length * 2, 6, "Estimate Cost Projection");
		    
		    
//			$this->addAddress(" ", $member['officeaddress'] . "\n" . GetEmail(getLoggedOnMemberID()) , 13, 37);
			$this->addAddress(" ", $member['officename'] . "\n" .$member['officeaddress'] . "\n" . "Tel : " . $member['telephone'] . "\n" . "Fax : " . $member['officefax'] . "\n" . "Email : " . $member['officeemail'] , 13, 34);
			
		    $this->addHeading( 100, 40, "Admin Clerk: ", $member['firstname'] . " " . $member['lastname'], 30);
		    $this->addHeading( 100, 44, "Date: ", $member['createddate'], 30);
		    $this->addHeading( 100, 48, "Ref No: ", $member['quotenumber'], 30);
		    $this->addHeading( 100, 58, "Client Name : ", $member['courtname'], 30, 0, 'B');
		    $this->addHeading( 100, 62, "Contact Name  : ", $member['courtcontact'], 30);
		    
		    if ($member['courttelephone2'] == null || trim($member['courttelephone2']) == "" || trim($member['courttelephone2']) == "-") {
			    $this->addHeading( 100, 66, "Tel : ", $member['courtcellphone2'], 30);
			    
		    } else {
			    $this->addHeading( 100, 66, "Tel : ", $member['courttelephone2'], 30);
		    }
		    
		    $this->addHeading( 100, 70, "Fax : ", $member['courtfax2'], 30);
		    $this->addHeading( 100, 74, "Email : ", $member['courtemail2'], 30);
			
		    $this->SetFont('Arial','B',10);
		    
			$this->addAddress( "Billing Address :", $member['toaddress'], 13, 63);
			

		    $height = $this->addHeading( 41, 94, "Case No: ", $member['casenumber'], 30);
		    $height = $this->addHeading( 41, $height, "Parties: ", $member['plaintiff'], 30, 60);
		    $height = $this->addHeading( 41, $height, "Type: ", $member['transcripttype'], 30);
		    $height = $this->addHeading( 41, $height, "Our Ref No: ", $member['ourref'], 30);
		    $height = $this->addHeading( 41, $height, "Your Ref No: ", $member['yourref'], 30);
			              
		    $this->SetFont('Arial','', 10);
				
			$cols=array( "Quantity (Pages)"    => 31,
			             "Description"  => 91,
			             "Unit Price"  => 34,
			             "+- Total"  => 34);
		
			$this->addCols( $cols);
			$cols=array( "Quantity (Pages)"    => "R",
			             "Description"  => "L",
			             "Unit Price"  => "R",
			             "+- Total"  => "R");
			$this->addLineFormat( $cols);
			
			return 117;
		}
		
		function __construct($orientation, $metric, $size, $id) {
			global $member;
			
	        parent::__construct($orientation, $metric, $size);
	        
	        $this->SetAutoPageBreak(true, 0);
	                  
			//Include database connection details
			
			start_db();
		
			$margin = 7;
			$sql = "SELECT A.*, " .
					"DATE_FORMAT(A.paymentdate, '%d/%m/%Y') AS paymentdate, " .
					"DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate, " .
					"DATE_FORMAT(B.datedelivered, '%d/%m/%Y') AS datedelivered, " .
					"A.deladdress, A.toaddress, B.plaintiff, B.casenumber, B.transcripttype, B.j33number, B.depositamount, " .
					"D.name AS courtname, D.vatapplicable, D.address, D.fax AS courtfax, D.cellphone AS courtmobile, " .
					"D.email AS courtemail, D.telephone AS courttelephone, " . 
					"(SELECT DE.fullname FROM {$_SESSION['DB_PREFIX']}contacts DE WHERE DE.courtid = D.id ORDER BY DE.id LIMIT 1) AS courtcontact, " .
					"(SELECT DE.telephone FROM {$_SESSION['DB_PREFIX']}contacts DE WHERE DE.courtid = D.id ORDER BY DE.id LIMIT 1) AS courttelephone2, " .
					"(SELECT DE.cellphone FROM {$_SESSION['DB_PREFIX']}contacts DE WHERE DE.courtid = D.id ORDER BY DE.id LIMIT 1) AS courtcellphone2, " .
					"(SELECT DE.fax FROM {$_SESSION['DB_PREFIX']}contacts DE WHERE DE.courtid = D.id ORDER BY DE.id LIMIT 1) AS courtfax2, " .
					"(SELECT DE.email FROM {$_SESSION['DB_PREFIX']}contacts DE WHERE DE.courtid = D.id ORDER BY DE.id LIMIT 1) AS courtemail2, " .
					"E.name AS provincename, F.name AS terms, G.firstname, G.lastname, G.mobile, G.landline, G.email, G.fax, " .
					"I.name AS clientcourtname, " .
					"O.privatebankingdetails, O.bankingdetails, " .
					"O.address AS officeaddress, O.email AS officeemail, O.telephone, O.contact, O.fax AS officefax, O.name AS officename  " .
					"FROM {$_SESSION['DB_PREFIX']}quotes A " .
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
					"INNER JOIN {$_SESSION['DB_PREFIX']}offices O " .
					"ON O.id = A.officeid " .
					"WHERE A.id = $id " .
					"ORDER BY B.id";
			$result = mysql_query(getFilteredData($sql));
			
			if ($result) {
				$total = 0;
				$subtotal = 0;
				$shipping = 0;
				$totalvat = 0;
				$description = "";
				$depositamount = 0;
				$vatapplicable = "N";
				$showTotal = true;
				
				while (($member = mysql_fetch_assoc($result))) {
					$y = $this->newPage();
		
					$first = false;
					$vatapplicable = $member['vatapplicable'];
										    logError($member['description'], false);
					
//					$description = $this->stripHTML($member['description']);
					$description = $member['description'];
					$showTotal = ($member['na'] == "N" ? true : false);
					
					if ($member['depositrequired'] != null) {
						$depositamount = $member['depositrequired'];
					}
					
					$sql = "SELECT C.id, C.description, C.qty, C.vat, C.vatrate, C.total, C.unitprice, " .
							"H.name AS templatename " .
							"FROM {$_SESSION['DB_PREFIX']}quoteitems C " .
							"INNER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates H " .
							"ON H.id = C.templateid " .
							"WHERE C.quoteid = $id " .
							"ORDER BY C.id";
					$itemresult = mysql_query($sql);
					
					if ($itemresult) {
						while (($itemmember = mysql_fetch_assoc($itemresult))) {
							$line=array( 
									 "Quantity (Pages)"    => "+- " . number_format($itemmember['qty'], 0),
						             "Description"  => $itemmember['templatename'],
						             "Unit Price"  => "R " . number_format($itemmember['unitprice'], 2),
						             "+- Total"  => "R " . number_format($itemmember['total'], 2)
						         );
							             
							$size = $this->addLine( $y, $line );
							$y += $size;
							
							if ($y > 235) {
								$y = $this->newPage();
							}
		
							$subtotal += $itemmember['total'] - $member['vat'];
							$shipping = $itemmember['shippinghandling'];
							$totalvat += $itemmember['vat'];
							
							$total += $itemmember['total'];
						}
						
						if (! $showTotal) {
							$line=array( 
									 "Quantity (Pages)"    => "+- 1",
						             "Description"  => "Admin Fee",
						             "Unit Price"  => "R " . number_format(250, 2),
						             "+- Total"  => "R " . number_format(250, 2)
						         );
							             
							$size = $this->addLine( $y, $line );
							$y += $size;
							
							if ($y > 235) {
								$y = $this->newPage();
							}
		
							$subtotal += 250;
							$total += 250;						
						}
						
						
						$y += 4;
						
						if ($y > 175) {
							$y = $this->newPage();
						}
						
					    $this->SetXY( 41, $y );
					    $this->SetFont('Arial','',8);
						
					    $length = $this->GetStringWidth( $description );
					    $lignes = $this->sizeOfText( $description, $length) ;
					    $this->MultiCell(100, 5, $description, 0, 'L');
					    $y = $this->GetY();
						
						if ($y > 175) {
							$y = $this->newPage();
						}
						
						$y += 2;
				        $y = $this->addHeading( 41, $y, "1.", "iAfrica is not responsible for the delay caused by the judge to revise the matter.", 5, 85, '');
				        $y = $this->addHeading( 41, $y, "2.", "All judgements & sentences must be forwarded to the judge for revisions.", 5, 85, '');
				        $y = $this->addHeading( 41, $y, "3.", "iAfrica cannot guarantee its clients to when they will have the signed judgement back.", 5, 85, '');
				        $y = $this->addHeading( 41, $y, "4.", "The rate chosen by the client is only a transcription period, excluding the period which the judge will need in  completing the matter.", 5, 85, '');
				        $y = $this->addHeading( 41, $y, "5.", "+- Total amount plus an admin fee of R 250 required.", 5, 85, '');
				        $y = $this->addHeading( 41, $y, "6.", "Please note that this is just an Estimate Cost unit we get the final number of the pages transcribed.", 5, 85, '');
						
					} else {
						logError($sql . " - " . mysql_error());
					}
		 		}
				
			} else {
				logError($sql . " - " . mysql_error());
			}
			
			$terms = "                                     \n            * PLEASE NOTE: QUOTE ONLY VALID FOR 7 DAYS\n  * Please Use Our Reference as a Reference for All EFT Payments\n           * PLEASE FAX PROOF OF PAYMENT TO 'Office Fax'\n          Please use the office assigned to the quote’s fax number.";
			
		    $this->SetXY( 43, 230 );
		    $this->SetFont('Arial','',8);
		    
		    $length = $this->GetStringWidth( $terms );
		    $lignes = $this->sizeOfText( $terms, $length) ;
		    $this->MultiCell(100, 5, $terms, 0, 'L');
			 
		
	        $this->Line( 41, 225, 132, 225);
	        $this->Line( 132, 245, 200, 245);
	        $this->Line( 132, 250, 200, 250);
	        $this->Line( 132, 255, 200, 255);
			
			
			$line=array( 
					 "Quantity (Pages)"    => " ",
		             "Description"  => " ",
		             "Unit Price"  => "SUB TOTAL",
		             "+- Total"  => ($showTotal ? "R " . number_format($subtotal, 2) : "N/A")
		         );
			             
			$size = $this->addLine(247, $line );
			
			
			$line=array( 
					 "Quantity (Pages)"    => " ",
		             "Description"  => " ",
		             "Unit Price"  => "VAT (" . number_format(getSiteConfigData()->vatrate, 0) . "%)",
		             "+- Total"  => ($showTotal ? "R " . number_format($totalvat, 2) : "N/A")
		         );
			             
			$size = $this->addLine(252, $line );
			
			$line=array( 
					 "Quantity (Pages)"    => " ",
		             "Description"  => " ",
		             "Unit Price"   => "+- Total",
		             "+- Total"  => ($showTotal ? "R " . number_format($total, 2) : "N/A")
		         );
		         
			$size = $this->addLine(258, $line, 0, 'B' );
			             
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
		
		function addHeading( $x1, $y1, $heading, $value, $margin = 36, $maxwidth = -1, $headerfont =  'B') {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial',$headerfont,10);
		    $length = $this->GetStringWidth( $heading ) * 2;
	        $tailleTexte = $this->sizeOfText( $heading, $length );
		    $this->MultiCell( $length, 3, $heading);
		    
			$maxY = $this->GetY();
			
		    $this->SetXY( $x1 + $margin, $y1);
		    $this->SetFont('Arial','',10);
		    
		    if ($maxwidth == -1) {
			    $length = $this->GetStringWidth( $value . " " ) * 2;
		    	
		    } else {
		    	$length = $maxwidth;
		    }
		    
	        $tailleTexte = $this->sizeOfText( $value, $length );
		    $this->MultiCell( $length, 3.5, $value);
		    
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