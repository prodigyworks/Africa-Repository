		<script src="js/jquery.maskedinput.js" type="text/javascript"></script>
		<div class="modal" id="invoiceitemdialog">
			<form id="invoiceitemform" method="post" class="editform entryform">
				<INPUT type="hidden" id="itemid" name="itemid"  />
				<TABLE cellSpacing="5" cellPadding="10">
					<TBODY>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Item
							</TD>
							<TD noWrap>
								<?php createCombo("templateid", "id", "displayname", "{$_SESSION['DB_PREFIX']}invoiceitemtemplates"); ?>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Quantity
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="qty" name="qty"  onchange="qty_onchange()" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Unit Price
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="unitprice" name="unitprice" onchange="qty_onchange()" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								VAT Rate
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="vatrate" name="vatrate"  onchange="qty_onchange()"  value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								VAT
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="vat" name="vat" readonly value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Total
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="itemtotal" name="itemtotal" readonly value="" />
							</TD>
						</TR>
					</TBODY>
				</TABLE>
			</form>
		</div>
		<div class="modal" id="invoice_descriptiondialog">
			<label>Description</label>
			<textarea id="invoice_description" name="invoice_description" cols=140 rows=8></textarea>
		</div>
		<div class="modal" id="invoice_refunddialog">
			<label>Refund Ref</label><br>
			<INPUT id="refundref" name="refundref" value="" style="width:240px" /><br>
			<label>Refund Date</label><br>
			<INPUT id="refunddate" name="refunddate" value="" class="datepicker" /><br>
			<label>Refund Amount</label><br>
			<INPUT id="refundamount" name="refundamount" value="" style="width:80px" />
		</div>
		<div class="modal" id="invoice_creditdialog">
			<form id="credtform" method="post" class="editform entryform">
				<label>Credit Note Ref</label>
				<INPUT required="true" id="cred_ref" name="cred_ref" value="" cols=30 />
				<label>Credit Note Date</label>
				<INPUT required="true" id="cred_date" name="cred_date" value="" class="datepicker" />
				<label>Office</label>
				<?php createCombo("cred_officeid", "id", "name", "{$_SESSION['DB_PREFIX']}offices"); ?>
				<label>Contact</label>
				<?php createUserCombo("cred_contactid"); ?>
				<label>Reason</label>
				<TEXTAREA required="true" id="cred_reason" name="cred_reason" cols=130 rows=5></TEXTAREA>
			</form>
		</div>
		<div class="modal" id="emaildialog">
			<label>E-Mail Address</label>
			<input type="text" id="emailaddress" name="emailaddress" size=80 />
		</div>
		<div class="modal" id="invoicedialog">
			<form id="invoiceform" method="post" class="editform entryform">
				<TABLE cellSpacing="5" cellPadding="10">
					<TBODY>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Court / Client
							</TD>
							<TD noWrap>
								<DIV>
									<SPAN class="bold" id="courtname"></SPAN>
								</DIV>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Case Number
							</TD>
							<TD noWrap>
								<DIV>
									<SPAN  class="bold" id="casenumber"></SPAN>
								</DIV>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								<span id="j33label">J33 Number</span>
							</TD>
							<TD noWrap>
								<DIV>
									<SPAN  class="bold" id="j33number"></SPAN>
								</DIV>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap colspan=2>
								<hr />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Invoice Number
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 88px; text-transform: uppercase;" id="invoicenumber" name="invoicenumber" readonly required="true" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Invoice Date
							</TD>
							<TD noWrap>
								<INPUT id="invoicedate" class="datepicker" name="invoicedate" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Office
							</TD>
							<TD noWrap>
								<?php createCombo("officeid", "id", "name", "{$_SESSION['DB_PREFIX']}offices"); ?>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Contact
							</TD>
							<TD noWrap>
								<?php createUserCombo("contactid"); ?>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Billing Address
							</TD>
							<TD noWrap>
								<TEXTAREA id="toaddress" onchange="toaddress_onchange()" name="toaddress" cols=80 rows=4></TEXTAREA>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Terms
							</TD>
							<TD noWrap>
								<?php createCombo("termsid", "id", "name", "{$_SESSION['DB_PREFIX']}caseterms"); ?>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Paid
							</TD>
							<TD noWrap>
								<SELECT id="paid" name="paid" onchange="paid_change()"> 
									<OPTION value="N">No</OPTION> 
									<OPTION value="Y">Yes</OPTION>
								</SELECT>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Payment Number
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 180px" id="paymentnumber" name="paymentnumber" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Payment Date
							</TD>
							<TD noWrap>
								<INPUT id="paymentdate" class="datepicker" name="paymentdate" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Penalty
							</TD>
							<TD noWrap>
								<SELECT id="penalty" name="penalty" onchange="shippinghandling_onchange()">
									<OPTION value="N">No Penalty</OPTION>
									<OPTION value="T">10%</OPTION>
									<OPTION value="F">15%</OPTION>
									<OPTION value="Y">50%</OPTION>
								</SELECT>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Sub Total
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 78px" id="total" readOnly name="total" value="0.00" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Estimate Paid
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 78px" id="depositamount" readOnly name="depositamount" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Total
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 78px" id="acctotal" readOnly name="acctotal" value="" />
							</TD>
						</TR>
					</TBODY>
				</TABLE>
			
			</form>
			
			<div class="invoicebuttons">
				<table>
					<tr>
						<td>
							<a class="link1" id="convertquote" onclick="convertQuote()"><em><b><img src='images/accept.png' />&nbsp;Convert Quote</b></em></a>
						</td>
						<td>
							<a class="link1" onclick="saveHeader()"><em><b><img src='images/save.png' />&nbsp;Save</b></em></a>
						</td>
						<td>
							<a class="link1" id="creditbutton" onclick="credit()"><em><b><img src='images/back2.png' />&nbsp;Credit</b></em></a>
						</td>
						<td>
							<a class="link1" id="refundbutton" onclick="refunds()"><em><b><img src='images/back2.png' />&nbsp;Refunds</b></em></a>
						</td>
						<td>
							<a class="link1" id="addinvoicebutton" onclick="addInvoiceItem()"><em><b><img src='images/add.png' />&nbsp;Add Item</b></em></a>
						</td>
						<td>
							<a class="link1" id="printbutton" onclick="printInvoice()"><em><b><img src='images/print.png' />&nbsp;Print</b></em></a>
						</td>
						<td>
							<a class="link1" id="descriptionbutton" onclick="showDescription()"><em><b><img src='images/document.gif' />&nbsp;Description</b></em></a>
						</td>
						<td>
							<a class="link1" id="emailbutton" onclick="emailInvoice()"><em><b><img src='images/email.gif' />&nbsp;E-Mail</b></em></a>
						</td>
					</tr>
				</table>
			</div>
			<DIV style='height:200px; overflow-y: auto; border:1px solid grey' id="divtable">
				<TABLE width='100%' class="grid list">
				<?php createHeader(); ?>
				</TABLE>
			</DIV>
		</div>