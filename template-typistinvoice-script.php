			var currentID = 0;
			var currentInvoiceID = 0;
			var currentVATApplicable = "N";
			
			function printInvoice() {
				window.open("typistinvoicereport.php?id=" + currentInvoiceID);
			}
			
			function emailInvoice() {
				$("#emaildialog").dialog("open");
			}
			
			function saveHeader() {
				if (! verifyStandardForm("#invoiceform")) {
					pwAlert("Invalid form");
					return false;
				}
				
				callAjax(
						"savetypistinvoice.php", 
						{ 
							caseid: currentID,
							invoicenumber: $("#invoiceform #invoicenumber").val(),
							paymentnumber: $("#invoiceform #paymentnumber").val(),
							paymentdate: $("#invoiceform #paymentdate").val(),
							invoicedate: $("#invoiceform #invoicedate").val(),
							total: $("#invoiceform #total").val(),
							paid: $("#invoiceform #paid").val(),
							address: $("#invoiceform #address").val(),
							minutes: $("#invoiceform #minutes").val(),
							pages: $("#invoiceform #paegs").val()
						},
						function(data) {
							currentInvoiceID = data[0].id;

							$("#printbutton").show();
							$("#emailbutton").show();

							refreshData();
						}
					);
					
				
			}
			
			function editInvoice(id) {
				currentID = id;
				
				$("#invoiceform #address").val("");
				$("#invoiceform #minutes").val("");
				$("#invoiceform #pages").val("0");
				$("#invoiceform #invoicenumber").val("");
				$("#invoiceform #paymentnumber").val("");
				$("#invoiceform #paymentdate").val("");
				$("#invoiceform #invoicedate").val("");
				$("#invoiceform #total").val("0.00");
							
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.*, DATE_FORMAT(A.paymentdate, '%d/%m/%Y') AS paymentdate2, DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate2, D.depositamount, D.casenumber, D.j33number, E.vatapplicable, E.name AS courtname, E.address, G.name AS clientcourtname FROM <?php echo $_SESSION['DB_PREFIX'];?>cases D LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoices A ON D.id = A.caseid LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoiceitems B ON B.invoiceid = A.id LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoiceitemtemplates C ON C.id = B.templateid  INNER JOIN <?php echo $_SESSION['DB_PREFIX'];?>courts E ON E.id = D.courtid LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>courts G ON G.id = D.clientcourtid WHERE D.id = " + id
						},
						function(data) {
							var html = "<TABLE width='100%' class='grid list'><?php createHeader(); ?>";
							
							$(".addinvoice").attr("disabled", "true");
							
							for (var i = 0; i < data.length; i++) {
								var node = data[i];
								
								if (i == 0) {
									currentInvoiceID = node.id;
									currentVATApplicable = node.vatapplicable;
									
									$("#invoiceform #courtname").text(node.courtname);
									$("#invoiceform #casenumber").text(node.casenumber);
									
									if (currentVATApplicable == "Y") {
										$("#invoiceform #j33number").text(node.clientcourtname);
										$("#invoiceform #j33label").text("Client Court");
										
									} else {
										$("#invoiceform #j33number").text(node.j33number);
										$("#invoiceform #j33label").text("J33 Number");
									}
									
									if (node.depositamount == null) {
										$("#invoiceform #depositamount").val("0.00");
										
									} else {
										$("#invoiceform #depositamount").val(node.depositamount);
									}
									
									if (node.id == null) {
										$("#addinvoicebutton").hide();
										$("#printbutton").hide();
										$("#emailbutton").hide();
										
										if (currentVATApplicable == "N") {
											$("#invoiceform #termsid").val("<?php echo getSiteConfigData()->defaultpaymenttermsforcourt; ?>").trigger("change");
											
										} else {
											$("#invoiceform #termsid").val("<?php echo getSiteConfigData()->defaultpaymenttermsforclient; ?>").trigger("change");
										}
										
										$("#invoiceform #toaddress").val(node.address);
										$("#invoiceform #deladdress").val(node.address);
										$("#invoiceform #contactid").val("<?php echo getSiteConfigData()->defaultcontactid; ?>");
										$("#invoiceform #invoicenumber").val("");
										$("#invoiceform #paymentnumber").val("");
										$("#invoiceform #paymentdate").val("");
										$("#invoiceform #invoicedate").val("<?php echo date("d/m/Y");?>");
										$("#invoiceform #total").val("0.00");
										$("#invoiceform #acctotal").val("0.00");
										$("#invoiceform #shippinghandling").val("0.00");
										
									} else {
										$("#addinvoicebutton").show();
										$("#printbutton").show();
										$("#emailbutton").show();
										
										var depositamount = node.depositamount;
										
										if (depositamount == "") {
											depositamount = 0;
										}
										
										$("#invoiceform #termsid").val("");
										$("#invoiceform #shippinghandling").val(node.shippinghandling);
										$("#invoiceform #total").val(node.total);
										$("#invoiceform #acctotal").val(new Number(parseFloat(node.total) + parseFloat(node.shippinghandling) - parseFloat(depositamount)).toFixed(2));
										$("#invoiceform #toaddress").val(node.toaddress);
										$("#invoiceform #deladdress").val(node.deladdress);
										$("#invoiceform #termsid").val(node.termsid);
										$("#invoiceform #contactid").val(node.contactid);
										$("#invoiceform #invoicenumber").val(node.invoicenumber);
										$("#invoiceform #paymentnumber").val(node.paymentnumber);
										
										if (node.paymentdate2 == "00/00/0000") {
											$("#invoiceform #paymentdate").val("");
											
										} else {
											$("#invoiceform #paymentdate").val(node.paymentdate2);
										}
										
										$("#invoiceform #invoicedate").val(node.createddate2);
									}
									
									$(".addinvoice").removeAttr("disabled");
								}
								
								if (node.name != null) {
									html += "<TR>";
									html += "<TD><img title='Edit item' src='images/edit.png' onclick='editItem(" + node.itemid + ")' />&nbsp;<img src='images/delete.png'  title='Remove item' onclick='removeItem(" + node.itemid + ")' /></TD>";
									html += "<TD>" + node.name + "</TD>";
									html += "<TD align=right>" + new Number(node.qty).toFixed(0) + "</TD>";
									html += "<TD align=right>" + new Number(node.unitprice).toFixed(2) + "</TD>";
									html += "<TD align=right>" + new Number(node.vatrate).toFixed(2) + "</TD>";
									html += "<TD align=right>" + new Number(node.vat).toFixed(2) + "</TD>";
									html += "<TD align=right>" + new Number(node.itemtotal).toFixed(2) + "</TD>";
									html += "</TR>\n";
								}
							}

							html = html + "</TABLE>";
							
							$("#divtable").html(html);
							$("#invoicedialog").dialog("open");
						}
					);
			}
			
			function toaddress_onchange() {
				if ($("#invoiceform #deladdress").val() == "") {
					$("#invoiceform #deladdress").val($("#toaddress").val());
				}
			}
			
			function validateForm() {
				return true;
			}
			
			$(document).ready(
					function() {
						$("#templateid").change(
								function() {
									callAjax(
											"finddata.php", 
											{ 
												sql: "SELECT clientprice, courtprice FROM <?php echo $_SESSION['DB_PREFIX'];?>invoiceitemtemplates C WHERE id = " + $("#templateid").val()
											},
											function(data) {
												if (data.length == 1) {
													var node = data[0];
													
													if (currentVATApplicable == "N") {
														$("#unitprice").val(node.courtprice).trigger("change");
														
													} else {
														$("#unitprice").val(node.clientprice).trigger("change");
													}
												}
											}
										);
								}
							);
							
						//AAA1234/0711
						$("#invoicenumber").mask("aaa9999/9999");
						
						$("#invoicedialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								hide:"fade",
								width: 800,
								title:"Invoice",
								open: function(event, ui){
									
								},
								buttons: {
									"Close": function() {
										$(this).dialog("close")
									}
								}
							});		
												
						$("#emaildialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								hide:"fade",
								width: 600,
								title:"Invoice",
								open: function(event, ui){
									
								},
								buttons: {
									"Send": function() {
										$(this).dialog("close")
																			
										callAjax(
												"emaildocument.php", 
												{ 
													id: currentInvoiceID,
													emailaddress: $("#emailaddress").val()
												},
												function(data) {
													pwAlert("Email sent to " + $("#emailaddress").val());
												}
											);
									},
									"Close": function() {
										$(this).dialog("close")
									}
								}
							});		
													
						$("#invoiceitemdialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								width: 340,
								hide:"fade",
								title:"Invoice Item",
								open: function(event, ui){
									
								},
								buttons: {
									"Save": function() {
										if (saveInvoiceItem()) {
											$(this).dialog("close");
											
										}
									},
									Cancel: function() {
										$(this).dialog("close");
									}
								}
							});
					}
				);
