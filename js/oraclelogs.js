var globalAlertCallback = null;

function pwAlert(body, callback) {
	globalAlertCallback = callback;
	
	$(".alertdialogbody").html(body);
	$("#alertdialog").dialog("open");
}

function call(commandName, parameters) {
	if (parameters) {
		for (var param in parameters) {
			$("#" + param).val(parameters[param]);
			
			if (param == "page") {
				$("#commandForm").attr("action", parameters[param]);
			}
		}
	}
	
	setTimeout('document.body.style.cursor = "wait";', 0);
	
	$("#command").val(commandName);
	$("#commandForm").submit();
}

function isDate(txtDate) {     
	var objDate, 	// date object initialized from the txtDate string         
	mSeconds, 		// txtDate in milliseconds         
	day,      		// day         
	month,    		// month         
	year;     		// year     
	
	// date length should be 10 characters (no more no less)     
	
	if (txtDate.length !== 10) {         
		return false;     
	}     
	
	// third and sixth character should be '/'     
	
	if (txtDate.substring(2, 3) !== '/' || txtDate.substring(5, 6) !== '/') {         
		return false;     
	}     
	
	// extract month, day and year from the txtDate (expected format is mm/dd/yyyy)     
	// subtraction will cast variables to integer implicitly (needed     // for !== comparing)     
	
	day = txtDate.substring(0, 2) - 0;      
	month= txtDate.substring(3, 5) - 1; // because months in JS start from 0     
	year = txtDate.substring(6, 10) - 0;     
	
	// test year range     
	
	if (year < 1000 || year > 3000) {         
		return false;     
	}     
	
	// convert txtDate to milliseconds     
	
	mSeconds = (new Date(year, month, day)).getTime();     
	
	// initialize Date() object from calculated milliseconds     
	
	objDate = new Date();     
	objDate.setTime(mSeconds);     
	
	// compare input date and parts from Date() object     
	// if difference exists then date isn't valid     
	
	if (objDate.getFullYear() !== year ||         
		objDate.getMonth() !== month ||         
		objDate.getDate() !== day) {         
			return false;     
	}     
	// otherwise return true     
	return true; 
} 

function isTime(txtDate) {     
	var hour, minutes;     
	
	// date length should be 10 characters (no more no less)     
	
	if (txtDate.length !== 5) {         
		return false;     
	}     
	
	// third and sixth character should be '/'     
	
	if (txtDate.substring(2, 3) !== ':') {         
		return false;     
	}     
	
	hour = txtDate.substring(0, 2);      
	minutes= txtDate.substring(3, 5); 

	if (hour < 0 || hour > 23) {         
		return false;     
	}     
	
	if (minutes < 0 || minutes > 59) {         
		return false;     
	}     

	return true; 
} 

function callAjax(url, postdata, callback, async, error) {
	url = url + "?timestamp=" + new Date();
	
	$.ajax({
			url: url,
			dataType: 'json',
			async: async,
			data: postdata,
			type: "POST",
			error: function(jqXHR, textStatus, errorThrown) {
				if (error) {
					error(jqXHR, textStatus, errorThrown);
				} else {
//					alert("ERROR :" + errorThrown);
//					$("#footer").html(errorThrown);
				}
			},
			success: function(data) {
				if (callback != null) {
					callback(data);
				}
			}
		});
}

function addRow(tableID, cells) {
	  // Get a reference to the table
	  var tableRef;
	  
	  tableRef = document.getElementById(tableID);
	  var body = tableRef.appendChild(document.createElement('tbody')) 
	  var tr = body.appendChild(document.createElement("tr"));

	  // Append a text node to the cell
	  for (var i = 0; i < cells; i++) {
		  var newCell = tr.appendChild(document.createElement("td"));
		  newCell.innerHTML = "<br>";
	  }
}

function envelopeCode(node) {
	$(node).each(function(){
	 	$(this).html(function(index, html) {
		 	output = html.replace(new RegExp("<BR>", 'g'),"\n");
		 	output = output.replace(/^(.*)$/mg, "<li><pre>$1</pre></li>");
	 	});
	 	$(this).replaceWith('<ol class="lncode">'+output+'</ol>');
	 });
	
}

$(document).ready(function() {
	
		envelopeCode(".codepreview pre");
		 
		$("#alertdialog").dialog({
				modal: true,
				autoOpen: false,
				width: 300,
				show:"fade",
				title: "Information",
				hide:"fade",
				buttons: {
					Ok: function() {
						$(this).dialog("close");
						
						if (globalAlertCallback) {
							globalAlertCallback();
						}
					}
				}
			});
	
		$(".grid tbody tr").hover(
				function() {
					$(this).addClass("highlight");
				},
				function() {
					$(this).removeClass("highlight");
				}
			);
		
		try {
			$(".datepicker").datepicker({dateFormat: "dd/mm/yy"});
			
		} catch (error) {}
		
		try {
			$(".timepicker").timepicker();
			
		} catch (error) {}
		
		$('.mega-menu').dcMegaMenu({
			rowItems: '2',
			speed: 'fast',
			effect: 'fade',
			fullWidth: false
		});
		
		$(".entryform input").each(function() {
			$(this).after("<div class='bubble' title='Required field' />");
			$(this).blur(function() {
				if ($(this).attr("required") != null && $(this).attr("required") == "true" && $(this).val() == "") {
					$(this).addClass("invalid");
					$(this).next().css("visibility", "visible");
					
				} else {
					$(this).removeClass("invalid");
					$(this).next().css("visibility", "hidden");
				}
			});

		});
	
		$(".entryform select").each(function() {
			$(this).after("<div class='bubble' title='Required field' />");
			
			$(this).blur(function() {
				if ($(this).attr("required") != null && $(this).attr("required") == "true" && $(this).find("option:selected").text() == "") {
					$(this).addClass("invalid");
					$(this).next().css("visibility", "visible");
					
				} else {
					$(this).removeClass("invalid");
					$(this).next().css("visibility", "hidden");
				}
			});

		});
	
	});

