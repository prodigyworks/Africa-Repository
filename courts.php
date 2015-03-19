<?php
	require_once("clientlib.php");

	$crud = new ClientLib("N");
	
	if (! isUserInRole("ADMIN")) {
		$crud->allowAdd = false;
		$crud->allowRemove = false;
	}

	$crud->run();
?>
