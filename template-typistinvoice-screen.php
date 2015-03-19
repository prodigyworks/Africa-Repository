		<script src="js/jquery.maskedinput.js" type="text/javascript"></script>
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
								<INPUT style="WIDTH: 88px; text-transform: uppercase;" id="invoicenumber" name="invoicenumber" required="true" value="" />
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
								Pages
							</TD>
							<TD noWrap>
								<INPUT id="pages" name="pages" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Address
							</TD>
							<TD noWrap>
								<TEXTAREA id="address" onchange="address_onchange()" name="address" cols=80 rows=4></TEXTAREA>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Minnutes
							</TD>
							<TD noWrap>
								<TEXTAREA id="minutes" class="tinyMCE name="minutes"></TEXTAREA>
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Paid
							</TD>
							<TD noWrap>
								<SELECT id="paid" name="paid"> 
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
								Rate
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 78px" id="rate" name="total" value="" />
							</TD>
						</TR>
						<TR vAlign="middle">
							<TD vAlign="middle" noWrap>
								Total
							</TD>
							<TD noWrap>
								<INPUT style="WIDTH: 78px" id="total" readOnly name="total" value="" />
							</TD>
						</TR>
					</TBODY>
				</TABLE>
			
			</form>
			<a class="link1 saveheader" onclick="saveHeader()"><em><b><img src='images/save.png' />&nbsp;Save</b></em></a>
			<a class="link1 printinvoice" id="printbutton" onclick="printInvoice()"><em><b><img src='images/print.png' />&nbsp;Print</b></em></a>
			<a class="link1 emailinvoice" id="emailbutton" onclick="emailInvoice()"><em><b><img src='images/email.gif' />&nbsp;E-Mail</b></em></a>
		</div>