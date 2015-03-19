<?php
	include("system-header.php"); 
	
	$substringstart = 0;
	
	function startsWith($Haystack, $Needle){
	    // Recommended version, using strpos
	    return strpos($Haystack, $Needle) === 0;
	}
	
	if (isset($_FILES['casecsvfile']) && $_FILES['casecsvfile']['tmp_name'] != "") {
		if ($_FILES["casecsvfile"]["error"] > 0) {
			echo "Error: " . $_FILES["casecsvfile"]["error"] . "<br />";
			
		} else {
		  	echo "Upload: " . $_FILES["casecsvfile"]["name"] . "<br />";
		  	echo "Type: " . $_FILES["casecsvfile"]["type"] . "<br />";
		  	echo "Size: " . ($_FILES["casecsvfile"]["size"] / 1024) . " Kb<br />";
		  	echo "Stored in: " . $_FILES["casecsvfile"]["tmp_name"] . "<br>";
		}
		
		$subcat1 = "";
		$subcat2 = "";
		$row = 1;
		
		try {
			if (($handle = fopen($_FILES['casecsvfile']['tmp_name'], "r")) !== FALSE) {
			    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			        if ($row++ <= 2) {
			        	continue;
			        }
			        
			        $num = count($data);
			        
			        if ($data[2] != "" && $data[3] != "" && $num >= 15) {
			        	$daterec = convertStringToDate($data[0]);
			        	$court = mysql_escape_string($data[1]);
			        	$casenumber = mysql_escape_string($data[2]);
			        	$j33number = mysql_escape_string($data[3]);
			        	$parties = mysql_escape_string($data[4]);
			        	$nrreceivedcassettes = mysql_escape_string($data[5]);
			        	$rate = mysql_escape_string($data[6]);
			        	$transcriptrequestdate = convertStringToDate($data[7]);
			        	$typist	= mysql_escape_string($data[8]);
			        	$dateelectroniccopysubcourt = convertStringToDate($data[9]);	
			        	$datesenttotypist =  convertStringToDate($data[10]);
			        	$datebackfromtypist =  convertStringToDate($data[11]);
			        	$datahardcopyreturnedtocourt = convertStringToDate($data[12]);	
			        	$invoicenumber = mysql_escape_string($data[13]);
			        	$remarks = mysql_escape_string($data[14]);
			        	
						$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}typist " .
								"(name, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
								"VALUES " .
								"('$typist', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
								
						$result = mysql_query($qry);
	        	
	        			if (! $result) {
							if (mysql_errno() == 1062) {
								$qry = "SELECT id " .
										"FROM {$_SESSION['DB_PREFIX']}typist " .
										"WHERE name = '$typist'";
								$result = mysql_query($qry);
								
								//Check whether the query was successful or not
								if ($result) {
									while (($member = mysql_fetch_assoc($result))) {
										$typistid = $member['id'];
									}
								}
								
							} else {
								logError($qry . " - " . mysql_error());
							}
							
	        			} else {
				        	$typistid =  mysql_insert_id();
	        			}
	        					        	
						$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}invoiceitemtemplates " .
								"(name, type, clientprice, typistprice, courtprice, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
								"VALUES " .
								"('$rate', 'T', 0, 0, 0, NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
								
						$result = mysql_query($qry);
	        	
	        			if (! $result) {
							if (mysql_errno() == 1062) {
								$qry = "SELECT id " .
										"FROM {$_SESSION['DB_PREFIX']}invoiceitemtemplates " .
										"WHERE name = '$rate'";
								$result = mysql_query($qry);
								
								//Check whether the query was successful or not
								if ($result) {
									while (($member = mysql_fetch_assoc($result))) {
										$rateid = $member['id'];
									}
								}
								
							} else {
								logError($qry . " - " . mysql_error());
							}
							
	        			} else {
				        	$rateid =  mysql_insert_id();
	        			}
								
						$courtid = 0;
						$qry = "SELECT id " .
								"FROM {$_SESSION['DB_PREFIX']}courts " .
								"WHERE name = '$court'";
						$result = mysql_query($qry);
						
						//Check whether the query was successful or not
						if ($result) {
							while (($member = mysql_fetch_assoc($result))) {
								$courtid = $member['id'];
							}
								
						} else {
							logError($qry . " - " . mysql_error());
						}
			        	
						$found = false;
						$qry = "SELECT id " .
								"FROM {$_SESSION['DB_PREFIX']}cases " .
								"WHERE casenumber = '$casenumber' " .
								"AND j33number = '$j33number' " .
								"AND plaintiff = '$parties' " .
								"AND datereceived = '$daterec' ";
						$result = mysql_query($qry);
						
						//Check whether the query was successful or not
						if ($result) {
							while (($member = mysql_fetch_assoc($result))) {
								$found = true;
								$caseid = $member['id'];
							}
								
						} else {
							logError($qry . " - " . mysql_error());
						}
							
						if ($found) {
							$qry = "UPDATE {$_SESSION['DB_PREFIX']}cases SET " .
									"datereceived = '$daterec', " .
									"courtid = $courtid, " .
									"plaintiff = '$parties', " .
									"nrreceivedmedia = '$nrreceivedcassettes', " .
									"rate = $rateid, " .
									"datehardcopyretcourt = '$datehardcopyretcourt', " .
									"transcriptrequestdate = '$transcriptrequestdate', " .
									"datebackfromtypist = '$datebackfromtypist', " .
									"dateelectroniccopysubcourt = '$datahardcopyreturnedtocourt', " .
									"remarks = '$remarks', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
									"WHERE casenumber = '$casenumber' " .
									"AND j33number = '$j33number' " .
									"AND plaintiff = '$parties' " .
									"AND datereceived = '$daterec' ";
							$result = mysql_query($qry);
							
							echo "DUPLICATE CASE: $casenumber J33NUM:$j33number<br>";
		        	
		        			if (! $result) {
								logError($qry . " - " . mysql_error());
		        			}
							
						} else {
							$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}cases " .
									"(" .
									"datereceived, courtid, casenumber, j33number, plaintiff, nrreceivedmedia, " .
									"rate, transcriptrequestdate, datehardcopyretcourt, datebackfromtypist, dateelectroniccopysubcourt, " .
									"remarks, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid" .
									") " .
									"VALUES " .
									"(" .
									"'$daterec', $courtid, '$casenumber', '$j33number', '$parties', '$nrreceivedcassettes', " .
									"'$rateid', '$transcriptrequestdate', '$datahardcopyreturnedtocourt', '$datebackfromtypist', '$dateelectroniccopysubcourt', " .
									"'$remarks', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . "" .
									")";
							$result = mysql_query($qry);
		        	
		        			if (! $result) {
								logError($qry . " - " . mysql_error());
		        			}
		        			
		        			$caseid = mysql_insert_id();
						}
								
								
						$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}casetypist " .
								"(" .
								"caseid, typistid, datefromoffice, datebacktooffice, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid" .
								") " .
								"VALUES " .
								"(" .
								"$caseid, $typistid, '$transcriptrequestdate', '$datebackfromtypist', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . "" .
								")";
								
						$result = mysql_query($qry);
	        	
	        			if (! $result) {
							if (mysql_errno() != 1062) {
								logError($qry . " - " . mysql_error());
							}
	        			}
	        			
						$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}invoices " .
								"(" .
								"caseid, invoicenumber, total" .
								") " .
								"VALUES " .
								"(" .
								"$caseid, '$invoicenumber', 0" .
								")";
								
						$result = mysql_query($qry);
	        	
	        			if (! $result) {
							if (mysql_errno() != 1062) {
								logError($qry . " - " . mysql_error());
							}
	        			}
	        			
//				echo "" . $row . " rows loaded so far</br>";
			        }
			    }
			    
			    fclose($handle);
				echo "<h1>" . $row . " downloaded</h1>";
			}
			
		} catch (Exception $e) {
			echo "ERROR:" + $e->getMessage();
			logError($e->getMessage());
		}
	}
	
?>	
<h1>Upload Cases</h1>		
<br>
<form class="contentform" method="post" enctype="multipart/form-data">
	<label>Upload Case File (CSV)</label>
	<input type="file" name="casecsvfile" id="casecsvfile" style='width:600px; '/> 
	<br />
	<br />
	<div id="submit" class="show">
		<a class='link1' href="javascript:$('.contentform').submit()"><em><b>Submit</b></em></a>
	</div>
</form>
<?php
	
	include("system-footer.php"); 
?>