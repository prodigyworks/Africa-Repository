<?php
	require_once("crud.php");
	
	class InvoiceItemCrud extends Crud {
		
		function postInsertEvent() {
			$invoiceid = $_GET['id'];
			
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}invoices SET total = (SELECT SUM(total) FROM {$_SESSION['DB_PREFIX']}invoiceitems WHERE invoiceid = $invoiceid), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE id = $invoiceid";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
		}
		
		function postAddScriptEvent() {
?>				
			$("#qty").val("0.00");
			$("#unitprice").val("0.00");
			$("#vat").val("0.00");
			$("#total").val("0.00");
			$("#vatrate").val("<?php echo number_format(getSiteConfigData()->vatrate, 2); ?>");
<?php
		}
			
		public function postScriptEvent() {
?>
				
			function qty_onchange(node) {
				var qty = parseFloat($("#qty").val());
				var unitprice = parseFloat($("#unitprice").val());
				var vatrate = parseFloat($("#vatrate").val());
				
				if (isNaN(unitprice)) {
					unitprice = 0;
				}
				
				if (isNaN(vatrate)) {
					vatrate = 0;
				}
				
				if (isNaN(qty)) {
					qty = 0;
				}
				
				var total = parseFloat(qty * unitprice);
				var vat = total * (vatrate / 100);
				
				total += vat;
				
				$("#vatrate").val(new Number(vatrate).toFixed(2));
				$("#unitprice").val(new Number(unitprice).toFixed(2));
				$("#qty").val(new Number(qty).toFixed(2));
				$("#vat").val(new Number(vat).toFixed(2));
				$("#total").val(new Number(total).toFixed(2));
			}
<?php		
		}
	}

	$crud = new InvoiceItemCrud();
	$crud->title = "Invoice Items";
	$crud->postAddScriptEvent = "postAddScriptEvent";
	$crud->postInsertEvent = "postInsertEvent";
	$crud->table = "{$_SESSION['DB_PREFIX']}invoiceitems";
	$crud->dialogwidth = 880;
	$crud->sql = 
			"SELECT A.*, B.invoicenumber, C.casenumber, C.j33number  " .
			"FROM {$_SESSION['DB_PREFIX']}invoiceitems A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}invoices B " .
			"ON B.id = A.invoiceid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}cases C " .
			"ON C.id = B.caseid " .
			"WHERE A.invoiceid = " . $_GET['id'] . " " .
			"ORDER BY A.id";
	
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
				'name'       => 'invoiceid',
				'length' 	 => 6,
				'showInView' => false,
				'editable'	 => false,
				'filter'	 => false,
				'default' 	 => $_GET['id'],
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'j33number',
				'editable'	 => false,
				'bind'		 => false,
				'length' 	 => 18,
				'label' 	 => 'J33 Number'
			),
			array(
				'name'       => 'casenumber',
				'length' 	 => 18,
				'editable'	 => false,
				'bind'		 => false,
				'label' 	 => 'Case Number'
			),
			array(
				'name'       => 'invoicenumber',
				'length' 	 => 18,
				'editable'	 => false,
				'bind'		 => false,
				'label' 	 => 'Invoice Number'
			),
			array(
				'name'       => 'description',
				'length' 	 => 40,
				'label' 	 => 'Description'
			),
			array(
				'name'       => 'notes',
				'showInView' => false,
				'type'		 => 'TEXTAREA',
				'label' 	 => 'Notes'
			),
			array(
				'name'       => 'qty',
				'length' 	 => 10,
				'onchange'	 => 'qty_onchange',
				'datatype'	 => 'double',
				'align'		 => 'right',
				'label' 	 => 'Quantity'
			),
			array(
				'name'       => 'unitprice',
				'length' 	 => 12,
				'onchange'	 => 'qty_onchange',
				'datatype'	 => 'double',
				'align'		 => 'right',
				'label' 	 => 'Unit Price'
			),
			array(
				'name'       => 'vatrate',
				'length' 	 => 12,
				'onchange'	 => 'qty_onchange',
				'datatype'	 => 'double',
				'align'		 => 'right',
				'label' 	 => 'VAT Rate'
			),
			array(
				'name'       => 'vat',
				'length' 	 => 10,
				'readonly'	 => true,
				'required'	 => false,
				'datatype'	 => 'double',
				'align'		 => 'right',
				'label' 	 => 'VAT'
			),
			array(
				'name'       => 'total',
				'length' 	 => 13,
				'datatype'	 => 'double',
				'readonly'	 => true,
				'required'	 => false,
				'align'		 => 'right',
				'label' 	 => 'Total'
			)
		);
		
	$crud->run();
?>
