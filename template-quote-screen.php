		<div class="modal" id="quotedescriptionareadialog">
			<label>Description</label>
			<textarea id="quotedescriptionarea" name="quotedescriptionarea" cols=140 rows=8></textarea>
		</div>
		<div class="modal" id="quote_quoteitemdialog">
			<form id="quote_quoteitemform" method="post" class="editform entryform">
				<INPUT type="hidden" id="quote_itemid" name="quote_itemid"  />
				<TABLE cellSpacing="5" cellPadding="10">
					<TBODY>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Item
							</TD>
							<TD noWrap>
								<?php createCombo("quote_templateid", "id", "displayname", "{$_SESSION['DB_PREFIX']}invoiceitemtemplates"); ?>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Quantity
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="quote_qty" name="quote_qty"  onchange="quote_qty_onchange()" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Unit Price
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="quote_unitprice" name="quote_unitprice" onchange="quote_qty_onchange()" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								VAT Rate
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="quote_vatrate" name="quote_vatrate"  onchange="quote_qty_onchange()"  value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								VAT
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="quote_vat" name="quote_vat" readonly value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Total
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 80px" id="quote_itemtotal" name="quote_itemtotal" readonly value="" />
							</TD>
						</TR>
					</TBODY>
				</TABLE>
			</form>
		</div>
		<div class="modal" id="quote_emaildialog">
			<label>E-Mail Address</label>
			<input type="text" id="quote_emailaddress" name="quote_emailaddress" size=80 />
		</div>
		<div class="modal" id="quote_quotedialog">
			<form id="quote_quoteform" method="post" class="editform entryform">
				<TABLE cellSpacing="5" cellPadding="10">
					<TBODY>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Court / Client
							</TD>
							<TD noWrap>
								<DIV>
									<SPAN class="bold" id="quote_courtname"></SPAN>
								</DIV>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Case Number
							</TD>
							<TD noWrap>
								<DIV>
									<SPAN  class="bold" id="quote_casenumber"></SPAN>
								</DIV>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								<span id="quote_j33label">J33 Number</span>
							</TD>
							<TD noWrap>
								<DIV>
									<SPAN  class="bold" id="quote_j33number"></SPAN>
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
								Quote Number
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 88px; text-transform: uppercase;" id="quote_quotenumber" name="quote_quotenumber" readonly required="true" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Quote Date
							</TD>
							<TD noWrap>
								<INPUT id="quote_quotedate" class="datepicker" name="quote_quotedate" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Office
							</TD>
							<TD noWrap>
								<?php createCombo("quote_officeid", "id", "name", "{$_SESSION['DB_PREFIX']}offices"); ?>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Admin Clerk
							</TD>
							<TD noWrap>
								<?php createUserCombo("quote_contactid", " WHERE A.member_id in (SELECT AA.memberid FROM {$_SESSION['DB_PREFIX']}userroles AA WHERE AA.roleid = 'OFFICE') "); ?>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Billing Address
							</TD>
							<TD noWrap>
								<TEXTAREA id="quote_toaddress" onchange="quote_toaddress_onchange()" name="quote_toaddress" cols=80 rows=4></TEXTAREA>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Our Ref
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 180px" id="quote_ourref" name="quote_ourref" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Your Ref
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 180px" id="quote_yourref" name="quote_yourref" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Estimate Paid
							</TD>
							<TD noWrap>
								<SELECT id="quote_paid" name="quote_paid"> 
									<OPTION value="N">No</OPTION> 
									<OPTION value="Y">Yes</OPTION>
								</SELECT>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Deposit Payment No
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 180px" id="quote_paymentnumber" name="quote_paymentnumber" value="" onchange="quote_paymentnumber_onchange()" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Estimate Payment Date
							</TD>
							<TD noWrap>
								<INPUT id="quote_paymentdate" class="datepicker" name="quote_paymentdate" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Sub Total
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 78px" id="quote_total" readOnly name="quote_total" value="0.00" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Deposit Required
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 78px" id="quote_depositamount" name="quote_depositamount" value="" onchange="quote_paymentnumber_onchange()" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Total
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 78px" id="quote_acctotal" readOnly name="quote_acctotal" value="" />
								<span>N/A</span>
								<INPUT type="checkbox" id="quote_na" name="quote_na" />
							</TD>
						</TR>
					</TBODY>
				</TABLE>
			
			</form>
			
			<div class="quotebuttons">
				<table>
					<tr>
						<td>
							<a class="link1" onclick="quote_saveHeader()"><em><b><img src='images/save.png' />&nbsp;Save Header</b></em></a>
						</td>
						<td>
							<a class="link1" id="quote_addquotebutton" onclick="addQuoteItem()"><em><b><img src='images/add.png' />&nbsp;Add Item</b></em></a>
						</td>
						<td>
							<a class="link1" id="quote_printbutton" onclick="printQuote()"><em><b><img src='images/print.png' />&nbsp;Print</b></em></a>
						</td>
						<td>
							<a class="link1" id="quotedescriptionareabutton" onclick="showQuoteDescription()"><em><b><img src='images/document.gif' />&nbsp;Description</b></em></a>
						</td>
						<td>
							<a class="link1" id="quote_emailbutton" onclick="emailQuote()"><em><b><img src='images/email.gif' />&nbsp;E-Mail</b></em></a>
						</td>
					</tr>
				</table>
			</div>
			
			<DIV style='height:200px; overflow-y: auto; border:1px solid grey' id="quote_divtable">
				<TABLE width='100%' class="grid list">
				<?php createHeader(); ?>
				</TABLE>
			</DIV>
		</div>