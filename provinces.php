<?php
	require_once("crud.php");

	class ProvinceCrud extends Crud {
		public function postScriptEvent() {
?>
		    function navigateDown(pk) {
		    	subApp('courts.php', pk);
		    }
<?php
		}
	}
	
	$crud = new ProvinceCrud();
	$crud->title = "Provinces";
	$crud->table = "{$_SESSION['DB_PREFIX']}province";
	$crud->dialogwidth = 450;
	$crud->allowAdd = false;
	$crud->onDblClick = "navigateDown";
	$crud->sql = 
			"SELECT * " .
			"FROM {$_SESSION['DB_PREFIX']}province " .
			"WHERE id != " . getSiteConfigData()->privateclientprovinceid . " " .
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
				'label' 	 => 'Province'
			)
		);
		
	$crud->subapplications = array(
			array(
				'title'		  => 'Courts',
				'imageurl'	  => 'images/court.png',
				'application' => 'courts.php'
			)
		);
		
		
	$crud->run();
?>
