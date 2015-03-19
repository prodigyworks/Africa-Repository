<?php
	require_once("crud.php");

	class ClientLib extends Crud {
		private $clienttype = "";
		
		function __construct($ctype) {
	        parent::__construct();
	        
	        global $clienttype;
	        
	        $clienttype = $ctype;
	        
			$this->title = "Courts";
			$this->table = "{$_SESSION['DB_PREFIX']}courts";
			$this->onDblClick = "navigateDown";
			$this->dialogwidth = 750;
			
			if (isset($_GET['id'])) {
				$this->sql = 
						"SELECT A.*, B.name AS provincename " .
						"FROM {$_SESSION['DB_PREFIX']}courts A " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}province B " .
						"ON B.id = A.provinceid " .
						"WHERE A.provinceid = " . $_GET['id'] . " " .
						"AND A.vatapplicable = '$clienttype' " .
						"ORDER BY B.name, A.name";
				
			} else {
				$this->sql = 
					getFilteredData(
							"SELECT A.*, B.name AS provincename " .
							"FROM {$_SESSION['DB_PREFIX']}courts A " .
							"INNER JOIN {$_SESSION['DB_PREFIX']}province B " .
							"ON B.id = A.provinceid " .
							"WHERE A.vatapplicable = '$clienttype' " .
							"ORDER BY B.name, A.name"
						);
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
						'type'       => 'DATACOMBO',
						'length' 	 => 30,
						'label' 	 => 'Province',
						'table'		 => 'province',
						'table_id'	 => 'id',
						'alias'		 => 'provincename',
						'table_name' => 'name',
						'editable'   => ($clienttype == "N") ? (! isset($_GET['id'])) : true,
						'showInView' => ($clienttype == "N") ? (! isset($_GET['id'])) : true,
						'default'	 => isset($_GET['id']) ? $_GET['id'] : 0
					),
					array(
						'name'       => 'name',
						'length' 	 => 50,
						'label' 	 => 'Court'
					),
					array(
						'name'       => 'accountnumber',
						'length' 	 => 40,
						'label' 	 => 'Account Number'
					),
					array(
						'name'       => 'vatapplicable',
						'length' 	 => 20,
						'label' 	 => 'VAT Applicable',
						'showInView' => false,
						'editable'   => false,
						'default'	 => $clienttype
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
		
		public function postScriptEvent() {
?>
		    function navigateDown(pk) {
		    	subApp('managecases.php', pk);
		    }
<?php
		}
	}
?>