function navigate(url) {
	window.location.href = url;
}

function populateCombo(selectid, data, insertBlank) {
	var select = $(selectid);
	var options = select.attr('options');
	  
    $('option', select).remove();  
    
    if (insertBlank) {
        options[options.length] = new Option("", 0);  
    }
	
    $.each(data, function(index, array) {  
         options[options.length] = new Option(array['name'], array['id']);  
    });  
	
}

function getJSONData(url, selectid, callback) {
	$.ajax({
			url: url,
			dataType: 'json',
			async: false,
			error: function(jqXHR, textStatus, errorThrown) {
				alert("ERROR:" + errorThrown);
			},
			success: function(data) {
				populateCombo(selectid, data);

			 	callback();
			}
		});
}

function verifyStandardForm(form) {
	var isValid = true;
	var firstField = null;
	
	$(form).find("select").each(function() {
			var isRequired = ($(this).attr("required") != null && 
							(($(this).attr("required") == "true") || ($(this).attr("required") == true)));
			
			if ($(this).attr("class") == "multiselect") {
				return;
			}
			
			if (isRequired && $(this).find("option:selected").text() == "") {
				$(this).addClass("invalid");
				$(this).next().css("visibility", "visible");
				isValid = false;
				
				if (firstField == null) {
					firstField = $(this);
				}
				
			} else {
				$(this).removeClass("invalid");
				$(this).next().css("visibility", "hidden");
			}
		});

	$(form).find("input").each(function() {
			var isRequired = ($(this).attr("required") != null && 
							(($(this).attr("required") == "true") || ($(this).attr("required") == true)));
			
			if (isRequired && $(this).val() == "") {
				$(this).addClass("invalid");
				$(this).next().css("visibility", "visible");
				isValid = false;
				
				if (firstField == null) {
					firstField = $(this);
				}
				
			} else {
				$(this).removeClass("invalid");
				$(this).next().css("visibility", "hidden");
			}
		});
	
	if (firstField != null) {
//		$(form).scrollTo(firstField);
		firstField.focus();
	}

	return isValid;
}

function padZero(date) {
	if (("" + date).length == 1) {
		return "0" + date;
	}
	
	return date;
}

//implement JSON.stringify serialization 
JSON.stringify = JSON.stringify || function (obj) {     
	var t = typeof (obj);     
	if (t != "object" || obj === null) {         
		// simple data type         
		if (t == "string") obj = '"'+obj+'"';         
		return String(obj);     
	} else {         
		// recurse array or object         
		var n, v, json = [], arr = (obj && obj.constructor == Array);         
		for (n in obj) {             
			v = obj[n]; 
			t = typeof(v);             
			
			if (t == "string") v = '"'+v+'"';             
			else if (t == "object" && v !== null) v = JSON.stringify(v);             
			
			json.push((arr ? "" : '"' + n + '":') + String(v));         
		}         
		
		return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");     
	} 
};  

if (typeof String.prototype.startsWith != 'function') {
  // see below for better implementation!
  String.prototype.startsWith = function (str){
    return this.indexOf(str) == 0;
  };
}

function daysBetweenDates(from, to) {      
    var frommonth = from.slice(3, 5);
    fromdate = parseInt(fromdate);
    var fromdate = from.slice(0, 2); 
    frommonth = parseInt(frommonth);
    var fromyear = from.slice(6, 10); 
    fromyear = parseInt(fromyear);
    var tomonth = to.slice(3, 5); 
    todate = parseInt(todate);
    var todate = to.slice(0, 2); 
    tomonth = parseInt(tomonth);
    var toyear = to.slice(6, 10); 
    toyear = parseInt(toyear);
    var oneDay = 24*60*60*1000;
    var firstDate = new Date(fromyear,frommonth,fromdate);
    var secondDate = new Date(toyear,tomonth,todate);

    var diffDays = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime())/(oneDay)));
    
    return diffDays;
}

function getWeek(date) {
	var onejan = new Date(date.getFullYear(),0,1);
	
	return Math.ceil((((date - onejan) / 86400000) + onejan.getDay()+1)/7);
}

function dateToDMY(date) {
    var d = date.getDate();
    var m = date.getMonth() + 1;
    var y = date.getFullYear();
    
    return '' + (d <= 9 ? '0' + d : d) + '/' + (m<=9 ? '0' + m : m) + '/' + y;
}