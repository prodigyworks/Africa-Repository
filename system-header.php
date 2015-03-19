<?php
	require_once('system-db.php');
	
	if(!isset($_SESSION)) {
		session_start();
	}
	
	if (! isAuthenticated() && ! endsWith($_SERVER['PHP_SELF'], "/system-login.php")) {
		header("location: system-login.php?session=" . urlencode(base64_encode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] )));
		exit();
	}
	
	function showBreadCrumb() {
		BreadCrumbManager::showBreadcrumbTrail();
	}
?>
<?php 
	//Include database connection details
	require_once('system-config.php');
	require_once("confirmdialog.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Africa Transcriptions</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=8" />
<link rel="shortcut icon" href="favicon.ico">

<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/jquery-ui-1.10.3.custom.css" rel="stylesheet" type="text/css" />
<link href="css/dcmegamenu.css" rel="stylesheet" type="text/css" />
<link href="css/skins/white.css" rel="stylesheet" type="text/css" />

<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery-ui.min.js" type="text/javascript"></script>
<script src="js/jcarousellite.js" type="text/javascript"></script>
<script src='js/jquery.hoverIntent.minified.js' type='text/javascript'></script>
<script src='js/jquery.dcmegamenu.1.3.3.js' type='text/javascript'></script>
<script src="js/oraclelogs.js" language="javascript" ></script> 
<!--[if lt IE 7]>
<script type="text/javascript" src="js/ie_png.js"></script>
<script type="text/javascript">
	ie_png.fix('.png, .carousel-box .next img, .carousel-box .prev img');
</script>
<link href="css/ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->
</head>
<body id="page1">
<?php
	createConfirmDialog("passworddialog", "Forgot password ?", "forgotPassword");
	
	if (isset($_POST['command'])) {
		$_POST['command']();
	}
?>
<form method="post" id="commandForm" name="commandForm">
	<input type="hidden" id="command" name="command" />
	<input type="hidden" id="pk1" name="pk1" />
	<input type="hidden" id="pk2" name="pk2" />
	<input type="hidden" id="pk3" name="pk3" />
</form>
	<DIV id=maindiv1 class="tail-top-left"  align=center>
		<DIV id=maindiv2 style="top:0px; WIDTH: 1014px; text-align:left">
			<TABLE style="BORDER-COLLAPSE: collapse" cellSpacing=0 cellPadding=0 width=1014 align=left >
				<TR>
					<TD style="BACKGROUND: url(images/shadow_lft.png)" width=17>
						&nbsp;
					</TD>
					<TD>
						<div class="tail-top">
						<!-- header -->
						<?php 
							if (isAuthenticated()) {
						?>
							<div id="header" class='header1'>
								<?php		
									$qry = "UPDATE {$_SESSION['DB_PREFIX']}members SET " .
											"lastaccessdate = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
											"WHERE member_id = " . $_SESSION['SESS_MEMBER_ID'] . "";
									$result = mysql_query($qry);
									
									$qry = "UPDATE {$_SESSION['DB_PREFIX']}loginaudit SET " .
											"timeoff = NOW(), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
											"WHERE id = " . $_SESSION['SESS_LOGIN_AUDIT'] . "";
									$result = mysql_query($qry);
								?>
								<div id="toppanel">
									<label class="prefix">logged on: </label>
									<label>
									<a href='profile.php'>
										<?php echo $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME']; ?>
									</a>
									<span>
									&nbsp;|&nbsp;<a href='system-logout.php'>logout</a>
									</span> 
									</label>
								</div>
							</div>
						<?php		
							}
						?>
						<!-- content -->
							<div id="content">
								<div class="row-1">
									<div class="inside">
										<div class="container">
											<div class="menu2">
												<div>
													<?php
														if (isAuthenticated()) {
															showMenu();
														}
													?>
												</div>
											</div>
											<?php 
												if (isAuthenticated()) {
													
										    		if (isset($_GET['callee'])) {
														cache_function("showBreadCrumb", array("pageid" => $_SESSION['pageid'], "callee" => $_GET['callee']));
														
										    		} else {
														cache_function("showBreadCrumb", array("pageid" => $_SESSION['pageid']));
										    		}
												
													echo "<hr>\n";
												}
											?>
											<div class="content">
