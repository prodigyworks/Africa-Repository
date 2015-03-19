<?php 
	require_once("system-header.php"); 
	require_once("confirmdialog.php");
	
	createConfirmDialog("loginDialog", "Forgot password ?", "forgotPassword");
?>
<style>
	.content, .footer {
		display: none;
	}
	html {
		background-color: #f4f4f4;
		overflow: hidden;
	}
	.loginerror {
		position: absolute;
		margin-left: 200px;
		margin-top: -120px;
		color: red;
		z-index:9999922;
		font-style: italic;
	}
</style>
<!--  Start of content -->
<p align="center">&nbsp;</p>
<img src="images/login-page.png" />
		<?php
			if (! isAuthenticated()) {
		?>
		<div class="modal" id="dialog">
		<?php
			if (isset($_SESSION['ERRMSG_ARR'])) {
				echo "<div class='loginerror'>\n";
				echo "<img src='images/alert.png' />";
				
				for ($i = 0; $i < count($_SESSION['ERRMSG_ARR']); $i++) {
					echo "<p>" . $_SESSION['ERRMSG_ARR'][$i] . "</p>";
				}

				echo "</div>";
			}
		?>
			<form action="system-login-exec.php?session=<?php echo urlencode($_GET['session']); ?>" method="post" id="loginForm">
				<div><label>User name</label></div>
				<input type="text" name="login" id="login" value="<?php if (isset($_SESSION['ERR_USER'])) echo $_SESSION['ERR_USER']; ?>"/>
				<br/>
				<br/>
				<input type="hidden" id="callback" name="callback" value="<?php if (isset($_GET['session'])) echo base64_decode( urldecode( $_GET['session'])); else echo "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']; ?>" />
				<div><label>password</label></div>
				<input type="password" name="password" id="password" value="" />
				<br />
				<br>
				<a href="javascript:void(0)" onclick="checkForgotPassword()">Forgotten password ?</a>
				<br>
				<div id="logindialoglogo" onclick="$('#loginForm').submit()"></div>
				<img src="images/loginlogo.png" style="float:left"  />
			</form>
			<script>
				document.onkeypress = changeHREF;
				function changeHREF(ev) {
					ev = ev || event;
					
					if (ev.keyCode == 13) {
						$('#loginForm').submit();
					}
				}

			</script>
		</div>
		
		<script>
			function checkForgotPassword() {
				if ($("#login").val() != "") {
					$("#loginDialog .loginDialogbody").html("You are about to reset the password.<br>Are you sure ?");
					$("#loginDialog").dialog("open");
				}
			}
			
			function forgotPassword() {
				$("#loginForm").attr("action", "forgotpassword.php?session=<?php echo urlencode($_GET['session']); ?>");	
				$("#loginForm").submit();	
			}
			
			$(document).ready(function() {
					$("#login").change(
							function() {
								$(".loginerror").hide();
							}
						);
						
					$("#dialog").dialog({
							modal: true,
							width: 480,
							closeOnEscape: false,
							dialogClass: 'login-dialog',
							beforeClose: function() { return false; }
						});
				});
			
		</script>
				
		<?php
			}
			
			unset($_SESSION['ERRMSG_ARR']);
			unset($_SESSION['ERR_USER']);
		?>
<!--  End of content -->

<?php include("system-footer.php"); ?>					
