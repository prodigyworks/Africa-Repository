<?php
	require_once("crud.php");
	
	include("template-invoice-functions.php");
	
	class QuoteCrud extends Crud {
		
		public function postAddScriptEvent() {
?>				
		$("#total").val("0.00");
<?php			
		}
		
		public function postHeaderEvent() {
			include("template-invoice-screen.php");
			include("template-quote-screen.php");
		}
		
		public function postScriptEvent() {
			include("template-invoice-script.php");
			include("template-quote-script.php");
?>
			
			function printQuoteReport(id) {
				window.open("quotereport.php?id=" + id);
			}
			
			function redirectEdit(id) {
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT caseid FROM <?php echo $_SESSION['DB_PREFIX'];?>quotes WHERE id = " + id
						},
						function(data) {
							if (data.length == 1) {
								editQuote(data[0].caseid);
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
			
			function actualTotal(node) {
				var subtotal = parseFloat(node.total);
				var shippinghandling = parseFloat(node.shippinghandling);
				
				return new Number(parseFloat(subtotal + shippinghandling)).toFixed(2);
			}
<?php			
		}
	}

	$crud = new QuoteCrud();
	$crud->title = "Quotes";
	$crud->allowAdd = false;
	$crud->allowView = false;
	$crud->allowEdit = false;
	$crud->onDblClick = "navigateDown";
	$crud->table = "{$_SESSION['DB_PREFIX']}quotes";
	$crud->dialogwidth = 500;
	
	if (isset($_GET['id'])) {
		$crud->sql = 
				"SELECT A.*, B.casenumber, B.j33number, D.firstname, D.lastname, " .
				"BV.name AS courtname, E.name AS clientcourtname, F.name AS officename  " .
				"FROM {$_SESSION['DB_PREFIX']}quotes A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
				"ON B.id = A.caseid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}courts BV " .
				"ON BV.id = B.courtid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}members D " .
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
					"SELECT A.*, B.casenumber, B.j33number, D.firstname, D.lastname, " .
					"BV.name AS courtname, E.name AS clientcourtname, F.name AS officename  " .
					"FROM {$_SESSION['DB_PREFIX']}quotes A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
					"ON B.id = A.caseid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}courts BV " .
					"ON BV.id = B.courtid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}members D " .
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
				'name'       => 'id',
				'length' 	 => 6,
				'pk'		 => true,
				'showInView' => false,
				'editable'	 => false,
				'filter'	 => false,
				'bind' 	 	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'caseid',
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
				'label' 	 => 'Court / Client'
			),
			array(
				'name'       => 'j33number',
				'editable'	 => false,
				'bind'		 => false,
				'length' 	 => 18,
				'label' 	 => 'J33 Number'
			),
			array(
				'name'       => 'clientcourtname',
				'editable'	 => false,
				'bind'		 => false,
				'length' 	 => 18,
				'label' 	 => 'Client Court'
			),
			array(
				'name'       => 'casenumber',
				'length' 	 => 18,
				'editable'	 => false,
				'bind'		 => false,
				'label' 	 => 'Case Number'
			),
			array(
				'name'       => 'quotenumber',
				'length' 	 => 18,
				'label' 	 => 'Quote Number'
			),
			array(
				'name'       => 'createddate',
				'length' 	 => 12,
				'datatype'	 => 'date',
				'label' 	 => 'Quote Date'
			),
			array(
				'name'       => 'contactid',
				'datatype'	 => 'user',
				'length' 	 => 30,
				'label' 	 => 'Contact',
				'showInView' => false
			),
			array(
				'name'       => 'ourref',
				'length' 	 => 50,
				'required'	 => false,
				'label' 	 => 'Our Ref'
			),
			array(
				'name'       => 'yourref',
				'length' 	 => 50,
				'required'	 => false,
				'label' 	 => 'Your Ref'
			),
			array(
				'name'       => 'officeid',
				'type'       => 'DATACOMBO',
				'length' 	 => 20,
				'label' 	 => 'Office',
				'table'		 => 'offices',
				'table_id'	 => 'id',
				'alias'		 => 'officename',
				'table_name' => 'name'
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
				'name'       => 'paid',
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
				'name'       => 'paymentnumber',
				'length' 	 => 30,
				'required'	 => false,
				'label' 	 => 'Payment Number'
			),
			array(
				'name'       => 'paymentdate',
				'length' 	 => 12,
				'datatype'	 => 'date',
				'required'	 => false,
				'label' 	 => 'Payment Date'
			),
			array(
				'name'       => 'shippinghandling',
				'length' 	 => 16,
				'onchange'	 => 'shippinghandling_onchange',
				'datatype'	 => 'double',
				'align'		 => 'right',
				
				'label' 	 => 'Shipping / Handling'
			),
			array(
				'name'       => 'total',
				'length' 	 => 13,
				'datatype'	 => 'double',
				'readonly'	 => true,
				'required'	 => false,
				'align'		 => 'right',
				'label' 	 => 'Sub Total'
			),
			array(
				'name'       => 'acctotal',
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
				'title'		  => 'View / Edit Quote',
				'imageurl'	  => 'images/accept.png',
				'script' 	  => 'redirectEdit'
			),
			array(
				'title'		  => 'Print',
				'imageurl'	  => 'images/print.png',
				'script' 	  => 'printQuoteReport'
			)
		);
		
	$crud->run();
?>
