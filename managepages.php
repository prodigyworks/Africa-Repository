<?php
	require_once("crud.php");
	
	function sequenceUp() {
		$id = $_POST['navigationid'];
		$pageid = $_POST['navigationpageid'];
		$maxsequenceid = -1;
		$sequence = 0;
		$nextsequence = 0;
		
		$qry = "SELECT sequence " .
				"FROM {$_SESSION['DB_PREFIX']}pagenavigation A " .
				"WHERE A.pageid = $pageid " .
				"AND A.childpageid = $id ";
		$result = mysql_query($qry);

		//Check whether the query was successful or not
		if ($result) {
			if (mysql_num_rows($result) == 1) {
				$member = mysql_fetch_assoc($result);
				
				$sequence = $member['sequence'];
			}
			
		} else {
			logError(mysql_error());
		}
		
		$qry = "SELECT pagenavigationid, sequence " .
				"FROM {$_SESSION['DB_PREFIX']}pagenavigation A " .
				"WHERE A.pageid = $pageid AND " .
				"A.sequence < $sequence " .
				"ORDER BY A.sequence DESC ";
		$result = mysql_query($qry);

		//Check whether the query was successful or not
		if ($result) {
			if (mysql_num_rows($result) >= 1) {
				$member = mysql_fetch_assoc($result);
				
				$maxsequenceid = $member['pagenavigationid'];
				$nextsequence = $member['sequence'];
			}
			
		} else {
			logError(mysql_error());
		}
		
		if ($maxsequenceid != -1) {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}pagenavigation " .
					"SET sequence = '$nextsequence', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE pageid = $pageid " .
					"AND childpageid = $id ";

			$result = mysql_query($qry);
			
			if (! $result) {
				logError(mysql_error());
			}
			
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}pagenavigation " .
					"SET sequence = '$sequence', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE pagenavigationid = $maxsequenceid";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError(mysql_error());
			}
		}
		
		unset($_SESSION['MENU_CACHE']);
	}
	
	function sequenceDown() {		$id = $_POST['navigationid'];
		$pageid = $_POST['navigationpageid'];
		$maxsequenceid = -1;
		$sequence = 0;
		$nextsequence = 0;
		
		$qry = "SELECT sequence " .
				"FROM {$_SESSION['DB_PREFIX']}pagenavigation A " .
				"WHERE A.pageid = $pageid " .
				"AND A.childpageid = $id ";
		$result = mysql_query($qry);

		//Check whether the query was successful or not
		if ($result) {
			if (mysql_num_rows($result) == 1) {
				$member = mysql_fetch_assoc($result);
				
				$sequence = $member['sequence'];
			}
			
		} else {
			logError(mysql_error());
		}
		
		$qry = "SELECT pagenavigationid, sequence " .
				"FROM {$_SESSION['DB_PREFIX']}pagenavigation A " .
				"WHERE A.pageid = $pageid AND " .
				"A.sequence > $sequence " .
				"ORDER BY A.sequence ";
		$result = mysql_query($qry);

		//Check whether the query was successful or not
		if ($result) {
			if (mysql_num_rows($result) >= 1) {
				$member = mysql_fetch_assoc($result);
				
				$maxsequenceid = $member['pagenavigationid'];
				$nextsequence = $member['sequence'];
			}
			
		} else {
			logError(mysql_error());
		}
		
		if ($maxsequenceid != -1) {
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}pagenavigation " .
					"SET sequence = '$nextsequence', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE pageid = $pageid " .
					"AND childpageid = $id ";

			$result = mysql_query($qry);
			
			if (! $result) {
				logError(mysql_error());
			}
			
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}pagenavigation " .
					"SET sequence = '$sequence', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
					"WHERE pagenavigationid = $maxsequenceid";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError(mysql_error());
			}
		}
		
		unset($_SESSION['MENU_CACHE']);
	}
	
	function saveContent() {
		$pageid = $_POST['contentpageid'];
		$contentvalue = mysql_escape_string($_POST['contentvalue']);
		
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}pages SET content = '$contentvalue', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " WHERE pageid = $pageid";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError(mysql_error());
		}
	}
	
	class PageEdit extends Crud {
		/* Post insert event. */
		public function postInsertEvent() {
			$pageid = mysql_insert_id();
			$parentpageid = 1;
			
			if (isset($_GET['id'])) {
				$parentpageid = $_GET['id'];
			}
			
			$maxSequence = 0;
			
			$qry = "SELECT MAX(sequence) AS maxseq " .
					"FROM {$_SESSION['DB_PREFIX']}pagenavigation A " .
					"WHERE A.pageid = $parentpageid";
			$result = mysql_query($qry);

			//Check whether the query was successful or not
			if ($result) {
				if (mysql_num_rows($result) == 1) {
					$member = mysql_fetch_assoc($result);
					
					$maxSequence = $member['maxseq'];
				}
			}
			
			$maxSequence += 100;
			
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}pageroles (pageid, roleid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUE($pageid, 'PUBLIC', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
			
			if ($_POST['menuitem'] == "Y") {
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}pagenavigation (pageid, childpageid, sequence, pagetype, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUE($parentpageid, $pageid, $maxSequence, 'M', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
				
			} else {
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}pagenavigation (pageid, childpageid, sequence, pagetype, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUE($parentpageid, $pageid, $maxSequence, 'L', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
			}
			
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
		}
		
		/* Pre command event. */
		public function preCommandEvent() {
			if (isset($_POST['rolecmd'])) {
				if (isset($_POST['roles'])) {
					$counter = count($_POST['roles']);
		
				} else {
					$counter = 0;
				}
				
				$pageid = $_POST['pageid'];
				$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}pageroles WHERE pageid = $pageid";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError(mysql_error());
				}
		
				for ($i = 0; $i < $counter; $i++) {
					$roleid = $_POST['roles'][$i];
					
					$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}pageroles (pageid, roleid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) VALUES ($pageid, '$roleid', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
					$result = mysql_query($qry);
				};
			}
		}

		/* Post header event. */
		public function postHeaderEvent() {
?>
			<script src='js/jquery.picklists.js' type='text/javascript'></script>
			
			<div id="contentDialog" class="modal">
				<label>NOTES</label><br>
				<textarea id="editemailnotes" name="editemailnotes" class="tinyMCE" cols="180" rows=17 style="height: 400px"></textarea>
			</div>
			
			<div id="roleDialog" class="modal">
				<form id="rolesForm" name="rolesForm" method="post">
					<input type="hidden" id="pageid" name="pageid" />
					<input type="hidden" id="rolecmd" name="rolecmd" value="X" />
					<select class="listpicker" name="roles[]" multiple="true" id="roles" >
						<?php createComboOptions("roleid", "roleid", "{$_SESSION['DB_PREFIX']}roles", "", false); ?>
					</select>
				</form>
			</div>
<?php
		}
		
		/* Post script event. */
		public function postScriptEvent() {
?>
			var currentRole = null;
			var currentPageID = null;
			
			function sequenceUp(id) {
				var pageid = <?php
					if (isset($_GET['id'])) {
						echo $_GET['id'];
						
					} else {
						echo "1";
					}
				?>;
				
				post("editform", "sequenceUp", "submitframe", { navigationid: id, navigationpageid: pageid});
			}
			
			function sequenceDown(id) {
				var pageid = <?php
					if (isset($_GET['id'])) {
						echo $_GET['id'];
						
					} else {
						echo "1";
					}
				?>;
				
				post("editform", "sequenceDown", "submitframe", { navigationid: id, navigationpageid: pageid});
			}
			
			
			function typeChange() {
				if ($("#type").val() == "D") {/* Dynamic */
					if ($("#pagename").val().substring(0, 21) != "dynamicpage.php?page=") {
						$("#pagename").val("dynamicpage.php?page=" + $("#pagename").val());
					}

				} else {
					if ($("#pagename").val().length > 21) {
						if ($("#pagename").val().substring(0, 21) == "dynamicpage.php?page=") {
							$("#pagename").val($("#pagename").val().substring(21, $("#pagename").val().length));
						}
					}
				}
			}
			
			$(document).ready(function() {
					$("#roles").pickList({
							removeText: 'Remove Role',
							addText: 'Add Role',
							testMode: false
						});
					
					$("#roleDialog").dialog({
							autoOpen: false,
							modal: true,
							width: 800,
							title: "Roles",
							buttons: {
								Ok: function() {
									$("#rolesForm").submit();
								},
								Cancel: function() {
									$(this).dialog("close");
								}
							}
						});
					
					$("#contentDialog").dialog({
							autoOpen: false,
							modal: true,
							width: 800,
							title: "Content",
							buttons: {
								Ok: function() {
									tinyMCE.triggerSave();
									
									$(this).dialog("close");
									
									post(
											"editform", 
											"saveContent", 
											"submitframe", 
											{ 
												contentpageid: currentPageID, 
												contentvalue: tinyMCE.get('editemailnotes').getContent() 
											}
										);
								},
								Cancel: function() {
									$(this).dialog("close");
								}
							}
						});
				});
				
			function editContent(pageid) {
				currentPageID = pageid;
				
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT content FROM <?php echo $_SESSION['DB_PREFIX'];?>pages WHERE pageid = " + pageid
						},
						function(data) {
							tinyMCE.get("editemailnotes").setContent(data[0].content);
						}
					);
				
				$("#contentDialog").dialog("open");
			}
				
			function pageRoles(pageid) {
				getJSONData('findpageroles.php?pageid=' + pageid, "#roles", function() {
					$("#pageid").val(pageid);
					$("#roleDialog").dialog("open");
				});
			}
<?php
		}
	}
	
	$crud = new PageEdit();
	$crud->title = "Pages";
	$crud->table = "{$_SESSION['DB_PREFIX']}pages";
	$crud->dialogwidth = 600;
	
	if (isset($_GET['id'])) {
		$crud->sql = 
				"SELECT A.*, B.pagetype " .
				"FROM {$_SESSION['DB_PREFIX']}pages A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}pagenavigation B " .
				"ON B.childpageid = A.pageid " .
				"AND B.pagetype != 'P' " .
				"WHERE B.pageid = " . $_GET['id'] . " " .
				"ORDER BY B.sequence"; 
		
	} else {
		$crud->sql = 
				"SELECT A.*, B.pagetype " .
				"FROM {$_SESSION['DB_PREFIX']}pages A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}pagenavigation B " .
				"ON B.childpageid = A.pageid " .
				"AND B.pagetype = 'P' " .
				"WHERE B.pageid = 1 " . 
				"ORDER BY B.sequence"; 
	}
	
	$crud->messages = array(
			array('id'		  => 'contentvalue'),
			array('id'		  => 'contentpageid'),
			array('id'		  => 'navigationid'),
			array('id'		  => 'navigationpageid')
		);
	$crud->subapplications = array(
			array(
				'title'		  => 'Nav',
				'imageurl'	  => 'images/minimize.gif',
				'application' => 'managepages.php'
			),
			array(
				'title'		  => 'Roles',
				'imageurl'	  => 'images/user.png',
				'script' 	  => 'pageRoles'
			),
			array(
				'title'		  => 'Actions',
				'imageurl'	  => 'images/action.png',
				'application' => 'manageactions.php'
			),
			array(
				'title'		  => 'Content',
				'imageurl'	  => 'images/article.png',
				'script'      => 'editContent'
			),
			array(
				'title'		  => 'Move Up',
				'imageurl'	  => 'images/up.png',
				'script'      => 'sequenceUp'
			),
			array(
				'title'		  => 'Move Down',
				'imageurl'	  => 'images/down.png',
				'script'      => 'sequenceDown'
			)
		);
	$crud->columns = array(
			array(
				'name'       => 'pageid',
				'length' 	 => 6,
				'pk'		 => true,
				'showInView' => false,
				'editable'	 => false,
				'bind' 	 	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'label',
				'length' 	 => 60,
				'sortby'	 => true,
				'label' 	 => 'Application'
			),
			array(
				'name'       => 'pagename',
				'length' 	 => 50,
				'label' 	 => 'Page Name'
			),
			array(
				'name'       => 'type',
				'length' 	 => 20,
				'label' 	 => 'Page Type',
				'onchange'	 => 'typeChange',
				'type'       => 'COMBO',
				'options'    => array(
						array(
							'value'		=> 'P',
							'text'		=> 'PHP'
						),
						array(
							'value'		=> 'C',
							'text'		=> 'Content Managed'
						),
						array(
							'value'		=> 'D',
							'text'		=> 'Dynamic'
						)
					)
			),
			array(
				'name'       => 'menuitem',
				'length' 	 => 20,
				'bind'		 => false,
				'showInView' => false,
				'label' 	 => 'Add to menu',
				'type'       => 'COMBO',
				'options'    => array(
						array(
							'value'		=> 'Y',
							'text'		=> 'Yes'
						),
						array(
							'value'		=> 'N',
							'text'		=> 'No'
						)
					)
			)
		);
		
	$crud->run();
?>
