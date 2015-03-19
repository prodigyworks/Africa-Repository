<?php
	require_once("crud.php");
	
	$crud = new Crud();
	$crud->title = "Invoice Item Templates";
	$crud->table = "{$_SESSION['DB_PREFIX']}invoiceitemtemplates";
	$crud->dialogwidth = 500;
	$crud->checkconstraints = array(
			array(
				'table'      => 'invoiceitems',
				'column' 	 => 'templateid'
			)
		);
	$crud->sql = 
			"SELECT A.*, B.name AS provincename " .
			"FROM {$_SESSION['DB_PREFIX']}invoiceitemtemplates A " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}province B " .
			"ON B.id = A.provinceid " .
			"ORDER BY B.name, A.name";
	
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
				'name'       => 'provinceid',
				'type'       => 'DATACOMBO',
				'length' 	 => 30,
				'label' 	 => 'Province',
				'table'		 => 'province',
				'table_id'	 => 'id',
				'alias'		 => 'provincename',
				'table_name' => 'name'
			),
			array(
				'name'       => 'name',
				'length' 	 => 50,
				'label' 	 => 'Name'
			),
			array(
				'name'       => 'type',
				'length' 	 => 20,
				'label' 	 => 'Type',
				'type'       => 'COMBO',
				'options'    => array(
						array(
							'value'		=> "T",
							'text'		=> "Typist Rate"
						),
						array(
							'value'		=> "N",
							'text'		=> "Normal Rate"
						)
					)
			),
			array(
				'name'       => 'clientprice',
				'length' 	 => 22,
				'datatype'	 => 'double',
				'label' 	 => 'Private Client Price'
			),
			array(
				'name'       => 'courtprice',
				'length' 	 => 12,
				'datatype'	 => 'double',
				'label' 	 => 'Court Price'
			),
			array(
				'name'       => 'typistprice',
				'length' 	 => 12,
				'datatype'	 => 'double',
				'label' 	 => 'Typist Price'
			)
		);
		
	$crud->run();
?>
