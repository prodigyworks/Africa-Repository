<?php
	require_once("crud.php");
	
	include("template-invoice-functions.php");
	
	class InvoiceCrud extends Crud {
		
		public function postAddScriptEvent() {
?>				
		$("#total").val("0.00");
<?php			
		}
		
		public function postHeaderEvent() {
			include("template-invoice-screen.php");
		}
		
		public function postScriptEvent() {
			include("template-invoice-script.php");
?>
			
			function printInvoiceReport(id) {
				window.open("invoicereport.php?id=" + id);
			}
			
			function redirectEdit(id) {
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT caseid FROM <?php echo $_SESSION['DB_PREFIX'];?>invoices WHERE id = " + id
						},
						function(data) {
							if (data.length == 1) {
								editInvoice(data[0].caseid);
							}
						}
					);
			}
			
			/* Full name callback. */
			function fullName(node) {
				return (node.firstname + " " + node.lastname);
			}
			
		    function navigateDown(pk) {
		    	redirectEdit(pk);
		    }
		    
		    function penaltyFunction(node) {
				if (node.inv_penalty == "T") {
					return "10%";
				
				} else if (node.inv_penalty == "F") {
					return "15%";
								
				} else if (node.inv_penalty == "Y") {
					return "50%";
				}
				
				return "None";
		    }
			
			function actualTotal(node) {
				var subtotal = parseFloat(node.inv_total);
				var shippinghandling = parseFloat(node.inv_shippinghandling);
				
				if (node.inv_penalty == "T") {
					subtotal = subtotal * 0.9;
				
				} else if (node.inv_penalty == "F") {
					subtotal = subtotal * 0.85;
				
				} else if (node.inv_penalty == "Y") {
					subtotal = subtotal * 0.5;
				}
				
				return new Number(parseFloat(subtotal + shippinghandling)).toFixed(2);
			}
