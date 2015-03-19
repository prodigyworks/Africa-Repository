<?php
	require_once("crud.php");
	
	$crud = new Crud();
	$crud->title = "Invoice Item Templates";
	$crud->table = "{$_SESSION['DB_PREFIX']}typistinvoiceitemtemplates";
	$crud->dialogwidth = 500;
	$crud->checkconstraints = array(
			array(
				'table'      => 'invoiceitems',
				'column' 	 => 'templateid'
			)
		);
	$crud->sql = 
			"SELECT * " .
			"FROM {$_SESSION['DB_PREFIX']}typistinvoiceitemtemplates " .
			"ORDER BY name";
	
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
				'name'       => 'name',
				'length' 	 => 50,
				'label' 	 => 'Name'
			),
			array(
				'name'       => 'displayname',
				'length' 	 => 50,
				'bind'		 => false,
				'editable'	 => false,
				'label' 	 => 'Display Name'
			),
			array(
				'name'       => 'rate',
				'length' 	 => 22,
				'datatype'	 => 'double',
				'label' 	 => 'Typist Rate'
			),
			array(
				'name'       => 'clientrate',
				'length' 	 => 22,
				'datatype'	 => 'double',
				'label' 	 => 'Private Client Rate'
			),
			array(
				'name'       => 'courtrate',
				'length' 	 => 12,
				'datatype'	 => 'double',
				'label' 	 => 'Court Rate'
			)
		);
		
	$crud->run();
?>
