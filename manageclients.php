<?php
	require_once("crud.php");
	
	class ClientLib extends Crud {
		private $clienttype = "";
	
		function __construct() {
			parent::__construct();
			 
			$this->title = "Clients";
			$this->table = "{$_SESSION['DB_PREFIX']}courts";
			$this->onDblClick = "navigateDown";
			$this->dialogwidth = 750;
				
			if (isset($_GET['id'])) {
				$this->sql =
					"SELECT A.*, B.name AS provincename, " .
					"(SELECT CA.fullname FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS fullname, " .
					"(SELECT CA.address FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS address, " .
					"(SELECT CA.firstname FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS firstname, " .
					"(SELECT CA.lastname FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS lastname, " .
					"(SELECT CA.email FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS email, " .
					"(SELECT CA.telephone FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS telephone, " .
					"(SELECT CA.fax FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS fax, " .
					"(SELECT CA.cellphone FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS cellphone " .
					"FROM {$_SESSION['DB_PREFIX']}allcourts A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}province B " .
					"ON B.id = A.provinceid " .
					"WHERE A.provinceid = " . $_GET['id'] . " " .
					"AND A.vatapplicable = 'Y' " .
					"ORDER BY B.name, A.name";
	
			} else {
				$this->sql =
					"SELECT A.*, B.name AS provincename, " .
					"(SELECT CA.fullname FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS fullname, " .
					"(SELECT CA.firstname FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS firstname, " .
					"(SELECT CA.lastname FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS lastname, " .
					"(SELECT CA.address FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS address, " .
					"(SELECT CA.email FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS email, " .
					"(SELECT CA.telephone FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS telephone, " .
					"(SELECT CA.fax FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS fax, " .
					"(SELECT CA.cellphone FROM {$_SESSION['DB_PREFIX']}contacts CA WHERE CA.courtid = A.id ORDER BY CA.id LIMIT 1) AS cellphone " .
					"FROM {$_SESSION['DB_PREFIX']}allcourts A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}province B " .
					"ON B.id = A.provinceid " .
					"WHERE A.vatapplicable = 'Y' " .
					"ORDER BY B.name, A.name";
			}
				
			$this->columns = array(
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
					'showInView' => false,
					'editable' 	 => false,
					'filter'	 => false,
					'default'	 => getSiteConfigData()->privateclientprovinceid
				),
				array(
					'name'       => 'filter_name',
					'type'       => 'DATACOMBO',
					'length' 	 => 30,
					'label' 	 => 'Client',
					'where'		 => " WHERE A.vatapplicable = 'Y' ",
					'table'		 => 'courts',
					'table_id'	 => 'id',
					'alias'		 => 'name',
					'table_name' => 'name',
					'filtercolumn' => 'id',
					'bind'	 	 => false,
					'showInView' => false,
					'editable'	 => false
				),
				array(
					'name'       => 'name',
					'length' 	 => 50,
					'filter'	 => false,
					'label' 	 => 'Client'
				),
				array(
					'name'       => 'accountnumber',
					'length' 	 => 40,
					'label' 	 => 'Account Number'
				),
				array(
					'name'       => 'firstname',
					'bind'		 => false,
					'showInView' => false,
					'length' 	 => 40,
					'label' 	 => 'First Name'
				),
				array(
					'name'       => 'lastname',
					'bind'		 => false,
					'showInView' => false,
					'length' 	 => 40,
					'label' 	 => 'Last Name'
				),
				array(
					'name'       => 'fullname',
					'bind'		 => false,
					'editable'	 => false,
					'length' 	 => 40,
					'label' 	 => 'Primary Contact'
				),
				array(
					'name'       => 'fulladdress',
					'type'		 => 'DERIVED',
					'length' 	 => 60,
					'editable'   => false,
					'bind'		 => false,
					'function'   => 'address',
					'sortcolumn' => 'A.firstname',
					'label' 	 => 'Address'
				),
				array(
					'name'       => 'address',
					'type'		 => 'BASICTEXTAREA',
					'length' 	 => 60,
					'bind'		 => false,
					'showInView' => false,
					'label' 	 => 'Address'
				),
				array(
					'name'       => 'email',
					'bind'		 => false,
					'length' 	 => 40,
					'label' 	 => 'E-Mail'
				),
				array(
					'name'       => 'telephone',
					'bind'		 => false,
					'length' 	 => 15,
					'label' 	 => 'Telephone'
				),
				array(
					'name'       => 'cellphone',
					'bind'		 => false,
					'length' 	 => 15,
					'label' 	 => 'Cell Phone'
				),
				array(
					'name'       => 'fax',
					'bind'		 => false,
					'length' 	 => 15,
					'label' 	 => 'Fax'
				),
				array(
					'name'       => 'vatapplicable',
					'length' 	 => 20,
					'label' 	 => 'VAT Applicable',
					'type'       => 'COMBO',
					'showInView' => false,
					'editable'   => false,
					'filter'     => false,
					'default'	 => 'Y',
					'options'    => array(
							array(
								'value'		=> "Y",
								'text'		=> "Yes"
							),
							array(
								'value'		=> "N",
								'text'		=> "No"
							)
						)
				)
			);
	
			$this->subapplications = array(
					array(
							'title'		  => 'Cases',
							'imageurl'	  => 'images/court.png',
							'application' => 'managecases.php'
					),
					array(
							'title'		  => 'Contacts',
							'imageurl'	  => 'images/user.png',
							'application' => 'managecontacts.php'
					)
			);
		}
		
		public function postInsertEvent() {
			$courtid = mysql_insert_id();
			$firstname = mysql_escape_string($_POST['firstname']);
			$lastname = mysql_escape_string($_POST['lastname']);
			$telephone = mysql_escape_string($_POST['telephone']);
			$cellphone = mysql_escape_string($_POST['cellphone']);
			$fax = mysql_escape_string($_POST['fax']);
			$email = mysql_escape_string($_POST['email']);
			$address = mysql_escape_string($_POST['address']);
				
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}contacts (courtid, firstname, lastname, email, telephone, cellphone, fax, address) VALUE (" . 
				   "$courtid, '$firstname', '$lastname', '$email', '$telephone', '$cellphone', '$fax', '$address')";
			$result = mysql_query($qry);
	
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
		}
		
		public function postUpdateEvent($courtid) {
			$firstname = mysql_escape_string($_POST['firstname']);
			$lastname = mysql_escape_string($_POST['lastname']);
			$telephone = mysql_escape_string($_POST['telephone']);
			$cellphone = mysql_escape_string($_POST['cellphone']);
			$fax = mysql_escape_string($_POST['fax']);
			$address = mysql_escape_string($_POST['address']);
			$email = mysql_escape_string($_POST['email']);
				
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}contacts SET " .
				   "firstname = '$firstname', " .
				   "lastname = '$lastname', " . 
				   "email = '$email', " . 
				   "telephone = '$telephone', " .
				   "cellphone = '$cellphone', " . 
				   "fax = '$fax', " .
				   "address = '$address' " . 
				   "WHERE courtid = $courtid";
			$result = mysql_query($qry);
	
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
		}
		
		public function postScriptEvent() {
	?>
			function address(node) {
				if (node.address == null) return "";
				
				return node.address.replace(/[\r]/g, '').replace(/[\n]/g, ', ');
			}
			
		    function navigateDown(pk) {
		    	subApp('managecases.php', pk);
		    }
	<?php
		}
	}
	
	$crud = new ClientLib();
	$crud->run();
?>
