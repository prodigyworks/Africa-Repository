<?php
	require_once("crud.php");
	
	class OfficeCrud extends Crud {
		
		public function postScriptEvent() {
?>
<?php			
		}
	}

	$crud = new OfficeCrud();
	$crud->title = "Offices";
	$crud->table = "{$_SESSION['DB_PREFIX']}offices";
	$crud->dialogwidth = 850;
	$crud->sql = 
			"SELECT * " .
			"FROM {$_SESSION['DB_PREFIX']}offices " .
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
				'name'       => 'contact',
				'length' 	 => 50,
				'label' 	 => 'Contact'
			),
			array(
				'name'       => 'telephone',
				'length' 	 => 14,
				'label' 	 => 'Telephone'
			),
			array(
				'name'       => 'cellphone',
				'length' 	 => 14,
				'label' 	 => 'Cell phone'
			),
			array(
				'name'       => 'fax',
				'length' 	 => 14,
				'label' 	 => 'Fax'
			),
			array(
				'name'       => 'email',
				'length' 	 => 50,
				'label' 	 => 'E-mail'
			),
			array(
				'name'       => 'address',
				'length' 	 => 50,
				'type'		 => 'BASICTEXTAREA',
				'showInView' => false,
				'label' 	 => 'Address'
			),
			array(
				'name'       => 'bankingdetails',
				'length' 	 => 50,
				'type'		 => 'BASICTEXTAREA',
				'showInView' => false,
				'label' 	 => 'Banking Details (Court)'
			),
			array(
				'name'       => 'privatebankingdetails',
				'length' 	 => 50,
				'type'		 => 'BASICTEXTAREA',
				'showInView' => false,
				'label' 	 => 'Banking Details (Private Client)'
			)
		);
		
	$crud->run();
?>
