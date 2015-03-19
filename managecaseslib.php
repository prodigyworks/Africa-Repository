<?php
	require_once("crud.php");
	
	include("template-invoice-functions.php");
	
	function savecontact() {
		$courtid = $_POST['contact_courtid'];
		$firstname = $_POST['contact_firstname'];
		$lastname = $_POST['contact_lastname'];
		$fax = $_POST['contact_fax'];
		$email = $_POST['contact_email'];
		$telephone = $_POST['contact_telephone'];
		$cellphone = $_POST['contact_cellphone'];
		$address = $_POST['contact_address'];
		$title = $_POST['contact_title'];
		$accountnumber = $_POST['contact_clientaccount'];
		$clientname = $_POST['contact_clientname'];
		
		if ($clientname != "-") {
			$province = getSiteConfigData()->privateclientprovinceid;
			
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}courts ( " .
					"provinceid, name, accountnumber, vatapplicable) VALUES (" .
					"$province, '$clientname', '$accountnumber', 'Y')";
			$result = mysql_query($qry);
				
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
			
			$courtid = mysql_insert_id();
		}
		
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}contacts ( " .
			"courtid, title, firstname, lastname, telephone, cellphone, fax, address, email ) VALUES (" .
			"$courtid, '$title', '$firstname', '$lastname', '$telephone', '$cellphone', '$fax', '$address', '$email' )";
		$result = mysql_query($qry);
			
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
	}
	
	function running() {
		$caseid = $_POST['session_caseid'];
		$typistid = $_POST['session_typistid'];
		$sessionid = $_POST['session_sessionid'];
		$sessions = explode(", ", $_POST['session_sessions']);
		$editpages = explode(", ", $_POST['session_pages']);
		$editids = explode(", ", $_POST['session_pageids']);
		$casetypistid = 0;
		
		$qry = "SELECT id FROM {$_SESSION['DB_PREFIX']}casetypist " .
				"WHERE caseid = $caseid " .
				"AND typistid = $typistid";
	
		$result = mysql_query($qry);
		
		if (! $result) logError("Error: " . mysql_error());
		
		while (($member = mysql_fetch_assoc($result))) {
			$casetypistid = $member['id'];
		}
		
		for ($i = 0; $i < count($editpages); $i++) {
			$page = $editpages[$i];
			$pageid = $editids[$i];
			$edit_sessionid = $sessions[$i];
			
			if ($page != "") {
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}casetypistsessions SET " .
						"pages = $page," .
						"sessionid = '$edit_sessionid', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
						"WHERE id = $pageid";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
			}
		}
				
		if ($casetypistid != 0 && $casetypistid != "") {
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}casetypistsessions (casetypistid, sessionid, datefromoffice, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUE ($casetypistid, '$sessionid', NOW(), NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
			$result = mysql_query($qry);
	
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
		}
	}
	
	class CaseCrud extends Crud {
		
	    public function __construct() {
	        parent::__construct();
	        
			$this->dateReceived = "";
			$this->dateExpectedBack = "";
			$this->dateBackFromTypist = "";
	        
			$this->title = "Cases";
			$this->postDataRefreshEvent = "loadComplete";
			$this->postAddScriptEvent = "postAddScriptEvent";
			$this->postEditScriptEvent = "postEditScriptEvent";
			$this->table = "{$_SESSION['DB_PREFIX']}cases";
			$this->validateForm = "validateCaseForm";
			$this->dialogwidth = 950;
			
			if (isset($_GET['id'])) {
				$this->sql = 
						"SELECT A.*, O.name AS officename, Y.invoicenumber, Z.quotenumber, D.name AS courtname, " .
						"E.name AS provincename, F.paymentnumber, F.invoicenumber, G.name AS clientcourtname, J.displayname AS ratename," .
						"(SELECT SUM(H.pages) FROM {$_SESSION['DB_PREFIX']}casetypist I INNER JOIN {$_SESSION['DB_PREFIX']}typistinvoices H ON H.casetypistid = I.id WHERE I.caseid = A.id) AS totalpages " .
						"FROM {$_SESSION['DB_PREFIX']}cases A " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
						"ON D.id = A.courtid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
						"ON E.id = D.provinceid " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices F " .
						"ON F.caseid = A.id " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}courts G " .
						"ON G.id = A.clientcourtid " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates J " .
						"ON J.id = A.rate " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices Y " .
						"ON Y.caseid = A.id " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}quotes Z " .
						"ON Z.caseid = A.id " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}offices O " .
						"ON A.officeid = O.id " .
						"WHERE A.courtid = " . $_GET['id'] . " " .
						"ORDER BY A.datereceived DESC, E.name, D.name";
				
			} else {
				$this->sql = 
					getFilteredData(
							"SELECT A.*, O.name AS officename, Y.invoicenumber, Z.quotenumber, D.name AS courtname, " .
							"E.name AS provincename, F.paymentnumber, F.invoicenumber, G.name AS clientcourtname, J.displayname AS ratename, " .
							"(SELECT SUM(H.pages) FROM {$_SESSION['DB_PREFIX']}casetypist I INNER JOIN {$_SESSION['DB_PREFIX']}typistinvoices H ON H.casetypistid = I.id WHERE I.caseid = A.id) AS totalpages " .
							"FROM {$_SESSION['DB_PREFIX']}cases A " .
							"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
							"ON D.id = A.courtid " .
							"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
							"ON E.id = D.provinceid " .
							"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices F " .
							"ON F.caseid = A.id " .
							"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}allcourts G " .
							"ON G.id = A.clientcourtid " .
							"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates J " .
							"ON J.id = A.rate " .
							"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoices Y " .
							"ON Y.caseid = A.id " .
							"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}quotes Z " .
							"ON Z.caseid = A.id " .
							"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}offices O " .
							"ON A.officeid = O.id " .
							"ORDER BY A.datereceived DESC, E.name, D.name"
						);
			}
			
			$this->columns = array(
					array(
						'name'       => 'provincename',
						'length' 	 => 30,
						'filter'	 => false,
						'bind'		 => false,
						'editable'	 => false,
						'label' 	 => 'Province'
					),
					array(
						'name'		 => 'courtname',
						'length'	 => 30,
						'editable'	 => false,
						'bind'		 => false
					),
					array(
						'name'       => 'courtid',
						'datatype'   => 'court',
						'length' 	 => 30,
						'label' 	 => 'Court / Client',
						'onchange'	 => 'courtid_onchange',
						'showInView' => false,
						'suffix'	 => "<img src='images/add.png' onclick='javascript: addContact()' />",
						'editable'	 => isset($_GET['id']) ? false : true,
						'default'	 => isset($_GET['id']) ? $_GET['id'] : 0
					),
					array(
						'name'       => 'contactid',
						'type'       => 'MULTIDATACOMBO',
						'length' 	 => 30,
						'filter'	 => false,
						'label' 	 => 'Contacts',
						'table'		 => 'contacts',
						'table_id'	 => 'id',
						'table_name' => 'fullname',
						'editable'	 => true,
						'required'	 => false,
						'bind'		 => false,
						'suffix'	 => "<img src='images/add.png' onclick='javascript: editContact()' />",
						'showInView' => false
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
						'label' 	 => 'J33 Number'
					),
					array(
						'name'       => 'clientcourtid',
						'type'       => 'DATACOMBO',
						'length' 	 => 30,
						'label' 	 => 'Client Court',
						'table'		 => 'allcourts',
						'table_id'	 => 'id',
						'required'	 => false,
						'where'		 => " WHERE A.vatapplicable = 'N' ",
						'onchange'	 => 'clientcourtid_onchange',
						'alias'		 => 'clientcourtname',
						'table_name' => 'name'
					),
					array(
						'name'       => 'casenumber',
						'length' 	 => 20,
						'required'	 => false,
						'onchange'	 => 'casenumber_onchange',
						'label' 	 => 'Case Number'
					),
					array(
						'name'       => 'plaintiff',
						'length' 	 => 30,
						'required'	 => false,
						'label' 	 => 'Parties / Accused'
					),
					array(
						'name'       => 'judge',
						'length' 	 => 30,
						'required'	 => false,
						'label' 	 => 'Judge'
					),
					array(
						'name'       => 'datereceived',
						'length' 	 => 12,
						'required'	 => true,
						'datatype'	 => 'date',
						'onchange'   => 'email_notification',
						'label' 	 => 'Date Received'
					),
					array(
						'name'       => 'datesenttotypist',
						'length' 	 => 0,
						'hidden'	 => true,
						'bind'		 => false,
						'editable'	 => false,
						'datatype'	 => 'date',
						'required'	 => false,
						'label' 	 => 'Date Sent To Typist'
					),
					array(
						'name'       => 'depositdate',
						'length' 	 => 12,
						'datatype'	 => 'date',
						'required'	 => false,
						'label' 	 => 'Estimate Payment Date'
					),
					array(
						'name'       => 'depositamount',
						'length' 	 => 12,
						'datatype'	 => 'double',
						'align'		 => 'right',
						'required'	 => false,
						'onchange'	 => 'depositamount_onchange',
						'label' 	 => 'Estimate Amount'
					),
					array(
						'name'       => 'quotenum',
						'function'   => 'quotenum',
						'type'		 => 'DERIVED',
						'sortcolumn' => 'Y.quotenumber',
						'length' 	 => 30,
						'bind'		 => false,
						'editable'	 => false,
						'required'	 => false,
						'label' 	 => 'Quote Number'
					),
					array(
						'name'       => 'invoicenum',
						'function'   => 'invoicenum',
						'type'		 => 'DERIVED',
						'sortcolumn' => 'Y.invoicenumber',
						'length' 	 => 30,
						'bind'		 => false,
						'editable'	 => false,
						'required'	 => false,
						'label' 	 => 'Invoice Number'
					),
					array(
						'name'       => 'quotenumber',
						'length' 	 => 30,
						'bind'		 => false,
						'filterprefix'		 => 'Z',
						'readonly'	 => true,
						'required'	 => false,
						'showInView' => false,
						'label' 	 => 'Quote Number'
					),
					array(
						'name'       => 'invoicenumber',
						'length' 	 => 30,
						'bind'		 => false,
						'readonly'	 => true,
						'required'	 => false,
						'showInView' => false,
						'filterprefix'		 => 'Y',
						'label' 	 => 'Invoice Number'
					),
					array(
						'name'       => 'readytoinvoice',
						'length' 	 => 20,
						'label' 	 => 'Ready To Invoice',
						'type'       => 'COMBO',
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
					),
					array(
						'name'       => 'transcriptrequestdate',
						'length' 	 => 12,
						'datatype'	 => 'date',
						'required'	 => false,
						'label' 	 => 'Audio Request Date'
					),
					array(
						'name'       => 'datetransmitted',
						'length' 	 => 32,
						'datatype'	 => 'date',
						'required'	 => false,
						'label' 	 => 'Date: Bound appeal record and copies returned to court'
					),
					array(
						'name'       => 'paymentnumber',
						'length' 	 => 30,
						'editable'	 => false,
						'bind'		 => false,
						'required'	 => false,
						'label' 	 => 'Payment Number'
					),
					array(
						'name'       => 'rate',
						'type'       => 'DATACOMBO',
						'length' 	 => 30,
						'label' 	 => 'Rate',
						'required'	 => false,
						'table'		 => 'invoiceitemtemplates',
						'table_id'	 => 'id',
						'alias' 	 => 'ratename',
						'where'		 => " WHERE A.type = 'T' AND displayname != '' ",
						'table_name' => 'displayname'
					),
					array(
						'name'       => 'time',
						'length' 	 => 9,
						'required'	 => false,
						'label' 	 => 'Minutes'
					),
					array(
						'name'       => 'totalpages',
						'length' 	 => 9,
						'required'	 => false,
						'readonly'	 => true,
						'bind'	 	 => false,
						'suffix'	 => "<img src='images/edit.png' onclick='javascript: editPages()' />",
						'label' 	 => 'Pages'
					),
					array(
						'name'       => 'dataexpectedbackfromtypist',
						'length' 	 => 20,
						'datatype'	 => 'date',
						'required'	 => false,
						'onchange'   => 'email_notification',
						'label' 	 => 'Date: Expected back from typist'
					),
					array(
						'name'       => 'datebackfromtypist',
						'length' 	 => 20,
						'datatype'	 => 'date',
						'required'	 => false,
						'onchange'   => 'email_notification',
						'label' 	 => 'Date: Back from typist'
					),
					array(
						'name'       => 'datehardcopyretcourt',
						'length' 	 => 32,
						'datatype'	 => 'date',
						'required'	 => false,
						'label' 	 => 'Date: hardcopy transcription returned to court'
					),
					array(
						'name'       => 'dateelectroniccopysubcourt',
						'length' 	 => 32,
						'datatype'	 => 'date',
						'required'	 => false,
						'label' 	 => 'Date: electronic copy of transcription submitted to court'
					),
					array(
						'name'       => 'datecdsreceivedfromtocourt',
						'length' 	 => 32,
						'datatype'	 => 'date',
						'required'	 => false,
						'label' 	 => 'Date: cd\'s received from court'
					),
					array(
						'name'       => 'nrreceivedmedia',
						'length' 	 => 50,
						'required'	 => false,
						'label' 	 => 'Nr Received cassettes/ CD/ court documents'
					),
					array(
						'name'       => 'instructions',
						'type'		 => 'BASICTEXTAREA',
						'showInView' => false,
						'required'	 => false,
						'label' 	 => 'Instructions'
					),
					array(
						'name'       => 'remarks',
						'required'	 => false,
						'type'		 => 'BASICTEXTAREA',
						'showInView' => false,
						'label' 	 => 'Remarks'
					),
					array(
						'name'       => 'transcripttype',
						'length' 	 => 80,
						'required'	 => false,
						'label' 	 => 'Transcript Type'
					),
					array(
						'name'       => 'typistid',
						'type'       => 'MULTIDATACOMBO',
						'length' 	 => 30,
						'filter'	 => false,
						'label' 	 => 'Typists',
						'table'		 => 'members',
						'table_id'	 => 'member_id',
						'table_name' => 'fullname',
						'where'		 => "WHERE member_id IN (SELECT memberid FROM {$_SESSION['DB_PREFIX']}userroles WHERE roleid = 'TYPIST') ",
						'editable'	 => true,
						'required'	 => false,
						'bind'		 => false,
						'showInView' => false
					),
					array(
						'name'       => 'officeid',
						'type'       => 'DATACOMBO',
						'length' 	 => 30,
						'label' 	 => 'Office',
						'table'		 => 'offices',
						'table_id'	 => 'id',
						'alias'		 => 'officename',
						'table_name' => 'name',
						'editable'	 => false,
						'default'	 => GetOfficeID(getLoggedOnMemberID())
					),
				);
				
			$this->messages = array(
					array('id'		  => 'session_caseid'),
					array('id'		  => 'session_typistid'),
					array('id'		  => 'session_sessionid'),
					array('id'		  => 'session_pageids'),
					array('id'		  => 'session_sessions'),
					array('id'		  => 'contact_courtid'),
					array('id'		  => 'contact_title'),
					array('id'		  => 'contact_firstname'),
					array('id'		  => 'contact_lastname'),
					array('id'		  => 'contact_telephone'),
					array('id'		  => 'contact_cellphone'),
					array('id'		  => 'contact_fax'),
					array('id'		  => 'contact_email'),
					array('id'		  => 'contact_address'),
					array('id'		  => 'contact_clientaccount'),
					array('id'		  => 'contact_clientname'),
					array('id'		  => 'session_pages')
					
				);
				
			$this->subapplications = array(
					array(
						'title'		  => 'Invoice',
						'imageurl'	  => 'images/invoice.png',
						'script' 	  => 'editInvoice'
					),
					array(
						'title'		  => 'Quote',
						'imageurl'	  => 'images/accept.png',
						'script' 	  => 'editQuote'
					),
					array(
						'title'		  => 'Typists',
						'imageurl'	  => 'images/team.png',
						'application' => 'managecasetypists.php'
					),
					array(
						'title'		  => 'Running',
						'imageurl'	  => 'images/running.png',
						'script' 	  => 'running'
					),
					array(
						'title'		  => 'Documents',
						'imageurl'	  => 'images/article.png',
						'script' 	  => 'editDocuments'
					),
					array(
						'title'		  => 'Contacts',
						'imageurl'	  => 'images/user.png',
						'script' 	  => 'editContact'
					)
				);
		}

		/* Post header event. */
		public function postHeaderEvent() {
			include("template-invoice-screen.php");
			include("template-quote-screen.php");
?>
<?php
			
			createDocumentLink();
?>
			<div id="pagesdialog" class="modal">
				<div id="pagescontainer"></div>
			</div>
			<div id="contactdialog" class="modal">
				<form id="contactdialogform" method="POST">
					<table>
						<tr style='display:none'>
							<td>Client</td>
							<td><?php createCombo("form_contact_clientid", "id", "name", "{$_SESSION['DB_PREFIX']}allcourts", "WHERE vatapplicable = 'Y'", false) ?></td>
						</tr>
						<tr id='row_clientname' style='display:none'>
							<td>Client</td>
							<td><input type="text" required="true" id="form_contact_clientname" size=50 /></td>
						</tr>
						<tr id='row_clientaccount' style='display:none'>
							<td>Account Number</td>
							<td><input type="text" id="form_contact_account_number" size=30 /></td>
						</tr>
												<tr>
							<td>Title</td>
							<td>
								<SELECT id="form_contact_title" required="true">
									<OPTION value=""></OPTION>
									<OPTION value="Mr">Mr</OPTION>
									<OPTION value="Mrs">Mrs</OPTION>
									<OPTION value="Miss">Miss</OPTION>
									<OPTION value="Ms">Ms</OPTION>
									<OPTION value="Master">Master</OPTION>
								</SELECT>
							</td>
						</tr>
						<tr>
							<td>First Name</td>
							<td><input id="form_contact_firstname" size=20 required="true" /></td>
						</tr>
						<tr>
							<td>Last Name</td>
							<td><input id="form_contact_lastname" size=20 required="true" /></td>
						</tr>
						<tr>
							<td>Telephone</td>
							<td><input id="form_contact_telephone" size=15 /></td>
						</tr>
						<tr>
							<td>Cellphone</td>
							<td><input id="form_contact_cellphone" size=15 /></td>
						</tr>
						<tr>
							<td>Fax</td>
							<td><input id="form_contact_fax" size=15 /></td>
						</tr>
						<tr>
							<td>E-mail</td>
							<td><input id="form_contact_email" size=40 required="true" /></td>
						</tr>
						<tr>
							<td>Address</td>
							<td><textarea id="form_contact_address" cols=60 rows=5></textarea></td>
						</tr>
					</table>
				</form>
			</div>
			<div id="runningdialog" class="modal">
				<div id="runningcontainer"></div>
			</div>
			<div id="casenumberdialog" class="modal">
				<h1>Existing Case Number</h1>
				<hr />
				<div id="existingcases">
				</div>
			</div>
<?php
		}
		
		public function getEmailHTML($caseid) {
			$dataexpectedbackfromtypist = "";
			$judge = "";
			$courtname = "";
			$j33number = "";
			$casenumber = "";
			$plaintiff = "";
			$ratename = "";
				
			$qry = "SELECT A.*, DATE_FORMAT(A.dataexpectedbackfromtypist, '%d/%m/%Y') AS dataexpectedbackfromtypist,
					A.j33number, A.casenumber, B.name AS courtname, C.name AS ratename
					FROM {$_SESSION['DB_PREFIX']}cases A
					INNER JOIN {$_SESSION['DB_PREFIX']}courts B
					ON B.id = A.courtid
					LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}invoiceitemtemplates C
					ON C.id = A.rate
					WHERE A.id = $caseid";
						
			$result = mysql_query($qry);
				
			if (! $result) logError($qry . " - " . mysql_error());
				
			while (($member = mysql_fetch_assoc($result))) {
				$dataexpectedbackfromtypist = $member['dataexpectedbackfromtypist'];
				$judge = $member['judge'];
				$courtname = $member['courtname'];
				$j33number = $member['j33number'];
				$casenumber = $member['casenumber'];
				$plaintiff = $member['plaintiff'];
				$ratename = $member['ratename'];
			}

			return "<table>
						 <tr>
							<td><b>Court/Client</b></td>
							<td>$courtname</td>
						 </tr>
						 <tr>
							<td><b>J33 Number</b></td>
							<td>$j33number</td>
						 </tr>
						 <tr>
							<td><b>Case Number</b></td>
							<td>$casenumber</td>
						 </tr>
						 <tr>
							<td><b>Parties/Accused</b></td>
							<td>$plaintiff</td>
						 </tr>
						 <tr>
							<td><b>Judge</b></td>
							<td>$judge</td>
						 </tr>
						 <tr>
							<td><b>Rate</b></td>
							<td>$ratename</td>
						 </tr>
						 <tr>
							<td><b>Date: Expected back from typist</b></td>
							<td>$dataexpectedbackfromtypist</td>
						 </tr>
					 </table>";
		}
		
		public function postInsertEvent() {
			$caseid = mysql_insert_id();
			$table = $this->getEmailHTML($caseid);
			$membername = GetUserName();
				
			for ($i = 0; $i < count($_POST['typistid']); $i++) {
				$typist = $_POST['typistid'][$i];

				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}casetypist (caseid, typistid, datefromoffice, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUE ($caseid, $typist, NOW(), NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
				$result = mysql_query($qry);

				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				sendInternalTypistMessage(
						$typist, 
						"Case Assignment", 
						"<h3>You have been assigned the following case by $membername</h3><br>$table"
					);
			}
			
			for ($i = 0; $i < count($_POST['contactid']); $i++) {
				$contactid = $_POST['contactid'][$i];

				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}casecontacts (caseid, contactid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUE ($caseid, $contactid, NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
				$result = mysql_query($qry);

				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
			}
			
			addAuditLog("C", "I", $caseid);
		}
		
		public function preUpdateEvent($id) {
			$qry = "SELECT " .
					"DATE_FORMAT(dataexpectedbackfromtypist, '%d/%m/%Y') AS dataexpectedbackfromtypist, " .
					"DATE_FORMAT(datereceived, '%d/%m/%Y') AS datereceived, " .
					"DATE_FORMAT(datebackfromtypist, '%d/%m/%Y') AS datebackfromtypist " .
					"FROM {$_SESSION['DB_PREFIX']}cases " .
					"WHERE id = $id";
				
			$result = mysql_query($qry);
				
			if (! $result) logError($qry . " - " . mysql_error());
				
			//Check whether the query was successful or not
			if ($result) {
				while (($member = mysql_fetch_assoc($result))) {
					$this->dateExpectedBack = $member['dataexpectedbackfromtypist'];
					$this->dateReceived = $member['datereceived'];
					$this->dateBackFromTypist != $member['datebackfromtypist'];
				}
			}
		}
		
		public function postUpdateEvent($caseid) {
			$in = "(";
			$ix = 0;
			$alerttrigger = false;
			$table = $this->getEmailHTML($caseid);
			$membername = GetUserName();
			
			if ($this->dateExpectedBack != $_POST['dataexpectedbackfromtypist'] || 
				$this->dateReceived != $_POST['datereceived'] ||
				$this->dateBackFromTypist != $_POST['datebackfromtypist']) {
				$alerttrigger = true;
			}
			
			logError($this->dateExpectedBack . " - " . $_POST['dataexpectedbackfromtypist'] , false);
			
			for ($i = 0; $i < count($_POST['typistid']); $i++) {
				$typist = $_POST['typistid'][$i];

				if ($ix++ > 0) {
					$in .= ", ";
				}
				
				$in .= $typist;
			}
			
			$in .= ")";
			
			if ($ix > 0) {
				$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}casetypist WHERE caseid = $caseid AND typistid NOT IN $in";
				$result = mysql_query($qry);
	
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
			}
						
			for ($i = 0; $i < count($_POST['typistid']); $i++) {
				$typist = $_POST['typistid'][$i];
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}casetypist (caseid, typistid, datefromoffice, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUE ($caseid, $typist, NOW(), NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
				$result = mysql_query($qry);

				if (! $result) {
					if (mysql_errno() != 1062) {
						logError($qry . " - " . mysql_error());
					}
					
				} else {
					sendInternalTypistMessage(
							$typist,
							"Case Assignment",
							"<h3>You have been assigned the following case by $membername</h3><br>$table"
						);
				}
			}
			
			$in = "(";
			$ix = 0;
				
			for ($i = 0; $i < count($_POST['contactid']); $i++) {
				$typist = $_POST['contactid'][$i];
			
				if ($ix++ > 0) {
					$in .= ", ";
				}
			
				$in .= $typist;
			}
				
			$in .= ")";
				
			if ($ix > 0) {
				$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}casecontacts WHERE caseid = $caseid AND contactid NOT IN $in";
				$result = mysql_query($qry);
			
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
			}
			
			for ($i = 0; $i < count($_POST['contactid']); $i++) {
				$contactid = $_POST['contactid'][$i];
			
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}casecontacts (caseid, contactid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUE ($caseid, $contactid, NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
				$result = mysql_query($qry);
			
				if (! $result) {
					if (mysql_errno() != 1062) {
						logError($qry . " - " . mysql_error());
					}
				}
				
				if ($alerttrigger) {
					$qry = "SELECT email, fullname, firstname FROM {$_SESSION['DB_PREFIX']}contacts " .
							"WHERE id = $contactid";
				
					$result = mysql_query($qry);
					
					if (! $result) logError("Error: " . mysql_error());
					
					while (($member = mysql_fetch_assoc($result))) {
						$contactemail = $member['email'];
						$contactfullname = $member['fullname'];
						$contactfirstname = $member['firstname'];
						$message = "TEST";
							
//						smtpmailer($contactemail, "support@iafricatranscriptions.co.za", "I Africa Transcriptions (PTY) LTD", "Case changes", getEmailHeader() . "<h4>Dear $contactfirstname,</h4><p>" . $message . "</p>" . getEmailFooter());
					}
				}
			}
				
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}cases SET datesenttotypist = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE id = $caseid";
			$result = mysql_query($qry);

			if (! $result) {
				logError($qry . " - " . mysql_error());
			}			

			addAuditLog("C", "U", $caseid);
		}
		
		public function postDeleteEvent($id) {
			addAuditLog("C", "D", $id);
		}
		
		
		public function postEditScriptEvent() {
?>
			emailEvent = false;
			
			$("#courtid").trigger("change");
			$("#typistid option").removeAttr("selected");
			$("#contactid option").removeAttr("selected");
			
			callAjax(
					"finddata.php", 
					{ 
						sql: "SELECT typistid FROM <?php echo $_SESSION['DB_PREFIX'];?>casetypist WHERE caseid = " + id
					},
					function(data) {
						if (data.length > 0) {
							for (var i = 0; i < data.length; i++) {
								$("#typistid option[value='" + data[i].typistid + "']").attr("selected", "1");
							}
						}
						
						$("#typistid").multiselect("refresh");
					}
				);
						
			callAjax(
					"finddata.php", 
					{ 
						sql: "SELECT contactid FROM <?php echo $_SESSION['DB_PREFIX'];?>casecontacts WHERE caseid = " + id
					},
					function(data) {
						if (data.length > 0) {
							for (var i = 0; i < data.length; i++) {
								$("#contactid option[value='" + data[i].contactid + "']").attr("selected", "1");
							}
						}
						
						$("#contactid").multiselect("refresh");
					}
				);
				
			
			<?php
		}
		
		
		public function postAddScriptEvent() {
?>
			$("#readytoinvoice").val("N");
			$("#depositamount").val("0.00");
			$("#typistid option").attr("selected", false);
			$("#contactid option").attr("selected", false);
			$("#rate").html("<OPTION value='0'></OPTION>");
			
			
			emailEvent = false;
<?php
		}
		
		public function postScriptEvent() {
?>
			var currentID = 0;
			var emailEvent = false;
			
			function addContact() {
				clearContactDialog();
				
				$("#row_clientaccount").show();
				$("#row_clientname").show();
				
				$("#contactdialog").dialog("open");
			}
			
			function clearContactDialog() {
				$("#form_contact_clientname").val("");
				$("#form_contact_title").val("");
				$("#form_contact_firstname").val("");
				$("#form_contact_lastname").val("");
				$("#form_contact_telephone").val("");
				$("#form_contact_cellphone").val("");
				$("#form_contact_fax").val("");
				$("#form_contact_email").val("");
				$("#form_contact_address").val("");
				$("#form_contact_account_number").val("");
			}
			
			function editContact(node) {
				clearContactDialog();
				
				$("#row_clientaccount").hide();
				$("#row_clientname").hide();
				$("#form_contact_clientname").val("-");
				
				if (node != null) {
					callAjax(
							"finddata.php", 
							{ 
								sql: "SELECT courtid FROM <?php echo $_SESSION['DB_PREFIX'];?>cases WHERE id = " + node
							},
							function(data) {
								if (data.length > 0) {
									$("#form_contact_clientid").val(data[0].courtid);
								}
							}
						);
				
				} else {
					$("#form_contact_clientid").val($("#courtid").val());
				}
				
				$("#contactdialog").dialog("open");
			}
			
			function validateCaseForm() {
				var xselected = false;
				
				$("#typistid option").each(function(){
						if ($(this).attr("selected") == true) {
							xselected = true;
						} 
					});
				
				if (xselected && $("#rate").val() == 0) {
					pwAlert("The Rate field may not be blank when submitting the case to a typist");
					return false;
				}
				
				if ($("#casenumber").val().match(/^[A-Za-z0-9\/]*$/) == null) {
					pwAlert("Case number may only consist of characters, numbers and the '/'");
					return false;
				}
				
				if ($("#j33number").val().match(/^[A-Za-z0-9\/]*$/) == null) {
					pwAlert("J33 number may only consist of characters, numbers and the '/'");
					return false;
				}
				
				return true;
			}
			
			function running(id) {
				populateRunningTable(id, function() { 							
						$("#runningdialog").dialog("open");
				 	});
			}
			
			function populateRunningTable(id, callback) {
				currentID = id;
				
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.*, D.pages AS origpages, B.datefromoffice AS origdatefromoffice, B.id AS casetypistid, C.fullname, C.member_id, E.time, E.remarks, E.instructions FROM <?php echo $_SESSION['DB_PREFIX'];?>casetypist B INNER JOIN <?php echo $_SESSION['DB_PREFIX'];?>cases E ON E.id = B.caseid LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>casetypistsessions A ON B.id = A.casetypistid LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>typistinvoices D ON D.casetypistid = B.id INNER JOIN <?php echo $_SESSION['DB_PREFIX'];?>members C ON C.member_id = B.typistid WHERE B.caseid = " + id
						},
						function(data) {
							var html = "";
							
							if (data.length > 0) {
								for (var i = 0; i < data.length; i++) {
									var node = data[i];
									var nodeid = node.id;
									var datefrom = node.datefromoffice == "" ? node.origdatefromoffice : node.datefromoffice;
									var dateto = node.datebacktooffice;
									var nodepages = node.pages == null ? node.origpages : node.pages;
									var editcasetypistid = node.casetypistid;
									<?php
									if (! isUserInRole("ADMIN")) {
									?>
										var nodesession = node.sessionid == null ? "N/A" : "<INPUT type='text' cols=40 id='edit_sessions' name='edit_sessions[]' value='" + node.sessionid + "' />";
									<?php
										
									} else {
									?>
										var nodesession = node.sessionid == null ? "N/A" : node.sessionid + "<INPUT type='hidden' cols=40 id='edit_sessions' name='edit_sessions[]' value='" + node.sessionid + "' />";
									<?php
									}
									?>
									
									if (i == 0) {
										html += "<TABLE cellspacing=3 width='100%'>";
										html += "<TR><TD>Minutes</TD><TD><INPUT type='text' readonly value='" + node.time + "' /></TD></TR>";
										html += "<TR><TD>Remarks</TD><TD><TEXTAREA cols=100 rows=5 readonly>" + node.remarks + "'</TEXTAREA></TD></TR>";
										html += "<TR><TD>Instructions</TD><TD><TEXTAREA cols=100 rows=5 readonly>" + node.instructions + "'</TEXTAREA></TD></TR>";
										html += "</TABLE><HR />";
										html += "<DIV style='height:300px; overflow:auto'><TABLE cellspacing=2 width='100%'><THEAD><TR><TD>Typist</TD><TD>Session</TD><TD>Pages</TD><TD>Date Sent</TD><TD>Date Recieved</TD></TR></THEAD>";
									}
									
									if (nodepages == null) {
										nodepages = 0;
									}
									
									if (node.sessionid == null) {
										html += "<TR><TD>" + node.fullname + "</TD><TD>" + nodesession + "</TD><TD>" + nodepages + "</TD><TD>" + datefrom + "</TD></TR>";
										
									} else {
										html += "<TR><TD>" + node.fullname + "</TD><TD>" + nodesession + "</TD><TD><INPUT type='text' id='edit_pages' name='edit_pages[]' size=10 value='" + nodepages + "'/><INPUT type='hidden' id='edit_id' name='edit_id[]' value='" + nodeid + "'/></TD><TD>" + datefrom + "</TD><TD><b>" + dateto + "</b></TD></TR>";
									}
								}
							}
							
							html += "<TR><TD>";
<?php
							ob_start();
							createUserCombo("new_sessionuserid", " WHERE A.member_id in (SELECT AA.memberid FROM {$_SESSION['DB_PREFIX']}userroles AA WHERE AA.roleid = 'TYPIST') ");
							$imageContents = ob_get_contents();
							ob_end_clean();
							
							echo "html += \"" . str_replace("\n", " ", $imageContents) . "\";";
?>
							
							html += "</TD><TD><input type='text' id='new_sessionid' name='new_sessionid' value='' size=30 /></TD><TD>&nbsp;</TD><TD><?php echo date("d/m/Y"); ?></TD><TD></TD></TR>";
							html += "</TABLE></DIV>";

							$("#runningcontainer").html(html);
							
							callback();
							
						},
						false,
						function(error, x , d) {
							pwAlert("ERROR:" + error + x + d);
						}
					);
					
			}
			
			function loadComplete() {
			    var rowIds = $("#tempgrid").getDataIDs();
			    var today = new Date();
			    var todaystr;
			    
			    todaystr = padZero(today.getDate());
			    todaystr += "/" + padZero(today.getMonth() + 1);
			    todaystr += "/" + (1900 + today.getYear());
			    
			    for (i = 1; i <= rowIds.length; i++) {//iterate over each row
			        rowData = $("#tempgrid").jqGrid('getRowData', i);

			        if (rowData['invoicenum'] != 'No Invoice') {
			            $("#tempgrid").jqGrid('setRowData', i, false, "green-row");
			            
			        } else if (rowData['rate'].startsWith('Overnight') && rowData['datesenttotypist'] != "") {
			        	if (daysBetweenDates(rowData['datesenttotypist'], todaystr) > 0 ) {
				            $("#tempgrid").jqGrid('setRowData', i, false, "red-row");
			        		
			        	} else {
				            $("#tempgrid").jqGrid('setRowData', i, false, "orange-row");
			        	}
			            
			        } else if (rowData['rate'].startsWith('Urgent') && rowData['datesenttotypist'] != "") {
			        	if (daysBetweenDates(rowData['datesenttotypist'], todaystr) > 1 ) {
				            $("#tempgrid").jqGrid('setRowData', i, false, "red-row");
			        		
			        	} else {
				            $("#tempgrid").jqGrid('setRowData', i, false, "orange-row");
			        	}
			            
			        } else if (rowData['rate'].startsWith('Normal') && rowData['datesenttotypist'] != "") {
			        	if (daysBetweenDates(rowData['datesenttotypist'], todaystr) > 5 ) {
				            $("#tempgrid").jqGrid('setRowData', i, false, "red-row");
			        		
			        	} else {
				            $("#tempgrid").jqGrid('setRowData', i, false, "orange-row");
			        	}
			            
			        } else if (rowData['datesenttotypist'] != "") {
			            $("#tempgrid").jqGrid('setRowData', i, false, "orange-row");
			        }
			    }
			}
			
			function editDocuments(node) {
				viewDocument(node, "addcasedocument.php", node);
			}
			
			function invoicenum(node) {
				if (node.invoicenumber == "" || node.invoicenumber == null) {
					return "No Invoice";
				}
				
				return node.invoicenumber;
			}
			
			function quotenum(node) {
				if (node.quotenumber == "" || node.quotenumber == null) {
					return "No Quote";
				}
				
				return node.quotenumber;
			}
			
			function editPages() {
				var id = $("#crudid").val();
				
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.id, A.pages, B.fullname, C.id AS casetypistid FROM <?php echo $_SESSION['DB_PREFIX'];?>casetypist C LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>typistinvoices A ON C.id = A.casetypistid INNER JOIN <?php echo $_SESSION['DB_PREFIX'];?>cases D ON D.id = C.caseid  INNER JOIN <?php echo $_SESSION['DB_PREFIX'];?>members B ON B.member_id = C.typistid WHERE D.id = " + id
						},
						function(data) {
							if (data.length > 0) {
								var html = "<TABLE width='60%'><THEAD><TR><TD>Typist</TD><TD>Pages</TD></TR></THEAD>";
								
								for (var i = 0; i < data.length; i++) {
									var node = data[i];
									var nodeid = node.id;
									var nodepages = node.pages;
									var editcasetypistid = node.casetypistid;
									
									if (nodeid == null) {
										nodeid = 0;
										nodepages = 0;
									}
									
									html += "<TR><TD>" + node.fullname + "</TD><TD><input type='hidden' id='editcasetypistid' name='editcasetypistid[]' value='" + editcasetypistid + "' /><input type='hidden' id='editpageid' name='editpageid[]' value='" + nodeid + "' /><input type='text' id='editpages' name='editpages[]' value='" + nodepages + "' /></TD></TR>";
								}
								
								html += "</TABLE>";
								
								$("#pagescontainer").html(html);
								
								$("#pagesdialog").dialog("open");
							}
							
						},
						false,
						function(error) {
							pwAlert("ERROR:" + error);
						}
					);
					
			}
			
			function email_notification() {
				emailEvent = true;
			}
			
			function courtid_onchange() {
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT vatapplicable, provinceid FROM <?php echo $_SESSION['DB_PREFIX'];?>courts WHERE id = " + $("#courtid").val()
						},
						function(data) {
							if (data.length > 0) {
								var node = data[0];
								
								$.ajax({
										url: "createprovinceratecombo.php",
										dataType: 'html',
										async: false,
										data: { 
											provinceid: node.provinceid
										},
										type: "POST",
										error: function(jqXHR, textStatus, errorThrown) {
											pwAlert("ERROR :" + errorThrown);
										},
										success: function(data) {
											var rateval = $("#rate").val();

											$("#rate").html(data);
											$("#rate").val(rateval);
										}
									});

								$.ajax({
										url: "createcontactcombo.php",
										dataType: 'html',
										async: false,
										data: { 
											courtid: $("#courtid").val()
										},
										type: "POST",
										error: function(jqXHR, textStatus, errorThrown) {
											pwAlert("ERROR :" + errorThrown);
										},
										success: function(data) {
											$("#contactid").html(data);
											$("#contactid option").removeAttr("selected");
											
											$("#contactid").multiselect("refresh");
										}
									});
									
													
								if (node.vatapplicable == "N") {
									$("#j33number").removeAttr("disabled");
									$("#clientcourtid").attr("disabled", true);
									$("#clientcourtid").val("0");
									$("#depositamount").attr("disabled", true);
									$("#depositdate").attr("disabled", true);
									
								} else {
									$("#j33number").attr("disabled", true);
									$("#j33number").val("");
									$("#clientcourtid").removeAttr("disabled");
									$("#depositamount").removeAttr("disabled");
									$("#depositdate").removeAttr("disabled");
								}
							}
						},
						false
					);
			}
			
			function casenumber_onchange() {
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT id, j33number, DATE_FORMAT(datedelivered, '%d/%m/%Y') AS datedelivered FROM <?php echo $_SESSION['DB_PREFIX'];?>cases WHERE casenumber = '" + $("#casenumber").val() + "'"
						},
						function(data) {
							if (data.length > 0) {
								var html = "<table class='grid2' width='100%'><thead><tr><td>Case ID</td><td>J33 Number</td><td>Date Delivered</td></tr></thead>\n";
								
								for (var i = 0; i < data.length; i++) {
									var node = data[i];
									
									html += "<tr><td><a target='_new' href='casedetailreport.php?id=" + node.id + "'>" + node.id + "</a></td><td>" + node.j33number + "</td><td>" + node.datedelivered + "</td></tr>\n";
								}
								
								html += "</table>\n";
								
								$("#existingcases").html(html);
								$("#casenumberdialog").dialog("open");
							}
						}
					);
			}
			
			function clientcourtid_onchange() {
				if ($("#clientcourtid").val() != "") {
					$("#j33number").val("");
				}	
			}
			
			function j33number_onchange() {
				if ($("#j33number").val() != "") {
					$("#clientcourtid").val("0");
				}	
			}
			
			function depositamount_onchange(node) {
				if ($("#depositamount").val() == "") {
					return "0.00";
				}
				$("#depositamount").val(new Number($("#depositamount").val()).toFixed(2));
			}
			
