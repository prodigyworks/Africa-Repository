<?php
	require_once("managecaseslib.php");

	$crud = new CaseCrud();
	$crud->sql = 
		getFilteredData(
				"SELECT A.*, O.name AS officename, Y.invoicenumber, Z.quotenumber, D.name AS courtname, " .
				"E.name AS provincename, F.paymentnumber, F.invoicenumber, G.name AS clientcourtname, J.displayname AS ratename, " .
				"(SELECT SUM(H.pages) FROM {$_SESSION['DB_PREFIX']}casetypist I INNER JOIN {$_SESSION['DB_PREFIX']}typistinvoices H ON H.casetypistid = I.id WHERE I.caseid = A.id) AS totalpages " .
				"FROM {$_SESSION['DB_PREFIX']}cases A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
				"ON D.id = A.courtid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
				"ON E.id = D.provinceid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices F " .
				"ON F.caseid = A.id " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}allcourts G " .
				"ON G.id = A.clientcourtid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates J " .
				"ON J.id = A.rate " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices Y " .
				"ON Y.caseid = A.id " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}quotes Z " .
				"ON Z.caseid = A.id " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}offices O " .
				"ON A.officeid = O.id " .
				"WHERE A.readytoinvoice = 'Y' " .
				"AND (F.paid IS NULL or F.paid = 'N') ".
				"ORDER E.name, D.name, A.id DESC"
			);
	$crud->run();
?>
