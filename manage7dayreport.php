<?php
	require_once("managecaseslib.php");

	class Case7DayCrud extends CaseCrud {
		
	    public function __construct() {
	        parent::__construct();
	        
	        $date = date("m-d-Y");
			$date1 = str_replace('-', '/', $date);
			$range = date('Y-m-d',strtotime($date1 . "-7 days"));
	        
			parent::setSQL( 
					getFilteredData(
							"SELECT A.*, D.name AS courtname, E.name AS provincename, F.paymentnumber, F.invoicenumber, G.name AS clientcourtname " .
							"FROM {$_SESSION['DB_PREFIX']}cases A " .
							"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
							"ON D.id = A.courtid " .
							"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
							"ON E.id = D.provinceid " .
							"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices F " .
							"ON F.caseid = A.id " .
							"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}allcourts G " .
							"ON G.id = A.clientcourtid " .
							"WHERE A.datereceived < '$range' " .
							"AND (A.datetransmitted IS NULL OR A.datetransmitted = 0) " .
							"ORDER E.name, D.name, A.id DESC"
						)
				);
		}
	}

	$crud = new Case7DayCrud();
	$crud->run();
?>
