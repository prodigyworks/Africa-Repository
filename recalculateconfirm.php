<?php
	require_once("system-header.php");
?>
<h2>Criteria</h2>
<br>
<table class="criteria" cellpadding=10>
	<tr>
		<td class="label">
			Date From
		</td>
		<td>
			:
		</td>
		<td>
			<?php if (isset($_POST['datefrom'])) echo $_POST['datefrom']; ?>
		</td>
	</tr>
	<tr>
		<td class="label">
			Date To
		</td>
		<td>
			:
		</td>
		<td>
			<?php if (isset($_POST['dateto'])) echo $_POST['dateto']; ?>
		</td>
	</tr>
	<tr>
		<td class="label">
			Status
		</td>
		<td>
			:
		</td>
		<td>
			<?php 
				if (isset($_POST['status'])) {
					if ($_POST['status'] == "Y") echo "Paid";
					if ($_POST['status'] == "N") echo "Unpaid";
				} 
			?>
		</td>
	</tr>
	<tr>
		<td class="label">
			Court / Client
		</td>
		<td>
			:
		</td>
		<td>
			<?php 
				if (isset($_POST['courtid'])) {
					$sql = "SELECT name  " .
							"FROM {$_SESSION['DB_PREFIX']}courts " .
							"WHERE id = " . $_POST['courtid'] . " ";
					$result = mysql_query($sql);
					
					if ($result) {
						while (($member = mysql_fetch_assoc($result))) {
							echo $member['name'];
						}
					}
				} 
			?>
		</td>
	</tr>
	<tr>
		<td class="label">
			Percentage Increase
		</td>
		<td>
			:
		</td>
		<td>
			<?php if (isset($_POST['percentage'])) echo $_POST['percentage']; ?>
		</td>
	</tr>
