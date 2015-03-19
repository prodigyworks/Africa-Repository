<?php
	require_once("crud.php");

	$crud = new Crud();
	$crud->title = "Terms";
	$crud->table = "{$_SESSION['DB_PREFIX']}caseterms";
	$crud->dialogwidth = 400;
	$crud->sql = 
			"SELECT * " .
			"FROM {$_SESSION['DB_PREFIX']}caseterms " .
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
			)
		);
		
	$crud->run();
?>