<?php
			include("template-quote-script.php");
			include("template-invoice-script.php");
?>			
			function saveRunning() {
				var pageids = "";
				var pages = "";
				var sessions = "";
				
				callAjax(
						"verifysessionsave.php", 
						{ 
							session_caseid: currentID,
							session_typistid: $("#new_sessionuserid").val(), 
							session_sessionid: $("#new_sessionid").val()
						},
						function(data) {
							if (data.length > 0) {
								if (data[0].error != "") {
									pwAlert(data[0].error);
									return;
								}
								
								$('input[name="edit_id[]"]').each(function(){
										if (pageids != "") {
											pageids += ", ";
										}
										
										pageids += $(this).val();
									});
								
								$('input[name="edit_sessions[]"]').each(function(){
										if (sessions != "") {
											sessions += ", ";
										}
										
										sessions += $(this).val();
									});
								
								$('input[name="edit_pages[]"]').each(function(){
										if (pages != "") {
											pages += ", ";
										}
										
										pages += $(this).val();
									});
								
								post("editform", "running", "submitframe", {
										session_caseid: currentID, 
										session_pageids :pageids,
										session_pages: pages,
										session_sessions: sessions,
										session_typistid: $("#new_sessionuserid").val(), 
										session_sessionid: $("#new_sessionid").val()
									});
							}
							
						},
						false,
						function(error) {
							pwAlert("ERROR:" + error);
						}
					);
			}
			
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
							
						$("#runningdialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: true,
								hide:"fade",
								width: 650,
								title:"Running Case",
								open: function(event, ui){
									
								},
								buttons: {
									"Save": function() {
										$('#submitframe').one('load', function() {
												 $('#submitframe').unbind('load');
												populateRunningTable(currentID, function() { });
										    });
										    
										saveRunning();
											
									},
									"Save & Close": function() {
										$(this).dialog("close");

										saveRunning();
									},
									"Cancel": function() {
										$(this).dialog("close");
									}
								}
							});		
							
						$("#contactdialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: true,
								hide:"fade",
								width: 650,
								title:"Add Contact",
								open: function(event, ui){
									
								},
								buttons: {
									"Save": function() {
										if (! verifyStandardForm("#contactdialogform")) {
											return;
										}
										
										$('#submitframe').one('load', function() {
												var id = $("#crudid").val();
												
												 $('#submitframe').unbind('load');
												 
												 if ($("#form_contact_clientname").val() != "-") {
												
													 $.ajax({
															url: "createclientcombo.php",
															dataType: 'html',
															async: false,
															data: { 
															},
															type: "POST",
															error: function(jqXHR, textStatus, errorThrown) {
																pwAlert("ERROR :" + errorThrown);
															},
															success: function(data) {
																$("#courtid").html(data);
															}
														});
												 }
																									
												
												 $.ajax({
														url: "createcontactcombo.php",
														dataType: 'html',
														async: false,
														data: { 
															courtid: $("#courtid").val()
														},
														type: "POST",
														error: function(jqXHR, textStatus, errorThrown) {
															pwAlert("ERROR :" + errorThrown);
														},
														success: function(data) {
															$("#contactid").html(data);
															$("#contactid option").removeAttr("selected");
															
															callAjax(
																	"finddata.php", 
																	{ 
																		sql: "SELECT contactid FROM <?php echo $_SESSION['DB_PREFIX'];?>casecontacts WHERE caseid = " + id
																	},
																	function(data) {
																		if (data.length > 0) {
																			for (var i = 0; i < data.length; i++) {
																				$("#contactid option[value='" + data[i].contactid + "']").attr("selected", "1");
																			}
																		}
																		
																		$("#contactid").multiselect("refresh");
																	}
																);
														}
													});
																										
										    });
										    
										$(this).dialog("close");
										
										post("editform", "savecontact", "submitframe", {
												contact_courtid: $("#form_contact_clientid").val(), 
												contact_title :$("#form_contact_title").val(),
												contact_firstname :$("#form_contact_firstname").val(),
												contact_lastname :$("#form_contact_lastname").val(),
												contact_telephone :$("#form_contact_telephone").val(),
												contact_cellphone :$("#form_contact_cellphone").val(),
												contact_fax :$("#form_contact_fax").val(),
												contact_email :$("#form_contact_email").val(),
												contact_address :$("#form_contact_address").val(),
												contact_clientname: $("#form_contact_clientname").val(),
												contact_clientaccount: $("#form_contact_account_number").val()
											});
																					
									},
									"Cancel": function() {
										$(this).dialog("close");
									}
								}
							});		
						
						$("#pagesdialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: true,
								hide:"fade",
								width: 500,
								title:"Pages",
								open: function(event, ui){
									
								},
								buttons: {
									Ok: function() {
										var pageids = "";
										var pages = "";
										var casetypistids = "";
										
										$('input[name="editpageid[]"]').each(function(){
												if (pageids != "") {
													pageids += ", ";
												}
												
												pageids += $(this).val();
											});
										
										$('input[name="editpages[]"]').each(function(){
												if (pages != "") {
													pages += ", ";
												}
												
												pages += $(this).val();
											});
										
										$('input[name="editcasetypistid[]"]').each(function(){
												if (casetypistids != "") {
													casetypistids += ", ";
												}
												
												casetypistids += $(this).val();
											});

										callAjax(
												"savepages.php", 
												{ 
													casetypistids: casetypistids,
													pageids: pageids,
													pages: pages
												},
												function(data) {
													if (data.length > 0) {
														$("#editdialog #totalpages").val(data[0].pages);
													}
													
												},
												false,
												function(error) {
													pwAlert("ERROR:" + error);
												}
											);
										
										$(this).dialog("close")
									},
									Cancel: function() {
										$(this).dialog("close")
									}
								}
							});		
					}
				);
<?php
		}
	}
?>
