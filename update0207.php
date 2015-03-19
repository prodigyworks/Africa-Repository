<?php
	require_once("system-db.php");
	
	start_db();
	
	function alter($sql) {
		$result = mysql_query($sql);
		
		if (! $result) {
			logError(mysql_error());
		}
	}
	/*
	 * 
Introduce new role for court user.
0.5 hours - DONE
Database change to a sub table to the user table, which will associate the user with 1 or more courts.
0.5 hours - DONE
Change the user registration to allow this role be selected when creating a new user and associate with a court(s).
4 hours - DONE
Change the user management to allow courts to be associated with court users.
2 hours - DONE
New report based on invoice statement with specified differences and restrictions to data dependant on user / court associations.
3 hours - DONE
Restriction of menu for court users to only see the statement.
1 hour
	 */
	alter("INSERT INTO africatranscriptions_roles(roleid, systemrole) VALUES ('COURTUSER', 'N')");
	alter("CREATE TABLE africatranscriptions_usercourts (
			id INT(10) NOT NULL AUTO_INCREMENT,
			memberid INT(10) NOT NULL,
			courtid INT(10) NOT NULL,
			metacreateddate DATETIME NOT NULL,
			metacreateduserid INT(11) NOT NULL,
			metamodifieddate DATETIME NOT NULL,
			metamodifieduserid INT(11) NOT NULL,
			PRIMARY KEY (id),
			UNIQUE INDEX memberid_courtid (memberid, courtid)
			);");
?>
