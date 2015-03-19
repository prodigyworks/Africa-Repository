<?php
	$link = null;
	$db = null;
	
	require_once('system-db.php');
	
	class BreadCrumb {
	    // property declaration
	    public $page = "";
	    public $label = "";
	}
	
	class BreadCrumbManager {
		public static function initialise() {
			if (! isset($_SESSION['BREADCRUMBMANAGER'])) {
				$_SESSION['BREADCRUMBMANAGER'] = array();
			}
		}
		
		public static function add($pageName, $pageLabel) {
			$bc = new BreadCrumb();
			$bc->page = $pageName;
			$bc->label = $pageLabel;
			
			$_SESSION['BREADCRUMBMANAGER'][count($_SESSION['BREADCRUMBMANAGER'])] = $bc;
		}
		
		public static function remove($index) {
			unset($_SESSION['BREADCRUMBMANAGER'][$index]);
		}
		
		public static function showBreadcrumbTrail() {
			unset($_SESSION['BREADCRUMBMANAGER']);
			
			self::initialise();
    		self::add($_SESSION['pagename'], $_SESSION['title']);
    		
    		if (isset($_GET['callee'])) {
				self::fetchAccessedParent();
				
    		} else {
				self::fetchParent($_SESSION['pageid']);
    		}
	    	
			$first = true;
			
			echo "<h4 class='breadcrumb'>";
			
			for ($i = count($_SESSION['BREADCRUMBMANAGER']) - 1; $i >= 0; $i--) {
				if (! $first) {
					echo "<span class='divider'>&nbsp;-----&gt;</span>";
				}
				
				$first = false;
				
				
				if ($i == 0) {
					echo "<a href='javascript: void(0)' class='lastchild'";
					
				} else {
					echo "<a href='" .$_SESSION['BREADCRUMBMANAGER'][$i]->page . "' ";
				}
				
				echo ">" . $_SESSION['BREADCRUMBMANAGER'][$i]->label . "</a>";
			} 
			
			echo "</h4>";
		}
		
		public static function fetchAccessedParent() {
			$qry = "SELECT A.pageid, A.pagename, A.label FROM {$_SESSION['DB_PREFIX']}pages A " .
					"WHERE A.pagename = '" . base64_decode($_GET['callee']) . "'";
			$result = mysql_query($qry);
			
			//Check whether the query was successful or not
			if ($result) {
				if (mysql_num_rows($result) == 1) {
					$member = mysql_fetch_assoc($result);
					
					self::add($member['pagename'], $member['label']);
					self::fetchParent($member['pageid']);
				}
			}
		}
		
		public static function fetchParent($id) {
			$qry = "SELECT A.pageid, B.pagename, B.label FROM {$_SESSION['DB_PREFIX']}pagenavigation A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}pages B " .
					"ON B.pageid = A.pageid " .
					"WHERE A.childpageid = $id";
			$result = mysql_query($qry);
			
			//Check whether the query was successful or not
			if ($result) {
				if (mysql_num_rows($result) == 1) {
					$member = mysql_fetch_assoc($result);
					
					if ($id != $member['pageid']) {
						self::add($member['pagename'], $member['label']);
						self::fetchParent($member['pageid']);
					}
					
				} else if (mysql_num_rows($result) == 0) {
					if ($id > 1) { /* Not a home connection */
						self::add("index.php", "Home");
					}
				}
			}
		}
		
		public static function calculate() {
	    	if (isAuthenticated()) {
		    	if (isset($_SESSION['lastconnectiontime'])) {
		    		$lastsessiontime = time() - $_SESSION['lastconnectiontime'];
		    		
		    		/* 30 minutes. */
		    		if (DEV_ENV != "true") {
			    		if ($lastsessiontime >= 36000) {	//Unset the variables stored in session
							unset($_SESSION['SESS_MEMBER_ID']);
							unset($_SESSION['SESS_FIRST_NAME']);
							unset($_SESSION['SESS_LAST_NAME']);
							unset($_SESSION['ROLES']);
							unset($_SESSION['MENU_CACHE']);
	//						unset($_SESSION['ERRMSG_ARR']);
	
							$_SESSION['ROLES'] = array();
							$_SESSION['ROLES'][0] = "PUBLIC";
							$_SESSION['ROLES'][1] = "UNAUTHENTICATED";
		
			    			header("location: system-login.php?session=" . urlencode(base64_encode("index.php")));
			    		}
		    		}
		    	}
	    	}
	    	
	   		$_SESSION['lastconnectiontime'] = time();
	    }
	}
	
	class SessionManagerClass {
		public static function initialise() {
			//Start session
			start_db();
		    
		    $_SESSION['pagename'] = substr($_SERVER["PHP_SELF"], strripos($_SERVER["PHP_SELF"], "/") + 1);
		    
		    if ($_SESSION['pagename'] == "dynamicpage.php") {
		    	$_SESSION['pagename'] .= "?page=" . $_GET['page'];
		    }
		    
		    BreadCrumbManager::initialise();
		    
		    self::initialiseDB();
			self::initialisePageData();

			BreadCrumbManager::calculate();
		}
		
	    public static function initialiseDB() {
	    	initialise_db();
		
			if (! isset($_SESSION['ROLES'])) {
				$_SESSION['ROLES'] = array();
				$_SESSION['ROLES'][0] = "PUBLIC";
				$_SESSION['ROLES'][1] = "UNAUTHENTICATED";
			}
	    }
	    
        public static function initialisePageData() {
			$qry = "SELECT DISTINCT A.* FROM {$_SESSION['DB_PREFIX']}pages A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}pageroles B " .
					"ON B.pageid = A.pageid " .
					"WHERE A.pagename = '" . $_SESSION['pagename'] . "' " .
					"AND B.roleid IN (" . ArrayToInClause($_SESSION['ROLES']) . ")";
			$result = mysql_query($qry);

			//Check whether the query was successful or not
			if ($result) {
				if (mysql_num_rows($result) == 1) {
					$member = mysql_fetch_assoc($result);
					
					$_SESSION['pageid'] = $member['pageid'];
					$_SESSION['title'] = $member['label'];
					
				} else {
//					logError("Cannot access page " . $_SESSION['pagename'] . " ($qry)", false);
					header("location: system-access-denied.php");
				}
					
			} else {
//				logError("Cannot access page " . $_SESSION['pagename'] . " ($qry / "  . mysql_error() . ")", false);
				header("location: system-access-denied.php");
			}
	    }
	    
	}

    SessionManagerClass::initialise();
	
	function showErrors() {
		if( isset($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR']) && count($_SESSION['ERRMSG_ARR']) >0 ) {
			echo '<ul class="err">';
			foreach($_SESSION['ERRMSG_ARR'] as $msg) {
				echo '<li>',$msg,'</li>'; 
			}
			echo '</ul>';
			unset($_SESSION['ERRMSG_ARR']);
		}
	}
    
    function showSubMenu($id) {
    	$menuHTML = "";
		$qry = "SELECT DISTINCT B.pagename, B.label, A.target, A.title FROM {$_SESSION['DB_PREFIX']}pagenavigation A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}pages B " .
				"ON A.childpageid = B.pageid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}pageroles C " .
				"ON C.pageid = B.pageid " .
				"WHERE A.pageid = " . $id . " " .
				"AND A.pagetype ='M' " .
				"AND C.roleid IN (" . ArrayToInClause($_SESSION['ROLES']) . ") " .
				"ORDER BY A.sequence";
		$result=mysql_query($qry);

		//Check whether the query was successful or not
		if($result) {
			
			if (mysql_num_rows($result) >  0) {
				$titleUsed = false;
				
				$menuHTML = $menuHTML .  "<ul>\n";
		
				/* Show children. */
				while (($member = mysql_fetch_assoc($result))) {
					if ($member['title'] != null) {
						if ($titleUsed) {
							$menuHTML = $menuHTML .  "</ul></li>\n";
						}
						
						$titleUsed = true;
						$menuHTML = $menuHTML .  "<li><a href='#'>" . $member['title'] . "</a><ul>";						
					}
					
					if ($member['pagename'] == $_SESSION['pagename']) {
						$menuHTML = $menuHTML .  "<li class='active submenuitem'>" ;
						
					} else {
						$menuHTML = $menuHTML .  "<li class='submenuitem'>";
					}
					
					$target = "";
					
					if ($member['target'] != null) {
						$target = " target='" . $member['target'] . "' ";
					}
					
					$menuHTML = $menuHTML .  "<a $target href='" . $member['pagename'] . "'>" . $member['label'] . "</a></li>\n";
				}
				
				if ($titleUsed) {
					$menuHTML = $menuHTML .  "</ul></li>\n";
				}
		
				$menuHTML = $menuHTML .  "</ul>\n";
			}
		}
		
		return $menuHTML;
    }

    function findParentMenu($id, $ancestors) {
		$qry = "SELECT pageid, pagetype " .
				"FROM {$_SESSION['DB_PREFIX']}pagenavigation " .
				"WHERE childpageid = $id";
		$result=mysql_query($qry);

		//Check whether the query was successful or not
		if($result) {
			
			if (mysql_num_rows($result) > 0) {
				$member = mysql_fetch_assoc($result);
				$ancestors[count($ancestors)] = $member['pageid'];
				
				if ($member['pagetype'] == "M" ||
					$member['pagetype'] == "L") {
					$ancestors = findParentMenu($member['pageid'], $ancestors);
				}
				
			} else {
				$ancestors[count($ancestors)] = 1;
			}
		}
		
		return $ancestors;
    }
    
    function showMenu() {
    	$menuHTML = "";
    	
    	if (isset($_SESSION['MENU_CACHE'])) {
    		$menuHTML = $_SESSION['MENU_CACHE'];
   
    	} else {
	    	$menuHTML = nestPages(1, array(1));
    		$_SESSION['MENU_CACHE'] = $menuHTML;
    	}
    	
    	echo $menuHTML;
    }
    
    function nestPages($id, $ancestors) {
    	$menuHTML = "";
		$qry = "SELECT DISTINCT A.*, B.* FROM {$_SESSION['DB_PREFIX']}pagenavigation A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}pages B " .
				"ON A.childpageid = B.pageid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}pageroles C " .
				"ON C.pageid = B.pageid " .
				"WHERE A.pageid = " . $id . " " .
				"AND A.pagetype = 'P' " .
				"AND C.roleid IN (" . ArrayToInClause($_SESSION['ROLES']) . ") " .
				"ORDER BY A.sequence";
		$result=mysql_query($qry);
		
		//Check whether the query was successful or not
		if($result) {
			
			if (mysql_num_rows($result) == 0) {
				if (isAuthenticated()) {
					$ancestors = findParentMenu($id, $ancestors);
					
					$menuHTML = $menuHTML . nestPages($ancestors[count($ancestors) - 1], $ancestors);
				}
				
			} else {
				$result=mysql_query($qry);
				$highestPage = 0;

				while (($member = mysql_fetch_assoc($result))) {
					
					for ($index = 0; $index < count($ancestors); $index++) {
						if ($ancestors[$index] == $member['pageid']) {
							
							if ($highestPage < $member['pageid']) {
								$highestPage = $member['pageid'];
							}
						}
					}
				}
		
				$result=mysql_query($qry);
				$first = true;
				$counter = 0;
				
				$menuHTML = $menuHTML . "<div class='red'><ul class='mega-menu'>\n";
		
				/* Show children. */
				while (($member = mysql_fetch_assoc($result))) {
					$anchorClass = "" ;
					
					$menuHTML = $menuHTML . "<li class='";
					
					$counter++;
					
					if ($counter == 6) {
						$anchorClass = "last " ;
					}
					
					if ($first) {
						$first = false;
						$anchorClass = $anchorClass . "first " ;
					}
					
					if ($highestPage == $member['pageid']) {
						$menuHTML = $menuHTML .  "current " ;
					}
					
					$menuHTML = $menuHTML .  "' onclick='window.location.href = \"" . $member['pagename'] . "\"'>";
					$target = "";
					
					if ($member['target'] != null) {
						$target = " target='" . $member['target'] . "' ";
					}

					$menuHTML = $menuHTML .  "<a $target class='$anchorClass' href='" . $member['pagename'] . "'><em><b>" . $member['label'] . "</b></em></a>\n";
				    $menuHTML = $menuHTML . showSubMenu($member['childpageid']);

					$menuHTML = $menuHTML .  "</li>\n";
					
					if ($member['divider'] == 1) {
						$menuHTML = $menuHTML .  "<div class='divider'>&nbsp;</div>\n";
					}
				}
		
				$menuHTML = $menuHTML .  "</ul></div>\n";
			}
		}
		
		return $menuHTML;
    }
	
	function createDocumentLink() {
		?>
		<div class='modal documentmodal' id='documentDialog'>
		<iframe width=100% height=100% src='' frameborder='0' scrolling='no' src='' ></iframe>
		</div>
		<script>
		$(document).ready(function() {
			$('#documentDialog').dialog({
					autoOpen: false,
					modal: true,
					width: 750,
					height: 600,
					title: 'Documents',
					show:'fade',
					hide:'fade',
					dialogClass: 'document-dialog',
					buttons: {
						'Back': function() {
							$(this).dialog('close');
							try { resetTimer(); } catch (e) {}
						}
					}
				});
			});
				
			function viewDocument(headerid, callback, id, table, key) {
				var parameters = "";
				
				try {resetRefresh(); } catch(e) {}
				
				if (callback) {
					parameters = "&documentcallback=" + callback + "&identifier=" + id ;
				}
				
				if (table) {
					parameters += "&table=" + table + "&key=" + key;
					
				} else {
					parameters += "&table=casedocs&key=caseid";
				}
				
				$('iframe').attr('src', 'documents.php?sessionid=<?php echo session_id(); ?>&id=' + headerid + parameters);
				$('#documentDialog').dialog('open');
			}
			
			function viewSessionDocument(sessionid, callback, id) {
				try {resetRefresh(); } catch(e) {}
				var parameters = "";
				
				if (callback) {
					parameters = "&documentcallback=" + callback + "&identifier=" + id;
				}
				
				$('iframe').attr('src', 'documents.php?sessionid=' + sessionid + parameters);
				$('#documentDialog').dialog('open');
			}
		</script>
		<?php
	}
	
	function showMessages() {
		?>
		<table class="grid list" maxrows=0 width='100%'>
			<thead>
				<tr>
					<td>From</td>
					<td>Message</td>
					<td>Sent</td>
					<td>Action</td>
				</tr>
			</thead>
		<?php 
			$memberid = $_SESSION['SESS_MEMBER_ID'];
			$qry = "SELECT A.id, A.from_member_id AS fromid, A.message, A.createddate, B.firstname, B.lastname " .
					"FROM {$_SESSION['DB_PREFIX']}messages A " .
					"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
					"ON B.member_id = A.from_member_id " .
					"WHERE A.to_member_id = $memberid ";
			$result = mysql_query($qry);
		
			//Check whether the query was successful or not
			if($result) {
				while (($member = mysql_fetch_assoc($result))) {
					echo "<tr>\n";
					echo "<td><a href='profile?id=" . $member['fromid'] . "'>" . $member['firstname'] . " " . $member['lastname'] . "</a></td>\n";
					echo "<td>" . $member['message'] . "</td>\n";
					echo "<td>" . $member['createddate'] . "</td>\n";
					echo "<td><img src='images/delete.png' /></td>\n";
					echo "</tr>\n";
				}
			}
		?>
		</table>
		<?php
	}
	
	function showAdvert($groupid, $width, $height) {
		$qry = "SELECT A.url, A.imageid " .
				"FROM {$_SESSION['DB_PREFIX']}advert A " .
				"WHERE A.published = 'Y' " .
				"AND A.groupid = $groupid " .
				"AND A.publisheddate <= NOW() " .
				"AND A.expirydate >= NOW() ";
		$result = mysql_query($qry);
		$found = false;
		
		//Check whether the query was successful or not
		if ($result) {
			$rows = mysql_num_rows($result);
			$chosenrow = rand(1, $rows);
			$index = 1;
			
			while (($member = mysql_fetch_assoc($result))) {
				if ($index++ == $chosenrow) {
					echo "<a href='" . $member['url'] . "'><img src='system-imageviewer.php?id=" . $member['imageid'] . "' width=$width height=$height /></a>";
					$found = true;
					break;
				}
			}
		}
		
		if (! $found) {
			echo "<img src='images/advertisehere.png' width=$width height=$height />";
		}
	
	}
		

		
?>
