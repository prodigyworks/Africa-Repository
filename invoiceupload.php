<?php
	require_once("crud.php");
	
	include("template-invoice-functions.php");
	
	class CaseTypistCrud extends Crud {
		
		public function postEditScriptEvent() {
?>
			var readonly = ($("#pages").val() != "" && $("#pages").val() != "0");
			
			$("#pages").attr("readonly", readonly);
			$("#notificationid").attr("readonly", readonly);
<?php
		}
		
	    public function __construct() {
	        parent::__construct();
	        
			$this->title = "Cases";
			$this->table = "{$_SESSION['DB_PREFIX']}casetypist";
			$this->dialogwidth = 750;
			$this->allowEdit = false;
			$this->allowAdd = false;
			$this->allowRemove = false;
			$this->allowFilter = false;
			$this->allowView = false;
			$this->onClickCallback = "onClick";
			
			$this->sql = 
				getFilteredData(
						"SELECT B.*, A.instructions, A.rate AS caserate, A.plaintiff, A.j33number, A.time, A.casenumber, A.clientcourtid, D.name AS courtname, E.name AS provincename, " .
						"G.name AS clientcourtname, H.firstname AS typistfirstname, H.lastname AS typistlastname, " .
						"I.id AS invoiceid, I.pages, J.displayname AS ratename " .
						"FROM {$_SESSION['DB_PREFIX']}casetypistwithsession B " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}cases A " .
						"ON A.id = B.caseid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
						"ON D.id = A.courtid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
						"ON E.id = D.provinceid " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}allcourts G " .
						"ON G.id = A.clientcourtid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}members H " .
						"ON H.member_id = B.typistid " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}typistinvoices I " .
						"ON I.casetypistid = B.id " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates J " .
						"ON J.id = A.rate " .
						"WHERE B.typistid = " . getLoggedOnMemberID() . " " .
						"AND  (sessionid IS NOT NULL OR ((I.pages IS NULL OR I.pages = 0) OR (B.datebacktooffice >= SUBDATE(NOW(),7)))) " .
						"ORDER BY B.datefromoffice DESC, E.name, D.name, B.id DESC"
					);
			
			$this->columns = array(
					array(
						'name'       => 'id',
						'length' 	 => 6,
						'pk'		 => true,
						'editable'	 => false,
						'filter'	 => false,
						'showInView' => false,
						'bind' 	 	 => false,
						'label' 	 => 'ID'
					),
					array(
						'name'       => 'provincename',
						'length' 	 => 30,
						'bind'		 => false,
						'editable'	 => true,
						'required'	 => false,
						'readonly'	 => true,
						'label' 	 => 'Province'
					),
					array(
						'name'       => 'courtname',
						'length' 	 => 40,
						'label' 	 => 'Court / Client',
						'editable'	 => true,
						'required'	 => false,
						'readonly'	 => true,
						'bind'	 	 => false
					),
					array(
						'name'       => 'action',
						'formatter'	 => 'statusFormatter',
						'length' 	 => 10,
						'bind'		 => false,
						'editable'	 => false,
						'filter'	 => false,
						'label' 	 => 'Status'
					),
					array(
						'name'       => 'j33number',
						'length' 	 => 20,
						'required'	 => false,
						'editable'	 => true,
						'readonly'	 => true,
						'bind'	 	 => false,
						'label' 	 => 'J33 Number'
					),
					array(
						'name'       => 'casenumber',
						'length' 	 => 20,
						'required'	 => false,
						'bind'	 	 => false,
						'editable'	 => true,
						'readonly'	 => true,
						'label' 	 => 'Case Number'
					),
					array(
						'name'       => 'instructions',
						'type'		 => 'BASICTEXTAREA',
						'showInView' => false,
						'bind'		 => false,
						'readonly'	 => true,
						'required'	 => false,
						'label' 	 => 'Instructions'
					),
					array(
						'name'       => 'typistid',
						'datatype'	 => 'typist',
						'showInView' => false,
						'editable'	 => false,
						'default'	 => getLoggedOnMemberID(),
						'label' 	 => 'Typist'
					),
					array(
						'name'       => 'typistname',
						'type'		 => 'DERIVED',
						'length' 	 => 30,
						'bind'		 => false,
						'function'   => 'typistname',
						'sortcolumn' => 'H.firstname',
						'editable'	 => false,
						'label' 	 => 'Name'
					),
					array(
						'name'       => 'plaintiff',
						'length' 	 => 20,
						'required'	 => false,
						'editable'	 => true,
						'readonly'	 => true,
						'bind'	 	 => false,
						'label' 	 => 'Parties'
					),
					array(
						'name'       => 'sessionid',
						'bind' 		 => false,
						'editable'	 => true,
						'required'	 => false,
						'label' 	 => 'Session'
					),
					array(
						'name'       => 'sessionpages',
						'bind' 		 => false,
						'editable'	 => true,
						'showInView' => false,
						'required'	 => false,
						'align'		 => 'right',
						'label' 	 => 'Pages'
					),
					array(
						'name'       => 'pages',
						'bind' 		 => false,
						'editable'	 => true,
						'required'	 => false,
						'align'		 => 'right',
						'label' 	 => 'Pages'
					),
					array(
						'name'       => 'ratename',
						'bind'		 => false,
						'readonly'	 => true,
						'length'	 => 30,
						'label' 	 => 'Rate'
					),
					array(
						'name'       => 'notificationid',
						'type'       => 'MULTIDATACOMBO',
						'length' 	 => 30,
						'label' 	 => 'Notify',
						'table'		 => 'members',
						'table_id'	 => 'member_id',
						'table_name' => 'fullname',
						'where'		 => "WHERE member_id IN (SELECT memberid FROM {$_SESSION['DB_PREFIX']}userroles WHERE roleid = 'OFFICE') ",
						'editable'	 => true,
						'bind'		 => false,
						'showInView' => false
					),
					array(
						'name'       => 'datefromoffice',
						'length' 	 => 12,
						'editable'	 => false,
						'bind'	 	 => false,
						'datatype'	 => 'date',
						'label' 	 => 'Date From Office'
					),
					array(
						'name'       => 'datebacktooffice',
						'length' 	 => 12,
						'bind'	 	 => false,
						'editable'	 => false,
						'datatype'	 => 'date',
						'label' 	 => 'Date Returned To Office'
					),
					array(
						'name'       => 'derivedinvoice',
						'type'		 => 'DERIVED',
						'length' 	 => 30,
						'bind'		 => false,
						'function'   => 'derivedinvoice',
						'sortcolumn' => 'H.invoicenumber',
						'editable'	 => false,
						'label' 	 => 'Invoice'
					)
				);
			$this->subapplications = array(
					array(
						'id'		  => 'invoicebutton',
						'title'		  => 'Invoice',
						'imageurl'	  => 'images/invoice.png',
						'script' 	  => 'invoice'
					),
					array(
						'id'		  => 'runningbutton',
						'title'		  => 'Running',
						'imageurl'	  => 'images/running.png',
						'script' 	  => 'running'
					),
					array(
						'title'		  => 'View Case Information',
						'imageurl'	  => 'images/view.png',
						'script' 	  => 'viewCase'
					),
					array(
						'title'		  => 'Upload Transcript',
						'imageurl'	  => 'images/article.png',
						'script' 	  => 'editDocuments'
					)
				);
		}

		/* Post header event. */
		public function postHeaderEvent() {
			include("template-invoice-screen.php");
			
			createDocumentLink();
?>
			<div id="casenumberdialog" class="modal">
				<h1>Existing Case Number</h1>
				<hr />
				<div id="existingcases">
				</div>
			</div>
<?php
		}
		
		public function postUpdateScriptEvent() {
?>
			if ($("#pages").attr("readonly") != true && $("#pages").attr("readonly") != "true") {
				pwAlert("Thanks, you're invoice was submitted successfully.");
				
			} else {
				pwAlert("Thanks, you're invoice upload notification has been emailed successfully.");
			}
			
<?php
		}
		
		public function preUpdateScriptEvent() {
?>
			if ($("#pages").parent().parent().is(":hidden") {
				$("#pages").val(parseInt($("#pages").val()) + parseInt($("#sessionpages").val()));
			}
<?php
		}
		
		public function postLoadScriptEvent() {
?>
			$("#runningbutton").hide();
<?php
		}
		
		public function postUpdateEvent($id) {
			$pages = $_POST['pages'];
			$casetypistid = $id;
			$found = false;
			$updateCase = true;
			
			if ($pages == "") {
				$pages = 0;	
			}
			
			$qry = "SELECT A.id " .
					"FROM  {$_SESSION['DB_PREFIX']}typistinvoices A " .
					"WHERE A.casetypistid = $casetypistid";
				 
			$result = mysql_query($qry);

			if (! $result) logError("Error: " . mysql_error());
			
			//Check whether the query was successful or not
			while (($member = mysql_fetch_assoc($result))) {
				$found = true;
				$updateCase = false;
			}
			
			$sessionid = $_POST['sessionid'];
			
			if ($sessionid != "") {
				$pages = $_POST['sessionpages'];
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}casetypistsessions SET " .
						"pages = $pages, " .
						"datebacktooffice = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
						"WHERE casetypistid = $casetypistid " .
						"AND sessionid = '$sessionid'";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}typistinvoices SET " .
						"pages = pages + $pages, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
						"WHERE casetypistid = $casetypistid";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$updateCase = true;
			}
			
			if (! $found) {
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}typistinvoices " .
						"(casetypistid, pages, createddate, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
						"VALUES " .
						"($casetypistid, $pages, NOW(), NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
			}
			
			if ($updateCase) {
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}casetypist SET " .
						"datebacktooffice = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
						"WHERE id = $casetypistid";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}cases SET " .
						"datebackfromtypist = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
						"WHERE id = (SELECT caseid FROM {$_SESSION['DB_PREFIX']}casetypist WHERE id = $casetypistid)";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
			}
			
			$qry = "SELECT C.j33number, C.casenumber, C.plaintiff, A.pages " .
					"FROM  {$_SESSION['DB_PREFIX']}typistinvoices A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}casetypist B " .
					"ON B.id = A.casetypistid " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}cases C " .
					"ON C.id = B.caseid " .
					"WHERE A.casetypistid = $casetypistid";
				 
			$result = mysql_query($qry);
			$j33number = "";
			$casenumber = "";
			$parties = "";
			$pages = "";

			if (! $result) logError("Error: " . mysql_error());
			
			//Check whether the query was successful or not
			while (($member = mysql_fetch_assoc($result))) {
				$j33number = $member['j33number'];
				$casenumber = $member['casenumber'];
				$parties = $member['plaintiff'];
				$pages = $member['pages'];
			}
			
			for ($i = 0; $i < count($_POST["notificationid"]); $i++) {
				$description = "<h3>Typist Invoice Upload.</h3><table>";
				$description .= "<tr><td><b>J33 Number : </b></td><td>$j33number</td></tr>";
				$description .= "<tr><td><b>Case Number : </b></td><td>$casenumber</td></tr>";
				$description .= "<tr><td><b>Parties : </b></td><td>$parties</td></tr>";
				$description .= "<tr><td><b>Pages : </b></td><td>$pages</td></tr>";
				$description .= "</table><h4>Invoice has been uploaded by " . GetUserName() . "</h4>";
				
				sendInternalUserMessage($_POST["notificationid"][$i], "Typist Invoice", $description);
			}
		}
		
		public function postScriptEvent() {
?>
			function fullName(node) {
				return (node.firstname + " " + node.lastname);
			}
			
			function invoice(id) {
				$("#pages").parent().parent().show();
				$("#sessionpages").parent().parent().hide();
				$("#sessionid").parent().parent().hide();
				$("#sessionid").val("");
				
				edit(id);
			}
			
			function running(id) {
				$("#pages").parent().parent().hide();
				$("#sessionpages").parent().parent().show();
				$("#sessionid").parent().parent().show();
				
				$.ajax({
						url: "createsessioncombo.php",
						dataType: 'html',
						async: false,
						data: { 
							casetypist: id
						},
						type: "POST",
						error: function(jqXHR, textStatus, errorThrown) {
							pwAlert("ERROR :" + errorThrown);
						},
						success: function(data) {
							$("#sessionid").parent().html(data);
						}
					});
				
				edit(id);
			}
			
			function onClick(node) {
				if (node.sessionid != null) {
					$("#runningbutton").show();
					$("#invoicebutton").hide();

				} else {
					$("#runningbutton").hide();
					$("#invoicebutton").show();
				}
			}
			
			function viewCase(node) {
				
				if ($("#sessionid").val() == "") {
					$("#pages").parent().parent().hide();
					$("#sessionpages").parent().parent().show();
					$("#sessionid").parent().parent().show();

				} else {
					$("#pages").parent().parent().show();
					$("#sessionpages").parent().parent().hide();
					$("#sessionid").parent().parent().hide();
				}

				view(node);
			}
			
			function editDocuments(node) {
				viewDocument(node, "addcasetypistdocument.php", node, "casetypistdocs", "casetypistid");
			}
			
			function typistname(node) {
				if (node.typistlastname == null) {
					return "";
				}
			
				return node.typistfirstname + " " + node.typistlastname;
			}

			function statusFormatter(el, cval, opts) {
				if (opts.sessionid != null) {
					return "<img src='images/running.png' />";
				}
				
				if (opts.pages != null && opts.pages != "" && opts.pages != "0") {
					return "<img src='images/allowed.png' />";
				}
				
				return "&nbsp;";
		    } 	
				
			function padDigits(number, digits) {
			    return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
			}	
		
			function derivedinvoice(node) {
				if (node.invoiceid == null) {
					return "";
				}
			
				return padDigits(node.invoiceid, 7);
			}
			
			function depositamount_onchange(node) {
				if ($("#depositamount").val() == "") {
					return "0.00";
				}
				$("#depositamount").val(new Number($("#depositamount").val()).toFixed(2));
			}
			
<?php
			include("template-invoice-script.php");
?>			
			$(document).ready(
					function() {
						$("#casenumberdialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: true,
								hide:"fade",
								width: 500,
								title:"Cases",
								open: function(event, ui){
									
								},
								buttons: {
									"Close": function() {
										$(this).dialog("close")
									}
								}
							});		
					}
				);
<?php
		}
	}
	
	$crud = new CaseTypistCrud();
	$crud->run();
?>
