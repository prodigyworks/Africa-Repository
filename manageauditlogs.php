<?php
	require_once("crud.php");
	
	function clearAllErrors() {
		mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}errors");
	}
	
	class AuditCrud extends Crud {
		
		public function postScriptEvent() {
?>
			/* Full name callback. */
			function fullName(node) {
				if (node.firstname == null) {
					return "System Management Process";
				}
				
				return (node.firstname + " " + node.lastname);
			}
			
			function tableName(node) {
				if (node.tablename == "Q") {
					return "Quotations";
				}
				
				if (node.tablename == "I") {
					return "Invoices";
				}
				
				if (node.tablename == "C") {
					return "Cases";
				}
				
				return "";
			}
			
			function typeName(node) {
				if (node.type == "I") {
					return "Insert";
				}
				
				if (node.type == "U") {
					return "Update";
				}
				
				if (node.tablename == "D") {
					return "Delete";
				}
				
				return "";
			}
<?php			
		}
	}

	$crud = new AuditCrud();
	$crud->allowAdd = false;
	$crud->allowEdit = false;
	$crud->title = "Audit Logs";
	$crud->table = "{$_SESSION['DB_PREFIX']}caseauditlogs";
//	$crud->dialogwidth = 900;
	$crud->sql = 
			"SELECT A.*, B.casenumber, B.j33number, C.fullname, C.firstname, C.lastname " .
			"FROM {$_SESSION['DB_PREFIX']}caseauditlogs A " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}cases B " .
			"ON B.id = A.pkvalue " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members C " .
			"ON C.member_id = A.auditmemberid " .
			"ORDER BY A.id DESC";
	
	$crud->columns = array(
			array(
				'name'       => 'id',
				'length' 	 => 6,
				'pk'		 => true,
				'showInView' => false,
				'filter'	 => false,
				'editable'	 => false,
				'bind' 	 	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'auditdate',
				'datatype'	 => 'datetime',
				'length' 	 => 18,
				'label' 	 => 'Audit Date'
			),
			array(
				'name'       => 'auditmemberid',
				'type'       => 'DATACOMBO',
				'length' 	 => 30,
				'label' 	 => 'User',
				'table'		 => 'members',
				'table_id'	 => 'member_id',
				'alias'		 => 'fullname',
				'table_name' => 'fullname'
			),
			array(
				'name'       => 'user',
				'type'		 => 'DERIVED',
				'length' 	 => 30,
				'sortcolumn' => 'C.firstname',
				'function'	 => 'fullName',
				'label' 	 => 'User'
			),
			array(
				'name'       => 'tablename',
				'type'		 => 'DERIVED',
				'sortcolumn' => 'A.tablename',
				'length' 	 => 20,
				'function'	 => 'tableName',
				'label' 	 => 'Table'
			),
			array(
				'name'       => 'type',
				'type'		 => 'DERIVED',
				'length' 	 => 10,
				'sortcolumn' => 'A.type',
				'function'	 => 'typeName',
				'label' 	 => 'Type'
			),
			array(
				'name'       => 'casenumber',
				'length' 	 => 20,
				'filterprefix' => "B",
				'label' 	 => 'Case Number'
			),
			array(
				'name'       => 'j33number',
				'length' 	 => 20,
				'filterprefix' => "B",
				'label' 	 => 'J33 Number'
			)
		);
		
	$crud->run();
?>
