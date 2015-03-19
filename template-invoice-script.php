			var currentID = 0;
			var currentInvoiceID = 0;
			var currentVATApplicable = "N";
			
			function printInvoice() {
				window.open("invoicereport.php?id=" + currentInvoiceID);
			}
			
			function emailInvoice() {
				$("#emaildialog").dialog("open");
			}

			function qty_onchange(node) {
				var qty = parseInt($("#qty").val());
				var unitprice = parseFloat($("#invoiceitemform #unitprice").val());
				var vatrate = parseFloat($("#invoiceitemform #vatrate").val());
				
				if (isNaN(unitprice)) {
					unitprice = 0;
				}
				
				if (isNaN(vatrate)) {
					vatrate = 0;
				}
				
				if (isNaN(qty)) {
					qty = 0;
				}
				
				var total = parseFloat(qty * unitprice);
				var vat = total * (vatrate / 100);
				
				total += vat;
				
				$("#invoiceitemform #vatrate").val(new Number(vatrate).toFixed(2));
				$("#invoiceitemform #unitprice").val(new Number(unitprice).toFixed(2));
				$("#invoiceitemform #qty").val(new Number(qty).toFixed(0));
				$("#invoiceitemform #vat").val(new Number(vat).toFixed(2));
				$("#invoiceitemform #itemtotal").val(new Number(total).toFixed(2));
			}
			
			function refunds() {
				$("#invoice_refunddialog").dialog("open");
			}
			
			function credit() {
				if ($("#cred_ref").val() == "") {
					callAjax(
							"getnextcreditnumber.php", 
							{ 
							},
							function(data) {
								if (data.length == 1) {
									if (currentVATApplicable == "N") {
										$("#cred_ref").val("C" + "<?php echo strtoupper(substr(GetOfficeName(getLoggedOnMemberID()), 0, 2));?>" + "C" + data[0].credit );

									} else {
										$("#cred_ref").val("C" + "<?php echo strtoupper(substr(GetOfficeName(getLoggedOnMemberID()), 0, 2));?>" + "A" + data[0].credit );
									}
									
									$("#cred_date").val("<?php echo date("d/m/Y"); ?>");
								}
								
								$("#invoice_creditdialog").dialog("open");
							}
						);

				} else {
					$("#invoice_creditdialog").dialog("open");
				}
			}
			
			function saveHeader() {
				if (! verifyStandardForm("#invoiceform")) {
					pwAlert("Invalid form");
					return false;
				}
				
				callAjax(
						"saveinvoice.php", 
						{ 
							caseid: currentID,
							description: $('#invoice_description').val(),
							refundref: $("#refundref").val(),
							refundamount: $("#refundamount").val(),
							refunddate: $("#refunddate").val(),
							invoicenumber: $("#invoiceform #invoicenumber").val(),
							paymentnumber: $("#invoiceform #paymentnumber").val(),
							shippinghandling: 0,
							paymentdate: $("#invoiceform #paymentdate").val(),
							penalty: $("#invoiceform #penalty").val(),
							invoicedate: $("#invoiceform #invoicedate").val(),
							total: $("#invoiceform #total").val(),
							paid: $("#invoiceform #paid").val(),
							toaddress: $("#invoiceform #toaddress").val(),
							deladdress: "",
							termsid: $("#invoiceform #termsid").val(),
							contactid: $("#invoiceform #contactid").val(),
							officeid: $("#invoiceform #officeid").val()
						},
						function(data) {
							$(".addinvoice").removeAttr("disabled");

							currentInvoiceID = data[0].id;
							
							$("#addinvoicebutton").show();
							$("#printbutton").show();
							$("#emailbutton").show();

							refreshData();
						}
					);
					
				
			}
			
			function printCredit() {
				window.open("creditreport.php?id=" + currentID);
			}
			
			function saveCredit() {
				if (! verifyStandardForm("#creditform")) {
					pwAlert("Invalid form");
					return false;
				}
				
				callAjax(
						"savecredit.php", 
						{ 
							caseid: currentID,
							creditref: $('#cred_ref').val(),
							creditdate: $('#cred_date').val(),
							creditreason: $("#cred_reason").val(),
							creditofficeid: $("#cred_officeid").val(),
							creditcontactid: $("#cred_contactid").val()
						},
						function(data) {
							refreshData();
						}
					);
					
				
			}
			
			function populateTable(data) {
				$("#invoiceform #total").val(data[0].headertotal);
				shippinghandling_onchange();
											
				var html = "<TABLE width='100%' class='grid list'><?php createHeader(); ?>";
				
				for (var i = 0; i < data.length; i++) {
					var node = data[i];
					
					if (node.name != null) {
						html += "<TR>";
						html += "<TD><img src='images/edit.png'  title='Edit item' onclick='editItem(" + node.id + ")' />&nbsp;<img src='images/delete.png'  title='Remove item' onclick='removeItem(" + node.id + ")' /></TD>";
						html += "<TD>" + node.name + "</TD>";
						html += "<TD align=right>" + new Number(node.qty).toFixed(0) + "</TD>";
						html += "<TD align=right>" + new Number(node.unitprice).toFixed(2) + "</TD>";
						html += "<TD align=right>" + new Number(node.vatrate).toFixed(2) + "</TD>";
						html += "<TD align=right>" + new Number(node.vat).toFixed(2) + "</TD>";
						html += "<TD align=right>" + new Number(node.total).toFixed(2) + "</TD>";
						html += "</TR>\n";
					}
				}

				html = html + "</TABLE>";
				
				$("#divtable").html(html);
			}
			
			function saveInvoiceItem() {
				if (! verifyStandardForm("#invoiceitemform")) {
					pwAlert("Invalid form");
					return false;
				}
				
				callAjax(
						"saveinvoiceitem.php", 
						{ 
							id: $("#invoiceitemform #itemid").val(),
							invoiceid: currentInvoiceID,
							qty: $("#invoiceitemform #qty").val(),
							unitprice: $("#invoiceitemform #unitprice").val(),
							vatrate: $("#invoiceitemform #vatrate").val(),
							vat: $("#invoiceitemform #vat").val(),
							total: $("#invoiceitemform #itemtotal").val(),
							templateid: $("#invoiceitemform #templateid").val()
						},
						function(data) {
							populateTable(data);

							refreshData();
						}
					);
					
				return true;
			}
			
			function removeItem(id) {
				if (confirm("You are about to remove this item. Are you sure ?")) {
					callAjax(
							"removeinvoiceitem.php", 
							{ 
								id: id,
								invoiceid: currentInvoiceID
							},
							function(data) {
								populateTable(data);
							}
						);
				}
			}
			
			function editItem(id) {
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.*, C.name FROM <?php echo $_SESSION['DB_PREFIX'];?>invoiceitems A LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoiceitemtemplates C ON C.id = A.templateid WHERE A.id = " + id
						},
						function(data) {
							if (data.length == 1) {
								var node = data[0];
								
								$("#invoiceitemform #itemid").val(node.id);
								$("#invoiceitemform #qty").val(node.qty);
								$("#invoiceitemform #unitprice").val(node.unitprice);
								$("#invoiceitemform #vat").val(node.vat);
								$("#invoiceitemform #itemtotal").val(node.total);
								$("#invoiceitemform #templateid").val(node.templateid);

								if (currentVATApplicable == "N") {
									$("#invoiceitemform #vatrate").val("0.00");
									$("#invoiceitemform #vatrate").attr("disabled", true);
									
								} else {
									$("#invoiceitemform #vatrate").attr("disabled", false);
									$("#invoiceitemform #vatrate").val(node.vatrate);
								}
								
								$("#invoiceitemdialog").dialog("open");
							}
						}
					);
			}
			
			function shippinghandling_onchange() {
				var subtotal = parseFloat($("#invoiceform #total").val());
				var shippinghandling = 0;
				var depositamount = parseFloat($("#invoiceform #depositamount").val());
				var penalty = $("#invoiceform #penalty").val();
				
				total = parseFloat((subtotal + shippinghandling) - depositamount);
				
				if (penalty == "T") {
					total = total * 0.9;

				} else if (penalty == "F") {
					total = total * 0.85;

				} else if (penalty == "Y") {
					total = total * 0.5;
				}
				
				$("#invoiceform #acctotal").val(new Number(total).toFixed(2));
				$("#invoiceform #depositamount").val(new Number(depositamount).toFixed(2));
			}
			
			function paid_change() {
				if ($("#paid").val() == "N") {
					$("#invoiceform #paymentnumber").val("");
					$("#invoiceform #paymentdate").val("");
					$("#invoiceform #paymentnumber").attr("disabled", true);
					$("#invoiceform #paymentdate").attr("disabled", true);
					$("#invoiceform #paymentnumber").attr("required", false);
					$("#invoiceform #paymentdate").attr("required", false);
										
				} else {
					$("#invoiceform #paymentnumber").attr("disabled", false);
					$("#invoiceform #paymentdate").attr("disabled", false);
					$("#invoiceform #paymentnumber").attr("required", true);
					$("#invoiceform #paymentdate").attr("required", true);
				}
			}
			
			function addInvoiceItem() {
				$("#invoiceitemform #itemid").val("");
				$("#invoiceitemform #templateid").val("0");
				$("#invoiceitemform #qty").val("1");
				
				if (currentVATApplicable == "N") {
					$("#invoiceitemform #vatrate").val("0.00");
					$("#invoiceitemform #vatrate").attr("disabled", true);
					
				} else {
					$("#invoiceitemform #vatrate").attr("disabled", false);
					$("#invoiceitemform #vatrate").val("<?php echo number_format(getSiteConfigData()->vatrate, 2); ?>");
				}
				
				$("#invoiceitemform #vat").val("0.00");
				$("#invoiceitemform #unitprice").val("0.00");
				$("#invoiceitemform #itemtotal").val("0.00");
				
				$('#invoiceitemdialog').dialog('open');				
			}
			
			function editInvoice(id) {
				currentID = id;
				
				$("#cred_ref").val("");
				$("#cred_date").val("");
				$("#cred_contactid").val("0");
				$("#cred_officeid").val("0");
				$("#cred_reason").val("");
				
				$("#invoiceform #toaddress").val("");
				$("#invoiceform #termsid").val("0");
				$("#invoiceform #contactid").val("<?php echo getLoggedOnMemberID(); ?>");
				$("#invoiceform #officeid").val("<?php echo GetOfficeID(getLoggedOnMemberID()); ?>");
				$("#invoiceform #invoicenumber").val("");
				$("#invoiceform #paymentnumber").val("");
				$("#invoiceform #paymentdate").val("");
				$("#invoiceform #penalty").val("N");
				$("#invoiceform #invoicedate").val("");
				$("#invoiceform #total").val("0.00");
				$("#invoiceform #acctotal").val("0.00");
				$("#invoiceform #depositamount").val("0.00");
							
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.*, E.provinceid, X.id AS quoteid, DATE_FORMAT(A.creditdate, '%d/%m/%Y') AS creditdate, DATE_FORMAT(A.paymentdate, '%d/%m/%Y') AS paymentdate2, DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate2, B.id AS itemid, B.qty, B.unitprice, B.vat, B.vatrate, B.total AS itemtotal, C.name, D.depositamount, D.casenumber, D.j33number, E.vatapplicable, E.name AS courtname, E.address, G.name AS clientcourtname FROM <?php echo $_SESSION['DB_PREFIX'];?>cases D LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoices A ON D.id = A.caseid LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoiceitems B ON B.invoiceid = A.id LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoiceitemtemplates C ON C.id = B.templateid  INNER JOIN <?php echo $_SESSION['DB_PREFIX'];?>courts E ON E.id = D.courtid LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>courts G ON G.id = D.clientcourtid  LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>quotes X ON X.caseid = D.id WHERE D.id = " + id
						},
						function(data) {
							var html = "<TABLE width='100%' class='grid list'><?php createHeader(); ?>";
							
							$(".addinvoice").attr("disabled", "true");
							
							for (var i = 0; i < data.length; i++) {
								var node = data[i];
								
								if (i == 0) {
									$.ajax({
											url: "createprovinceratecombo.php",
											dataType: 'html',
											async: false,
											data: { 
												provinceid: node.provinceid
											},
											type: "POST",
											error: function(jqXHR, textStatus, errorThrown) {
												pwAlert("ERROR :" + errorThrown);
											},
											success: function(data) {
												$("#templateid").html(data);
											}
										});
								
									currentInvoiceID = node.id;
									currentVATApplicable = node.vatapplicable;
									
									$("#cred_ref").val(node.creditnumber);
									$("#cred_date").val(node.creditdate);
									$("#cred_contactid").val(node.creditcontactid);
									$("#cred_officeid").val(node.creditofficeid);
									$("#cred_reason").val(node.creditreason);
									
									$("#invoiceform #courtname").text(node.courtname);
									$("#invoiceform #casenumber").text(node.casenumber);
									$("#invoiceform #paid").val(node.paid);
									
									if (node.description == null) {
										$('#invoice_description').val("");
										
									} else {
										$('#invoice_description').val(node.description);
									}
									
									$("#refundref").val(node.refundref);
									$("#refundamount").val(node.refundamount);
									$("#refunddate").val(node.refunddate);
									
									if (currentVATApplicable == "Y") {
										$("#invoiceform #j33number").text(node.clientcourtname);
										$("#invoiceform #j33label").text("Client Court");
										$("#depositamount").attr("disabled", false);
										
									} else {
										$("#invoiceform #j33number").text(node.j33number);
										$("#invoiceform #j33label").text("J33 Number");
										$("#depositamount").attr("disabled", true);
									}
									
									if (node.depositamount == null) {
										$("#invoiceform #depositamount").val("0.00");
										
									} else {
										$("#invoiceform #depositamount").val(node.depositamount);
									}

									if (node.id == null) {
										callAjax(
												"getnextinvoicenumber.php", 
												{ 
												},
												function(data) {
													if (data.length == 1) {
														if (currentVATApplicable == "N") {
															$("#invoiceform #invoicenumber").val("I" + "<?php echo substr(GetOfficeName(getLoggedOnMemberID()), 0, 2);?>" + "C" + data[0].invoice );

														} else {
															$("#invoiceform #invoicenumber").val("I" + "<?php echo substr(GetOfficeName(getLoggedOnMemberID()), 0, 2);?>" + "A" + data[0].invoice );
														}
														
														if (node.quoteid != null) {
															$("#convertquote").show();
															
														} else {
															$("#convertquote").hide();
														}
														
														$("#addinvoicebutton").hide();
														$("#printbutton").hide();
														$("#emailbutton").hide();
														
														if (currentVATApplicable == "N") {
															$("#invoiceform #termsid").val("<?php echo getSiteConfigData()->defaultpaymenttermsforcourt; ?>").trigger("change");
															
														} else {
															$("#invoiceform #termsid").val("<?php echo getSiteConfigData()->defaultpaymenttermsforclient; ?>").trigger("change");
														}
														
														$('#invoice_description').val("");
														$("#refundref").val("");
														$("#refundamount").val("");
														$("#refunddate").val("");
														$("#invoiceform #paid").val("N").trigger("change");
														$("#invoiceform #toaddress").val(node.address);
														$("#invoiceform #paymentnumber").val("");
														$("#invoiceform #paymentdate").val("");
														$("#invoiceform #penalty").val("N");
														$("#invoiceform #invoicedate").val("<?php echo date("d/m/Y");?>");
														$("#invoiceform #total").val("0.00");
														$("#invoiceform #acctotal").val("0.00");
													}
												}
											);
										
									} else {
										$("#convertquote").hide();
										$("#addinvoicebutton").show();
										$("#printbutton").show();
										$("#emailbutton").show();
										
										var depositamount = node.depositamount;
										
										if (depositamount == "") {
											depositamount = 0;
										}
										
										$("#invoiceform #termsid").val("");
										$("#invoiceform #total").val(node.total);
										$("#invoiceform #acctotal").val(new Number(parseFloat(node.total) - parseFloat(depositamount)).toFixed(2));
										$("#invoiceform #toaddress").val(node.toaddress);
										$("#invoiceform #termsid").val(node.termsid);
										$("#invoiceform #contactid").val(node.contactid);
										$("#invoiceform #officeid").val(node.officeid);
										$("#invoiceform #invoicenumber").val(node.invoicenumber);
										$("#invoiceform #paymentnumber").val(node.paymentnumber);
										$("#invoiceform #penalty").val(node.penalty);
										
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
			
			function convertQuote() {
				callAjax(
						"convertquote.php", 
						{
							id: currentID,
							invoicenumber: $("#invoiceform #invoicenumber").val()
						},
						function(data) {
							editInvoice(currentID);
						}
					);
			}
			
			function toaddress_onchange() {
			}
			
			function showDescription() {
				$("#invoice_descriptiondialog").dialog("open");
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
							
						$("#invoice_refunddialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								hide:"fade",
								title:"Refund",
								open: function(event, ui){
									
								},
								buttons: {
									Ok: function() {
										$(this).dialog("close");
									},
									Cancel: function() {
										$(this).dialog("close");
									}
								}
							});	
							
						$("#invoice_creditdialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								width:700,
								hide:"fade",
								title:"Credit Note",
								open: function(event, ui){
									
								},
								buttons: {
									"Print": function() {
										$(this).dialog("close");

										printCredit();
									},
									"Save": function() {
										saveCredit();
									},
									"Save & Close": function() {
										$(this).dialog("close");
										
										saveCredit();
									},
									Cancel: function() {
										$(this).dialog("close");
									}
								}
							});	
							
						$("#invoice_descriptiondialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								hide:"fade",
								width: 800,
								title:"Description",
								open: function(event, ui){
									
								},
								buttons: {
									Ok: function() {
										$(this).dialog("close");
									}
								}
							});	
							
						$("#invoicedialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								hide:"fade",
								width: 850,
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
								width: 450,
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
