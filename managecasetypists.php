<?php
	require_once("crud.php");

	
	class CaseTypistCrud extends Crud {
		
	    public function __construct() {
	        parent::__construct();
	        
			$this->title = "Case Typists";
			$this->dialogWidth = 500;
			$this->table = "{$_SESSION['DB_PREFIX']}casetypist";
			
			if (isset($_GET['id'])) {
				$this->sql = 
						"SELECT B.*, A.j33number, A.casenumber, A.clientcourtid, D.name AS courtname, E.name AS provincename, " .
						"G.name AS clientcourtname, H.firstname AS typistfirstname, H.lastname AS typistlastname " .
						"FROM {$_SESSION['DB_PREFIX']}casetypist B " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}cases A " .
						"ON A.id = B.caseid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
						"ON D.id = A.courtid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
						"ON E.id = D.provinceid " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}courts G " .
						"ON G.id = A.clientcourtid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}members H " .
						"ON H.member_id = B.typistid " .
						"WHERE B.caseid = " . $_GET['id'] . " " .
						"ORDER BY E.name, D.name, H.firstname";
			}
			
			$this->columns = array(
					array(
						'name'       => 'provincename',
						'length' 	 => 30,
						'bind'		 => false,
						'editable'	 => false,
						'label' 	 => 'Province'
					),
					array(
						'name'       => 'courtid',
						'type'       => 'DATACOMBO',
						'length' 	 => 30,
						'label' 	 => 'Court / Client',
						'table'		 => 'courts',
						'table_id'	 => 'id',
						'onchange'	 => 'courtid_onchange',
						'alias'		 => 'courtname',
						'table_name' => 'name',
						'editable'	 => false,
						'bind'	 	 => false,
					),
					array(
						'name'       => 'caseid',
						'editable'	 => false,
						'showInView' => false,
						'default'	 => isset($_GET['id']) ? $_GET['id'] : 0
					),
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
						'name'       => 'j33number',
						'length' 	 => 20,
						'onchange'	 => 'j33number_onchange',
						'required'	 => false,
						'editable'	 => false,
						'bind'	 	 => false,
						'label' 	 => 'J33 Number'
					),
					array(
						'name'       => 'casenumber',
						'length' 	 => 20,
						'required'	 => false,
						'bind'	 	 => false,
						'editable'	 => false,
						'onchange'	 => 'casenumber_onchange',
						'label' 	 => 'Case Number'
					),
					array(
						'name'       => 'typistid',
						'datatype'	 => 'typist',
						'showInView' => false,
						'label' 	 => 'Typist'
					),
					array(
						'name'       => 'typistname',
						'type'		 => 'DERIVED',
						'length' 	 => 30,
						'bind'		 => false,
						'editable'	 => false,
						'function'   => 'typistname',
						'sortcolumn' => 'H.firstname',
						'label' 	 => 'Name'
					),
					array(
						'name'       => 'datefromoffice',
						'length' 	 => 12,
						'datatype'	 => 'date',
						'editable'	 => true,
						'label' 	 => 'Date From Office'
					),
					array(
						'name'       => 'datebacktooffice',
						'length' 	 => 12,
						'editable'	 => false,
						'datatype'	 => 'date',
						'label' 	 => 'Date Returned To Office'
					)
				);
			$this->subapplications = array(
					array(
						'title'		  => 'Invoice',
						'imageurl'	  => 'images/invoice.png',
						'script' 	  => 'editInvoice'
					),
					array(
						'title'		  => 'Transcripts',
						'imageurl'	  => 'images/article.png',
						'script' 	  => 'editDocuments'
					)
				);
		}

		/* Post header event. */
		public function postHeaderEvent() {
			createDocumentLink();
		}
		
		public function postInsertEvent() {
			$casetypistid = mysql_insert_id();
			
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}casetypist SET " .
					"datefromoffice = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE id = $casetypistid";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
		}
		
		public function postScriptEvent() {
?>
			function editDocuments(node) {
				viewDocument(node, "addcasetypistdocument.php", node, "casetypistdocs", "casetypistid");
			}
			
			function typistname(node) {
				if (node.typistlastname == null) {
					return "";
				}
			
				return node.typistfirstname + " " + node.typistlastname;
			}
<?php
		}
	}
	
	$crud = new CaseTypistCrud();
	$crud->run();
?>
