<?php
	require_once("crud.php");

	
	class ContactCrud extends Crud {
		
		public function postScriptEvent() {
?>
			/* Full name callback. */
			function goBack() {
				window.history.back();
			}
<?php			
		}
	}
	
	$crud = new ContactCrud();
	$crud->title = "Tracking";
	$crud->table = "{$_SESSION['DB_PREFIX']}cases";
	$crud->allowFilter = false;
	$crud->allowEdit = false;
	$crud->allowAdd = false;
	$crud->allowRemove = false;
	$crud->allowView = false;
	
	$and = "";
			
	if (isset($_POST['j33number']) && $_POST['j33number'] != "") {
		$and = " AND B.j33number LIKE '" . trim(mysql_escape_string($_POST['j33number'])) . "' ";
		
	} else if (isset($_POST['casenumber']) && $_POST['casenumber'] != "") {
		$and = " AND B.casenumber = '" . trim(mysql_escape_string($_POST['casenumber'])) . "' ";
		
	} else if (isset($_POST['quotenumber']) && $_POST['quotenumber'] != "") {
		$and = " AND Z.quotenumber LIKE '" . trim(mysql_escape_string($_POST['quotenumber'])) . "' ";
		
	} else if (isset($_POST['invoicenumber']) && $_POST['invoicenumber'] != "") {
		$and = " AND A.invoicenumber = '" . trim(mysql_escape_string($_POST['invoicenumber'])) . "' ";
	}
	
	$sql = "SELECT A.*, B.transcriptrequestdate, B.plaintiff, " .
			"(SELECT SUM(H.pages) FROM {$_SESSION['DB_PREFIX']}casetypist I INNER JOIN {$_SESSION['DB_PREFIX']}typistinvoices H ON H.casetypistid = I.id WHERE I.caseid = B.id) AS totalpages, " .
			"B.datereceived, B.rate, " .
			"B.datebackfromtypist, J.name AS ratename, " .
			"B.j33number, B.casenumber, B.courtid, Z.quotenumber, " .
			"D.name AS courtname, E.name AS provincename, F.name AS terms, G.firstname, G.lastname " .
			"FROM {$_SESSION['DB_PREFIX']}cases B " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices A  " .
			"ON B.id = A.caseid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
			"ON D.id = B.courtid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
			"ON E.id = D.provinceid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}caseterms F " .
			"ON F.id = A.termsid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members G " .
			"ON G.member_id = A.contactid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates J " .
			"ON J.id = B.rate " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}quotes Z " .
			"ON Z.caseid = B.id " .
			"WHERE 1 = 1 $and " .
			"ORDER BY A.id DESC";

	$crud->sql = getFilteredData($sql);
	
	$crud->columns = array(
			array(
				'name'       => 'id',
				'length' 	 => 6,
				'pk'		 => true,
				'showInView' => false,
				'editable'	 => false,
				'bind' 	 	 => false,
				'filter'	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'provincename',
				'length' 	 => 30,
				'label' 	 => 'Province'
			),
			array(
				'name'       => 'courtname',
				'length' 	 => 30,
				'label' 	 => 'Province'
			),
			array(
				'name'       => 'j33number',
				'length' 	 => 20,
				'label' 	 => 'J33 Number'
			),
			array(
				'name'       => 'casenumber',
				'length' 	 => 20,
				'label' 	 => 'Case Number'
			),
			array(
				'name'       => 'plaintiff',
				'length' 	 => 30,
				'required'	 => false,
				'label' 	 => 'Parties / Accused'
			),
			array(
				'name'       => 'datereceived',
				'length' 	 => 20,
				'datatype'	 => 'date',
				'label' 	 => 'Date Received'
			),
			array(
				'name'       => 'transcriptrequestdate',
				'length' 	 => 12,
				'datatype'	 => 'date',
				'required'	 => false,
				'label' 	 => 'Audio Request Date'
			),
			array(
				'name'       => 'quotenumber',
				'length' 	 => 20,
				'label' 	 => 'Estimate Number'
			),
			array(
				'name'       => 'invoicenumber',
				'length' 	 => 20,
				'label' 	 => 'Invoice Number'
			),
			array(
				'name'       => 'rate',
				'type'       => 'DATACOMBO',
				'length' 	 => 30,
				'label' 	 => 'Rate',
				'required'	 => false,
				'table'		 => 'invoiceitemtemplates',
				'table_id'	 => 'id',
				'alias' 	 => 'ratename',
				'where'		 => " WHERE A.type = 'T' ",
				'table_name' => 'name'
			),
			array(
				'name'       => 'totalpages',
				'length' 	 => 9,
				'required'	 => false,
				'readonly'	 => true,
				'bind'	 	 => false,
				'label' 	 => 'Pages'
			),
			array(
				'name'       => 'datebackfromtypist',
				'length' 	 => 20,
				'datatype'	 => 'date',
				'required'	 => false,
				'label' 	 => 'Date: Back from typist'
			)
		);
		
	$crud->applications = array(
			array(
				'title'		  => 'Search',
				'imageurl'	  => 'images/filter.png',
				'script' 	  => 'goBack'
			)
		);
		
	$crud->run();
?>