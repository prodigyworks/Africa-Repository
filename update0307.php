<?php
	require_once("system-db.php");
	
	start_db();
	
	function alter($sql) {
		echo "<p>$sql</p>";
		$result = mysql_query($sql);
		
		if (! $result) {
			echo "<h1>Error : " . mysql_error() . "</h1>";
			logError(mysql_error(), false);
		}
	}
	/*
	 * 
1.	Assign Office users to a province, they may only see cases from the province they are assigned to. This is only applicable if they are only office users, if they are office users and accounting users, they may see all cases.
Introduce data filter to restrict case information by province if office user only on all screens and combo box dropdowns. 6 hours. - DONE
2.	Rate – On the case edit screen and on the typist invoice screen, please show the actual rate amount in brackets behind the rate name. So in the list of rates, if it shows Urgent, and the amount is R8.50 for that rate, it should display the following in the dropdown : Urgent ( R8.50) .
Show rate in brackets (0.5 hours) - DONE
3.	Rate Dropdown, please hide all items in the rate dropdown until a specific Client or Court has been chosen, if the office users go to rate before they choose a client it shown all of the rate for all clients/provinces, and this confuses them.
Restrict rate combo options until client or court has been entered. 1.5 hours - DONE
4.	Only allow admin users to create courts, however office users should still be able to create private clients.
Restrict creation process for roles. 1 hour. - DONE
5.	On the normal case view, where it shows all the cases, currently it’s arranged by Province, please rather set it to arrange by date, with the newest cases on top.
Change sequence of list. 0.5 hour
6.	On the court/client dropdown when creating or editing a case, can you group it so all the courts are together, and then all private clients below the courts. As per point 1 on this page, users should only be able to see courts in this list that are in their province.
Restriction will be made from point 1. Sequencing change. 0.5 hour - DONE
7.	Typist statement – I attached a pdf called typiststatement, please do the updates as per the comments on there, updates should be done both on the Excel file and PDF.
Amendments regarding totals and highlights. 1.5 hour - DONE
8.	Typist invoices hide – On the typist view, please hide all typist invoices for which an invoice to the client has been created.
Hide row rather than show tick. 1 hour - DONE
9.	Print typist invoice – when a case is highlighted we would like a Typist Invoice button, when clicked this button should print a Typist Invoice in pdf for this case, in case there is more than one typist, it should give a dropdown asking for which typist the Typist Invoice needs to be printed, please see attached TypistInvoice file for a sample invoice( I have attached an Excel file, please make the Typist Invoice print to PDF).
New typist invoice and button / lookup filter. 8 hours – On hold
10.	Running – when adding a session to the running, it automatically enters the amount of pages, I think it gets it from the first session created for that running. Please see screenshot : 

	 */
	alter("ALTER TABLE africatranscriptions_members ADD COLUMN provinceid INT(11) NULL DEFAULT NULL AFTER officeid;");
	alter("CREATE view africatranscriptions_allcourts AS select * from africatranscriptions_courts;");
	alter("ALTER TABLE africatranscriptions_invoiceitemtemplates ADD COLUMN displayname VARCHAR(60) NULL DEFAULT NULL;");
	alter("CREATE TRIGGER ratecolumn_insert BEFORE INSERT ON africatranscriptions_invoiceitemtemplates\nFOR EACH ROW BEGIN\nSET NEW.displayname = CONCAT(NEW.name, ' (R ', NEW.courtprice, ')'); END");
	alter("CREATE TRIGGER rate_update BEFORE UPDATE ON africatranscriptions_invoiceitemtemplates FOR EACH ROW BEGIN  SET NEW.displayname = CONCAT(NEW.name, ' (R ', NEW.courtprice, ')'); END");
	alter("UPDATE africatranscriptions_invoiceitemtemplates SET displayname = CONCAT(name, ' (R ', courtprice, ')');");
	alter("create view africatranscriptions_casetypistwithsession AS  select B.*, (SELECT sessionid FROM africatranscriptions_casetypistsessions CS WHERE CS.casetypistid = B.id AND (CS.pages IS NULL OR CS.pages = 0) LIMIT 1) AS sessionid FROM  africatranscriptions_casetypist B");
?>