</table>
<br>
<?php	
	if (isset($_POST['marker'])) {
		echo "<h2>Invoices have been recalcuated</h2>";
		
		$and = "";
		
		if (isset($_POST['status']) && $_POST['status'] != "") {
			$and .= " AND A.paid = '" . $_POST['status'] . "' ";
		}
		
		if (isset($_POST['datefrom']) && $_POST['datefrom'] != "") {
			$and .= " AND A.createddate >= '" . $_POST['datefrom'] . "' ";
		}
		
		if (isset($_POST['dateto']) && $_POST['dateto'] != "") {
			$and .= " AND A.createddate <= '" . $_POST['dateto'] . "' ";
		}
		
		if (isset($_POST['courtid']) && $_POST['courtid'] != "0") {
			$and .= " AND D.id = " . $_POST['courtid'] . " ";
		}
		
		$sql = "SELECT A.*, DATE_FORMAT(A.paymentdate, '%d/%m/%Y') AS paymentdate, " .
				"D.name AS courtname, E.name AS provincename, F.name AS terms, G.firstname, G.lastname " .
				"FROM {$_SESSION['DB_PREFIX']}invoices A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
				"ON B.id = A.caseid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
				"ON D.id = B.courtid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
				"ON E.id = D.provinceid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}caseterms F " .
				"ON F.id = A.termsid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}members G " .
				"ON G.member_id = A.contactid " .
				"WHERE 1 = 1 $and " .
				"ORDER BY A.id DESC";
		$result = mysql_query($sql);
		
		$percentage = (1 + ($_POST['percentage'] / 100));
		
		if ($result) {
			while (($member = mysql_fetch_assoc($result))) {
				$sql = "UPDATE {$_SESSION['DB_PREFIX']}invoiceitems SET " .
						"unitprice = unitprice * $percentage, " .
						"total = total * $percentage, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
						"WHERE invoiceid = " . $member['id'];
				$updateresult = mysql_query($sql);
				
				if (! $updateresult) {
					logError($sql . " - " . mysql_error());
				}
				
				$sql = "UPDATE {$_SESSION['DB_PREFIX']}invoices SET " .
						"total = total * $percentage, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
						"WHERE id = " . $member['id'];
				$updateresult = mysql_query($sql);
				
				if (! $updateresult) {
					logError($sql . " - " . mysql_error());
				}
			}
		}
		
	} else {
?>
<DIV style="border: 1px solid black">
<TABLE class="grid list" width='100%' cellpadding=2>
	<THEAD>
		<TR>
			<TD>Province</TD>
			<TD>Court / Client</TD>
			<TD>Invoice Number</TD>
			<TD>Payment Number</TD>
			<TD>Payment Date</TD>
			<TD>Status</TD>
			<TD align="right">Price</TD>
			<TD align="right">New Price</TD>
		</TR>
	</THEAD>
<?php
	$and = "";
	
	if (isset($_POST['status']) && $_POST['status'] != "") {
		$and .= " AND A.paid = '" . $_POST['status'] . "' ";
	}
	
	if (isset($_POST['datefrom']) && $_POST['datefrom'] != "") {
		$and .= " AND A.createddate >= '" . $_POST['datefrom'] . "' ";
	}
	
	if (isset($_POST['dateto']) && $_POST['dateto'] != "") {
		$and .= " AND A.createddate <= '" . $_POST['dateto'] . "' ";
	}
	
	if (isset($_POST['courtid']) && $_POST['courtid'] != "0") {
		$and .= " AND D.id = " . $_POST['courtid'] . " ";
	}

	$sql = "SELECT A.*, DATE_FORMAT(A.paymentdate, '%d/%m/%Y') AS paymentdate, " .
			"D.name AS courtname, E.name AS provincename, F.name AS terms, G.firstname, G.lastname " .
			"FROM {$_SESSION['DB_PREFIX']}invoices A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}cases B " .
			"ON B.id = A.caseid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}courts D " .
			"ON D.id = B.courtid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}province E " .
			"ON E.id = D.provinceid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}caseterms F " .
			"ON F.id = A.termsid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members G " .
			"ON G.member_id = A.contactid " .
			"WHERE 1 = 1 $and " .
			"ORDER BY A.id DESC";
	$result = mysql_query($sql);
	
	$percentage = (1 + ($_POST['percentage'] / 100));
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$newtotal = $member['total'] * $percentage;
			
			if ($member['paid'] == "Y") {
				$status = "Paid";
				
			} else {
				$status = "Unpaid";
			}
?>
		<TR>
			<TD><?php echo $member['provincename']; ?></TD>
			<TD><?php echo $member['courtname']; ?></TD>
			<TD><?php echo $member['invoicenumber']; ?></TD>
			<TD><?php echo $member['paymentnumber']; ?></TD>
			<TD><?php echo $member['paymentdate']; ?></TD>
			<TD><?php echo $status; ?></TD>
			<TD align=right><?php echo number_format($member['total'], 2); ?></TD>
			<TD align=right><?php echo number_format($newtotal, 2); ?></TD>
		</TR>
<?php
		}
	}
?>
</TABLE>
</DIV>
<br>
<FORM method="post" id="recalcform">
	<input type="hidden" name="marker" value="1" />
	<input type="hidden" name="percentage" value="<?php if (isset($_POST['percentage'])) echo $_POST['percentage']; ?>" />
	<input type="hidden" name="datefrom" value="<?php if (isset($_POST['datefrom'])) echo $_POST['datefrom']; ?>" />
	<input type="hidden" name="dateto" value="<?php if (isset($_POST['dateto'])) echo $_POST['dateto']; ?>" />
	<input type="hidden" name="courtid" value="<?php if (isset($_POST['courtid'])) echo $_POST['courtid']; ?>" />
	<input type="hidden" name="status" value="<?php if (isset($_POST['status'])) echo $_POST['status']; ?>" />
	<A class="link1 rgap5" onclick="$('#recalcform').submit()"><EM><B>Confirm</B></EM></A>
	<A class="link1 fleft" href="recalculateprice.php"><EM><B>Back</B></EM></A>
</FORM>
<?php
	}
	
	require_once("system-footer.php");
?>
