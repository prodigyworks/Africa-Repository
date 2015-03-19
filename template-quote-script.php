			var currentQuoteID = 0;
			
			function printQuote() {
				window.open("quotereport.php?id=" + currentQuoteID);
			}
			
			function emailQuote() {
				$("#quote_emaildialog").dialog("open");
			}
			
			function quote_paymentnumber_onchange() {
				if ($("#quote_paymentnumber").val() == "") {
					$("#quote_paid").val("N");

				} else {
					$("#quote_paid").val("Y");
				}
			}
			
			function quote_qty_onchange(node) {
				var qty = parseInt($("#quote_qty").val());
				var unitprice = parseFloat($("#quote_quoteitemform #quote_unitprice").val());
				var vatrate = parseFloat($("#quote_quoteitemform #quote_vatrate").val());
				
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
				
				$("#quote_quoteitemform #quote_vatrate").val(new Number(vatrate).toFixed(2));
				$("#quote_quoteitemform #quote_unitprice").val(new Number(unitprice).toFixed(2));
				$("#quote_quoteitemform #quote_qty").val(new Number(qty).toFixed(0));
				$("#quote_quoteitemform #quote_vat").val(new Number(vat).toFixed(2));
				$("#quote_quoteitemform #quote_itemtotal").val(new Number(total).toFixed(2));
			}
			
			function quote_saveHeader() {
				if (! verifyStandardForm("#quote_quoteform")) {
					pwAlert("Invalid form");
					return false;
				}
				
				callAjax(
						"savequote.php", 
						{ 
							caseid: currentID,
							quotenumber: $("#quote_quoteform #quote_quotenumber").val(),
							paymentnumber: $("#quote_quoteform #quote_paymentnumber").val(),
							description: $('#quotedescriptionarea').val(),
							ourref: $("#quote_quoteform #quote_ourref").val(),
							yourref: $("#quote_quoteform #quote_yourref").val(),
							na: $("#quote_na").attr("checked") ? "Y" : "N",
							shippinghandling: 0,
							paymentdate: $("#quote_quoteform #quote_paymentdate").val(),
							quotedate: $("#quote_quoteform #quote_quotedate").val(),
							total: $("#quote_quoteform #quote_total").val(),
							paid: $("#quote_quoteform #quote_paid").val(),
							toaddress: $("#quote_quoteform #quote_toaddress").val(),
							deladdress: "",
							termsid: 0,
							depositamount: $("#quote_quoteform #quote_depositamount").val(),
							contactid: $("#quote_quoteform #quote_contactid").val(),
							officeid: $("#quote_quoteform #quote_officeid").val(),
						},
						function(data) {
							$(".addquote").removeAttr("disabled");

							currentQuoteID = data[0].id;
							
							$("#quote_addquotebutton").show();
							$("#quote_printbutton").show();
							$("#quote_emailbutton").show();

							refreshData();
						}
					);
					
				
			}
			
			function quote_populateTable(data) {
				$("#quote_quoteform #quote_total").val(data[0].headertotal);
				$("#quote_quoteform #quote_depositamount").val(new Number(parseFloat(data[0].headertotal)).toFixed(2));
				quote_shippinghandling_onchange();
											
				var html = "<TABLE width='100%' class='grid list'><?php createHeader(); ?>";
				
				for (var i = 0; i < data.length; i++) {
					var node = data[i];
					
					if (node.name != null) {
						html += "<TR>";
						html += "<TD><img src='images/edit.png'  title='Edit item' onclick='quote_editItem(" + node.id + ")' />&nbsp;<img src='images/delete.png'  title='Remove item' onclick='quote_removeItem(" + node.id + ")' /></TD>";
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
				
				$("#quote_divtable").html(html);
			}
			
			function saveQuoteItem() {
				if (! verifyStandardForm("#quote_quoteitemform")) {
					pwAlert("Invalid form");
					return false;
				}
				
				callAjax(
						"savequoteitem.php", 
						{ 
							id: $("#quote_quoteitemform #quote_itemid").val(),
							quoteid: currentQuoteID,
							qty: $("#quote_quoteitemform #quote_qty").val(),
							unitprice: $("#quote_quoteitemform #quote_unitprice").val(),
							vatrate: $("#quote_quoteitemform #quote_vatrate").val(),
							vat: $("#quote_quoteitemform #quote_vat").val(),
							total: $("#quote_quoteitemform #quote_itemtotal").val(),
							templateid: $("#quote_quoteitemform #quote_templateid").val()
						},
						function(data) {
							quote_populateTable(data);

							refreshData();
						}
					);
					
				return true;
			}
			
			function quote_removeItem(id) {
				if (confirm("You are about to remove this quote item. Are you sure ?")) {
					callAjax(
							"removequoteitem.php", 
							{ 
								id: id,
								quoteid: currentQuoteID
							},
							function(data) {
								quote_populateTable(data);
							}
						);
				}
			}
			
			function quote_editItem(id) {
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.*, C.name FROM <?php echo $_SESSION['DB_PREFIX'];?>quoteitems A LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoiceitemtemplates C ON C.id = A.templateid WHERE A.id = " + id
						},
						function(data) {
							if (data.length == 1) {
								var node = data[0];
								
								$("#quote_quoteitemform #quote_itemid").val(node.id);
								$("#quote_quoteitemform #quote_qty").val(node.qty);
								$("#quote_quoteitemform #quote_unitprice").val(node.unitprice);
								$("#quote_quoteitemform #quote_vat").val(node.vat);
								$("#quote_quoteitemform #quote_itemtotal").val(node.total);
								$("#quote_quoteitemform #quote_templateid").val(node.templateid);

								if (currentVATApplicable == "N") {
									$("#quote_quoteitemform #quote_vatrate").val("0.00");
									$("#quote_quoteitemform #quote_vatrate").attr("disabled", true);
									
								} else {
									$("#quote_quoteitemform #quote_vatrate").attr("disabled", false);
									$("#quote_quoteitemform #quote_vatrate").val(node.vatrate);
								}
								
								$("#quote_quoteitemdialog").dialog("open");
							}
						}
					);
			}
			
			function quote_shippinghandling_onchange() {
				var subtotal = parseFloat($("#quote_quoteform #quote_total").val());
				var depositamount = parseFloat($("#quote_quoteform #quote_depositamount").val());
				
				total = parseFloat((subtotal));
				
				$("#quote_quoteform #quote_acctotal").val(new Number(total).toFixed(2));
				$("#quote_quoteform #quote_depositamount").val(new Number(depositamount).toFixed(2));
			}
			
			function addQuoteItem() {
				$("#quote_quoteitemform #quote_itemid").val("");
				$("#quote_quoteitemform #quote_templateid").val("0");
				$("#quote_quoteitemform #quote_qty").val("1");
				
				if (currentVATApplicable == "N") {
					$("#quote_quoteitemform #quote_vatrate").val("0.00");
					$("#quote_quoteitemform #quote_vatrate").attr("disabled", true);
					
				} else {
					$("#quote_quoteitemform #quote_vatrate").attr("disabled", false);
					$("#quote_quoteitemform #quote_vatrate").val("<?php echo number_format(getSiteConfigData()->vatrate, 2); ?>");
				}
				
				$("#quote_quoteitemform #quote_vat").val("0.00");
				$("#quote_quoteitemform #quote_unitprice").val("0.00");
				$("#quote_quoteitemform #quote_itemtotal").val("0.00");
				
				$('#quote_quoteitemdialog').dialog('open');				
			}
			
			function editQuote(id) {
				currentID = id;

				$("#quote_quoteform #quote_toaddress").val("");
				$("#quote_quoteform #quote_contactid").val("<?php echo getLoggedOnMemberID(); ?>");
				$("#quote_quoteform #quote_officeid").val("<?php echo GetOfficeID(getLoggedOnMemberID()); ?>");
				$("#quote_quoteform #quote_quotenumber").val("");
				$("#quote_quoteform #quote_paymentnumber").val("");
				$("#quote_quoteform #quote_ourref").val("");
				$("#quote_quoteform #quote_yourref").val("");
				$("#quote_quoteform #quote_paymentdate").val("");
				$("#quote_quoteform #quote_quotedate").val("");
				$("#quote_quoteform #quote_total").val("0.00");
				$("#quote_quoteform #quote_acctotal").val("0.00");
				$("#quote_quoteform #quote_depositamount").val("0.00");

				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.*, E.provinceid, A.ourref, A.yourref, DATE_FORMAT(A.paymentdate, '%d/%m/%Y') AS paymentdate2, DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate2, B.id AS itemid, B.qty, B.unitprice, B.vat, B.vatrate, B.total AS itemtotal, C.name, A.depositrequired AS depositamount, D.casenumber, D.j33number, E.vatapplicable, E.name AS courtname, E.address, G.name AS clientcourtname FROM <?php echo $_SESSION['DB_PREFIX'];?>cases D LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>quotes A ON D.id = A.caseid LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>quoteitems B ON B.quoteid = A.id LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>invoiceitemtemplates C ON C.id = B.templateid  INNER JOIN <?php echo $_SESSION['DB_PREFIX'];?>courts E ON E.id = D.courtid LEFT OUTER JOIN <?php echo $_SESSION['DB_PREFIX'];?>courts G ON G.id = D.clientcourtid WHERE D.id = " + id
						},
						function(data) {
							var html = "<TABLE width='100%' class='grid list'><?php createHeader(); ?>";
							
							$(".addquote").attr("disabled", "true");
							
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
												$("#quote_templateid").html(data);
											}
										});
								
									currentQuoteID = node.id;
									currentVATApplicable = node.vatapplicable;
									if (node.description == null || node.description == "") {
										$('#quotedescriptionarea').val(" ");
										
									} else {
										$('#quotedescriptionarea').val(node.description);
									}
									
									$("#quote_quoteform #quote_courtname").text(node.courtname);
									$("#quote_quoteform #quote_casenumber").text(node.casenumber);
									
									if (currentVATApplicable == "Y") {
										$("#quote_quoteform #quote_j33number").text(node.clientcourtname);
										$("#quote_quoteform #quote_j33label").text("Client Court");
										
									} else {
										$("#quote_quoteform #quote_j33number").text(node.j33number);
										$("#quote_quoteform #quote_j33label").text("J33 Number");
									}
									
									if (node.depositamount == null) {
										$("#quote_quoteform #quote_depositamount").val("0.00");
										
									} else {
										$("#quote_quoteform #quote_depositamount").val(node.depositamount);
									}

									if (node.id == null) {
										callAjax(
												"getnextquotenumber.php", 
												{ 
												},
												function(data) {
													if (data.length == 1) {
														if (currentVATApplicable == "N") {
															$("#quote_quoteform #quote_quotenumber").val("Q" + "<?php echo substr(GetOfficeName(getLoggedOnMemberID()), 0, 2);?>" + "C" + data[0].quote );

														} else {
															$("#quote_quoteform #quote_quotenumber").val("Q" + "<?php echo substr(GetOfficeName(getLoggedOnMemberID()), 0, 2);?>" + "A" + data[0].quote );
														}
													}
												}
											);
											
										$("#quote_addquotebutton").hide();
										$("#quote_printbutton").hide();
										$("#quote_emailbutton").hide();
										
										$("#quote_quoteform #quote_toaddress").val(node.address);
										$("#quote_quoteform #quote_paymentnumber").val("");
										$("#quote_quoteform #quote_ourref").val("");
										$("#quote_quoteform #quote_yourref").val("");
										$("#quote_quoteform #quote_na").attr("checked", false);

										$('#quotedescriptionarea').val("");
										$("#quote_quoteform #quote_paymentdate").val("");
										$("#quote_quoteform #quote_quotedate").val("<?php echo date("d/m/Y");?>");
										$("#quote_quoteform #quote_total").val("0.00");
										$("#quote_quoteform #quote_acctotal").val("0.00");
										
									} else {
										$("#quote_addquotebutton").show();
										$("#quote_printbutton").show();
										$("#quote_emailbutton").show();
										
										var depositamount = node.depositamount;
										
										if (depositamount == "") {
											depositamount = 0;
										}
										
										$("#quote_quoteform #quote_total").val(node.total);
										$("#quote_quoteform #quote_acctotal").val(new Number(parseFloat(node.total)).toFixed(2));
										$("#quote_quoteform #quote_toaddress").val(node.toaddress);
										$("#quote_quoteform #quote_contactid").val(node.contactid);
										$("#quote_quoteform #quote_officeid").val(node.officeid);
										$("#quote_quoteform #quote_quotenumber").val(node.quotenumber);
										$("#quote_quoteform #quote_paymentnumber").val(node.paymentnumber);
										$("#quote_quoteform #quote_ourref").val(node.ourref);
										$("#quote_quoteform #quote_yourref").val(node.yourref);
										$("#quote_quoteform #quote_na").attr("checked", node.na == "Y" ? true : false);
										
										if (node.paymentdate2 == "00/00/0000") {
											$("#quote_quoteform #quote_paymentdate").val("");
											
										} else {
											$("#quote_quoteform #quote_paymentdate").val(node.paymentdate2);
										}
										
										$("#quote_quoteform #quote_quotedate").val(node.createddate2);
									}
									
									$(".addquote").removeAttr("disabled");
								}
								
								if (node.name != null) {
									html += "<TR>";
									html += "<TD><img title='Edit item' src='images/edit.png' onclick='quote_editItem(" + node.itemid + ")' />&nbsp;<img src='images/delete.png'  title='Remove item' onclick='quote_removeItem(" + node.itemid + ")' /></TD>";
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

							$("#quote_divtable").html(html);
							$("#quote_quotedialog").dialog("open");
						}
					);
			}
			
			function showQuoteDescription() {
				$("#quotedescriptionareadialog").dialog("open");
			}
			
			function quote_toaddress_onchange() {
			}
			
			function quote_validateForm() {
				return true;
			}
			
			$(document).ready(
					function() {
						$("#quote_templateid").change(
								function() {
									callAjax(
											"finddata.php", 
											{ 
												sql: "SELECT clientprice, courtprice FROM <?php echo $_SESSION['DB_PREFIX'];?>invoiceitemtemplates C WHERE id = " + $("#quote_templateid").val()
											},
											function(data) {
												if (data.length == 1) {
													var node = data[0];
													
													if (currentVATApplicable == "N") {
														$("#quote_unitprice").val(node.courtprice).trigger("change");
														
													} else {
														$("#quote_unitprice").val(node.clientprice).trigger("change");
													}
												}
											}
										);
								}
							);
							
						$("#quotedescriptionareadialog").dialog({
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
							
						$("#quote_quotedialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								hide:"fade",
								width: 850,
								title:"Quote",
								open: function(event, ui){
									
								},
								buttons: {
									"Close": function() {
										$(this).dialog("close")
									}
								}
							});		
												
						$("#quote_emaildialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								hide:"fade",
								width: 600,
								title:"Quote",
								open: function(event, ui){
									
								},
								buttons: {
									"Send": function() {
										$(this).dialog("close")
																			
										callAjax(
												"emaildocument.php", 
												{ 
													id: currentQuoteID,
													emailaddress: $("#quote_emailaddress").val()
												},
												function(data) {
													pwAlert("Email sent to " + $("#quote_emailaddress").val());
												}
											);
									},
									"Close": function() {
										$(this).dialog("close")
									}
								}
							});		
													
						$("#quote_quoteitemdialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: false,
								width: 450,
								hide:"fade",
								title:"Quote Item",
								open: function(event, ui){
									
								},
								buttons: {
									"Save": function() {
										if (saveQuoteItem()) {
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
