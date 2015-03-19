<?php include("system-header.php"); ?>
<script src="js/jquery.multiselect.filter.min.js" type="text/javascript"></script>
<script src="js/jquery.multiselect.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.multiselect.css" />
<link rel="stylesheet" type="text/css" href="css/jquery.multiselect.filter.css" />
<script>
$(document).ready(
		function() {
		   	$(".multiselect").multiselect({
		   			multiple: true
			   }); 
		}
	);

function accounttype_onchange() {
	if ($("#accounttype").val() == "COURTUSER") {
		$("#courtrow").show();
		$("#provincerow").hide();
		
	} else if ($("#accounttype").val() == "OFFICE") {
		$("#provincerow").show();
		$("#courtrow").hide();
		
	} else {
		$("#courtrow").hide();
		$("#provincerow").hide();
	}
}
</script>
<!--  Start of content -->
<?php
if( isset($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR']) && count($_SESSION['ERRMSG_ARR']) >0 ) {
?>

<div id="errorwindow">
	<?php showErrors(); ?>
</div>
<?php
}
?>
<div class="registerPage">
	<h4>Staff Registry</h4>
	<form id="loginForm" enctype="multipart/form-data" name="loginForm" class="entryform manualform" method="post" action="system-register-exec.php">
	  <table border="0" align="left">
	    <tr>
	      <td>First Name </td>
	      <td><input required="true" name="fname" type="text" class="textfield" id="fname" /></td>
	    </tr>
	    <tr>
	      <td>Last Name </td>
	      <td><input required="true" name="lname" type="text" class="textfield" id="lname" /></td>
	    </tr>
	    <tr>
	      <td>Login</td>
	      <td><input required="true" name="login" type="text" class="textfield logintext" id="login" /></td>
	    </tr>
	    <tr>
	      <td>Email</td>
	      <td><input required="true" name="email" type="text" class="textfield60" id="email" /></td>
	    </tr>
	    <tr>
	      <td>Confirm Email</td>
	      <td><input required="true" name="confirmemail" type="text" class="textfield60" id="confirmemail" /></td>
	    </tr>
	    <tr>
	      <td>Image</td>
	      <td><input name="image" type="file" class="textfield60" id="image" /></td>
	    </tr>
	    <tr>
	    	<td>Office</td>
	    	<td>
	    	<?php createCombo("officeid", "id", "name", "{$_SESSION['DB_PREFIX']}offices", "", false); ?>
	    	</td>
	    </tr>
	    <tr>
	    	<td>User Type</td>
	    	<td>
	    		<SELECT id="accounttype" name="accounttype" onchange="accounttype_onchange()">
	    			<OPTION value="TYPIST">Typist</OPTION>
	    			<OPTION value="OFFICE">Office User</OPTION>
	    			<OPTION value="COURTUSER">Court User</OPTION>
	    			<OPTION value="ADMIN">Admin User</OPTION>
    			</SELECT>
	    	</td>
	    </tr>
	    <tr id="provincerow" style="display:none">
	    	<td>Province</td>
	    	<td>
	    	<?php createCombo("provinceid", "id", "name", "{$_SESSION['DB_PREFIX']}province", "", false); ?>
	    	</td>
	    </tr>
	    <tr id="courtrow" style="display:none">
	    	<td>Courts</td>
	    	<td>
	    	<?php createCombo("courtid", "id", "name", "{$_SESSION['DB_PREFIX']}allcourts", "WHERE vatapplicable = 'N'", false, true, array("class" => "multiselect", "size" => "1", "multiple" => "true"), false); ?>
	    	</td>
	    </tr>
	    <tr>
	    	<td colspan="2">
	    		<br />
	    		<h4>Security</h4>
	    		<hr />
	    	</td>
	    </tr>
	    <tr>
	      <td>Password</td>
	      <td>
	      	<input required="true" name="password" type="password" class="textfield pwd" id="password" />
	      </td>
	    </tr>
	    <tr>
	      <td>Confirm Password </td>
	      <td><input required="true" name="cpassword" type="password" class="textfield" id="cpassword" /></td>
	    </tr>
	    <tr>
	      <td>&nbsp;</td>
	      <td>
	  		<span class="wrapper"><a class='link1' href="javascript:if (verify()) $('#loginForm').submit();"><em><b>Submit</b></em></a></span>
	      </td>
	    </tr>
	  </table>
	  <input type="hidden" id="description" name="description" value="Profile image" />
	</form>
</div>
<script>
	$(document).ready(function() {
		$(".pwd").blur(verifypassword);
		$(".logintext").blur(checkLogin);
		$("#email").blur(checkEmail);
		$("#cpassword").blur(verifycpassword);
		$("#confirmemail").blur(verifycemail);
		$("#fname").focus();
	});
				
	function verify() {
		var isValid = verifyStandardForm('#loginForm');
		
		if (! verifypassword()) {
			isValid = false;
		}
		
		if (! verifycpassword()) {
			isValid = false;
		}
		
		if (! checkLogin()) {
			isValid = false;
		}
		
		if (! checkEmail()) {
			isValid = false;
		}
		
		if (! verifycemail()) {
			isValid = false;
		}
		
		return isValid;
	}
	
	function verifypassword() {
		var node = $(".pwd");
		var str = $(node).val();
		
		return true;
	}
	
	function verifycpassword() {
		var node = $("#cpassword");
		var str = $(node).val();
		
		if ($(node).val() == "") {
			return false;
		}
		
		if( str == $(".pwd").val()) {
			$(node).removeClass("invalid");
			$(node).next().css("visibility", "hidden");
			$(node).next().attr("title", "Required field.");
			
			return true;
			
		} else {
			$(node).addClass("invalid");
			$(node).next().css("visibility", "visible");
			$(node).next().attr("title", "Passwords do not match.");
			
			return false;
		}
	}
	
	function checkLogin() {
		var node = $(".logintext");
		var returnValue = true;
		
		if ($(node).val() == "") {
			return false;
		}
		
		callAjax(
				"finduser.php", 
				{ 
					login: $(".logintext").val()
				},
				function(data) {
					if (data.length > 1) {
						$(node).addClass("invalid");
						$(node).next().css("visibility", "visible");
						$(node).next().attr("title", "Login is already in use.");
						
						returnValue = false;
						
					} else {
						$(node).removeClass("invalid");
						$(node).next().css("visibility", "hidden");
						$(node).next().attr("title", "Required field.");
					}
				},
				false
			);
			
		return returnValue;
	}
	
	function checkEmail() {
		var node = $("#email");
		var returnValue = true;
		
		if ($(node).val() == "") {
			return false;
		}
		
		callAjax(
				"findemail.php", 
				{ 
					email: $("#email").val()
				},
				function(data) {
					if (data.length > 1) {
						$(node).addClass("invalid");
						$(node).next().css("visibility", "visible");
						$(node).next().attr("title", "Email address is already in use by user " + data[0].login + "(" +  data[0].firstname  + " " + data[o].lastname + ").");
						
						returnValue = false;
						
					} else {
						$(node).removeClass("invalid");
						$(node).next().css("visibility", "hidden");
						$(node).next().attr("title", "Required field.");
					}
				},
				false
			);
			
		return returnValue;
	}
	
	function verifycemail() {
		var node = $("#confirmemail");
		var str = $(node).val();
		
		if ($(node).val() == "") {
			return false;
		}
		
		if( str == $("#email").val()) {
			$(node).removeClass("invalid");
			$(node).next().css("visibility", "hidden");
			$(node).next().attr("title", "Required field.");
			
			return true;
			
		} else {
			$(node).addClass("invalid");
			$(node).next().css("visibility", "visible");
			$(node).next().attr("title", "Email addresses do not match.");
			
			return false;
		}
	}
</script>
<!--  End of content -->

<?php include("system-footer.php"); ?>
