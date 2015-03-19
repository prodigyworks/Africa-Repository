<?php
	require_once("crud.php");
	
	class ContactCrud extends Crud {
		
		public function postScriptEvent() {
?>
<?php			
		}
	}

	$crud = new ContactCrud();
	$crud->title = "Contacts";
	$crud->table = "{$_SESSION['DB_PREFIX']}contacts";
	$crud->dialogwidth = 750;
	$crud->sql = 
			"SELECT * " .
			"FROM {$_SESSION['DB_PREFIX']}contacts " .
			"WHERE courtid = " . $_GET['id'] . " " .
			"ORDER BY firstname, lastname";
	
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
				'name'       => 'courtid',
				'length' 	 => 6,
				'default'	 => $_GET['id'],
				'showInView' => false,
				'editable'	 => false,
				'filter'	 => false,
				'label' 	 => 'Court ID'
			),
			array(
				'name'       => 'title',
				'length' 	 => 10,
				'label' 	 => 'Title',
				'type'       => 'COMBO',
				'options'    => array(
						array(
							'value'		=> "Mr",
							'text'		=> "Mr"
						),
						array(
							'value'		=> "Mrs",
							'text'		=> "Mrs"
						),
						array(
							'value'		=> "Miss",
							'text'		=> "Miss"
						),
						array(
							'value'		=> "Ms",
							'text'		=> "Ms"
						),
						array(
							'value'		=> "Master",
							'text'		=> "Master"
						)
					)
			),
			array(
				'name'       => 'fullname',
				'length' 	 => 30,
				'showInView' => true,
				'editable'	 => false,
				'bind'		 => false,
				'label' 	 => 'Name'
			),
			array(
				'length' 	 => 60,
				'name'       => 'firstname',
				'showInView' => false,
				'label' 	 => 'First Name'
			),
			array(
				'name'       => 'lastname',
				'showInView' => false,
				'length' 	 => 60,
				'label' 	 => 'Last Name'
			),
			array(
				'name'       => 'telephone',
				'length' 	 => 20,
				'label' 	 => 'Telephone'
			),
			array(
				'name'       => 'cellphone',
				'length' 	 => 20,
				'label' 	 => 'Cell phone'
			),
			array(
				'name'       => 'fax',
				'length' 	 => 20,
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
			)
		);
		
	$crud->run();
?>