<?php			
		}
	}

	$crud = new InvoiceCrud();
	$crud->title = "Invoices";
	$crud->allowAdd = false;
	$crud->allowView = false;
	$crud->allowEdit = false;
	$crud->onDblClick = "navigateDown";
	$crud->table = "{$_SESSION['DB_PREFIX']}invoices ";
	$crud->dialogwidth = 500;
	
	if (isset($_GET['id'])) {
		$crud->sql = 
				"SELECT A.penalty AS inv_penalty, A.id AS inv_id, A.caseid AS inv_caseid, A.invoicenumber AS inv_invoicenumber, " .
				"A.createddate AS inv_createddate, A.officeid AS inv_officeid, A.contactid AS inv_contactid, " .
				"A.paid AS inv_paid, A.paymentnumber AS inv_paymentnumber, A.paymentdate AS inv_paymentdate, " .
				"A.termsid AS inv_termsid, A.shippinghandling AS inv_shippinghandling, A.total AS inv_total, " .
				"B.casenumber, B.j33number, C.name, D.firstname, D.lastname, " .
				"BV.name AS courtname, E.name AS clientcourtname, F.name AS officename  " .
				"FROM {$_SESSION['DB_PREFIX']}invoices A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
				"ON B.id = A.caseid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}courts BV " .
				"ON BV.id = B.courtid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}caseterms C " .
				"ON C.id = A.termsid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members D " .
				"ON D.member_id = A.contactid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}courts E " .
				"ON E.id = B.clientcourtid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}offices F " .
				"ON F.id = A.officeid " .
				"WHERE A.caseid = " . $_GET['id'] . " " .
				"ORDER BY A.id DESC";
				
	} else {
		$crud->sql = 
			getFilteredData(
					"SELECT A.penalty AS inv_penalty, A.id AS inv_id, A.caseid AS inv_caseid, A.invoicenumber AS inv_invoicenumber, " .
					"A.createddate AS inv_createddate, A.officeid AS inv_officeid, A.contactid AS inv_contactid, " .
					"A.paid AS inv_paid, A.paymentnumber AS inv_paymentnumber, A.paymentdate AS inv_paymentdate, " .
					"A.termsid AS inv_termsid, A.shippinghandling AS inv_shippinghandling, A.total AS inv_total, " .
					"B.casenumber, B.j33number, C.name, D.firstname, D.lastname, " .
					"BV.name AS courtname, E.name AS clientcourtname, F.name AS officename  " .
					"FROM {$_SESSION['DB_PREFIX']}invoices A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
					"ON B.id = A.caseid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}courts BV " .
					"ON BV.id = B.courtid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}caseterms C " .
					"ON C.id = A.termsid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members D " .
					"ON D.member_id = A.contactid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}allcourts E " .
					"ON E.id = B.clientcourtid " .
					"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}offices F " .
					"ON F.id = A.officeid " .
					"ORDER BY A.id DESC"
				);
	}
	
	$crud->columns = array(
			array(
				'name'       => 'inv_id',
				'filtercolumn'       => 'id',
				'length' 	 => 6,
				'pk'		 => true,
				'showInView' => false,
				'editable'	 => false,
				'filter'	 => false,
				'bind' 	 	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'inv_caseid',
				'filtercolumn'       => 'caseid',
				'length' 	 => 6,
				'editable'	 => false,
				'filter'	 => false,
				'showInView' => false,
				'default' 	 => isset($_GET['id']) ? $_GET['id'] : 0,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'courtname',
				'editable'	 => false,
				'bind'		 => false,
				'length' 	 => 35,
				'filter'	 => false,
				'label' 	 => 'Court / Client'
			),
			array(
				'name'       => 'j33number',
				'editable'	 => false,
				'bind'		 => false,
				'filterprefix' => 'B',
				'length' 	 => 18,
				'label' 	 => 'J33 Number'
			),
			array(
				'name'       => 'clientcourtname',
				'editable'	 => false,
				'filter'	 => false,
				'bind'		 => false,
				'length' 	 => 18,
				'label' 	 => 'Client Court'
			),
			array(
				'name'       => 'casenumber',
				'length' 	 => 18,
				'editable'	 => false,
				'filterprefix' => 'B',
				'bind'		 => false,
				'label' 	 => 'Case Number'
			),
			array(
				'name'       => 'inv_invoicenumber',
				'filtercolumn'       => 'invoicenumber',
				'length' 	 => 18,
				'label' 	 => 'Invoice Number'
			),
			array(
				'name'       => 'inv_createddate',
				'filtercolumn'       => 'createddate',
				'length' 	 => 12,
				'datatype'	 => 'date',
				'label' 	 => 'Invoice Date'
			),
			array(
				'name'       => 'inv_officeid',
				'filtercolumn'       => 'officeid',
				'type'       => 'DATACOMBO',
				'length' 	 => 20,
				'label' 	 => 'Office',
				'table'		 => 'offices',
				'table_id'	 => 'id',
				'alias'		 => 'officename',
				'table_name' => 'name'
			),
			array(
				'name'       => 'inv_contactid',
				'filtercolumn'       => 'contactid',
				'datatype'	 => 'user',
				'length' 	 => 30,
				'label' 	 => 'Contact',
				'showInView' => false
			),
			array(
				'name'       => 'staffname',
				'type'		 => 'DERIVED',
				'length' 	 => 30,
				'bind'		 => false,
				'editable'	 => false,
				'function'   => 'fullName',
				'sortcolumn' => 'A.firstname',
				'label' 	 => 'Contact'
			),
			array(
				'name'       => 'inv_paid',
				'filtercolumn'       => 'paid',
				'length' 	 => 10,
				'label' 	 => 'Paid',
				'type'       => 'COMBO',
				'options'    => array(
						array(
							'value'		=> "N",
							'text'		=> "No"
						),
						array(
							'value'		=> "Y",
							'text'		=> "Yes"
						)
					)
			),
			array(
				'name'       => 'inv_paymentnumber',
				'filtercolumn'       => 'paymentnumber',
				'length' 	 => 30,
				'required'	 => false,
				'label' 	 => 'Payment Number'
			),
			array(
				'name'       => 'inv_paymentdate',
				'filtercolumn'       => 'paymentdate',
				'length' 	 => 12,
				'datatype'	 => 'date',
				'required'	 => false,
				'label' 	 => 'Payment Date'
			),
			array(
				'name'       => 'inv_termsid',
				'filtercolumn'       => 'termsid',
				'type'       => 'DATACOMBO',
				'length' 	 => 50,
				'label' 	 => 'Terms',
				'table'		 => 'caseterms',
				'table_id'	 => 'id',
				'alias'		 => 'name',
				'table_name' => 'name'
			),
			array(
				'name'       => 'inv_shippinghandling',
				'filtercolumn'       => 'shippinghandling',
				'length' 	 => 16,
				'datatype'	 => 'double',
				'align'		 => 'right',
				'label' 	 => 'Shipping / Handling'
			),
			array(
				'name'       => 'inv_total',
				'filtercolumn'       => 'total',
				'length' 	 => 13,
				'datatype'	 => 'double',
				'readonly'	 => true,
				'required'	 => false,
				'align'		 => 'right',
				'label' 	 => 'Sub Total'
			),
			array(
				'name'       => 'inv_penalty',
				'filtercolumn'       => 'penalty',
				'length' 	 => 13,
				'readonly'	 => true,
				'required'	 => false,
				'function'	 => 'penaltyFunction',
				'readonly'	 => true,
				'required'	 => false,
				'type'		 => 'DERIVED',
				'label' 	 => 'Penalty'
			),
			array(
				'name'       => 'inv_acctotal',
				'filter'	 => false,
				'length' 	 => 13,
				'bind'		 => false,
				'datatype'	 => 'double',
				'function'	 => 'actualTotal',
				'readonly'	 => true,
				'required'	 => false,
				'type'		 => 'DERIVED',
				'align'		 => 'right',
				'label' 	 => 'Total'
			)
		);
		
	$crud->subapplications = array(
			array(
				'title'		  => 'View / Edit Invoice',
				'imageurl'	  => 'images/invoice.png',
				'script' 	  => 'redirectEdit'
			),
			array(
				'title'		  => 'Print',
				'imageurl'	  => 'images/print.png',
				'script' 	  => 'printInvoiceReport'
			)
		);
		
	$crud->run();
?>
