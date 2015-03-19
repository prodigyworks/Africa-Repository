<?php
require_once('php-sql-parser.php');
require_once('php-sql-creator.php');

function getFilteredData($sql) {
	if (! isset($_SESSION['SITE_CONFIG'])) {
		return $sql;
	}
	
	$parser = new PHPSQLParser($sql);
	$tablealias = null;
	$data = getSiteConfigData();
	
	if (isUserInRole("ADMIN") ||
		isUserInRole("STATEMENTS")) {
		/* Do nothing, access rights to all. */
		return $sql;
	}
	
	foreach ($parser->parsed['FROM'] as $table) {
		if ($table['table'] == "africatranscriptions_courts") {
			if ($table['alias'] != "") {
				$tablealias = $table['alias']['name'];
				
			} else {
				$tablealias = $table['table'];
			} 
		}
	}
	
//	echo $sql . "\n";
//	print_r($parser->parsed);
	
	if (! isset($parser->parsed['WHERE'])) {
		/* Create where clause. */
		$parser->parsed['WHERE'] = array();
					
	} else {
		/* Add to the where clause. */
		$parser->parsed['WHERE'][] = 
				array(
						"expr_type" 		=> "operator",
						"base_expr"			=> "AND",
						"sub_tree"			=> ""
					);
	}
					
	if ($tablealias != null && isUserInRole("OFFICE") && isset($_SESSION['SESS_PROVINCEID']) && $_SESSION['SESS_PROVINCEID'] != "" && $_SESSION['SESS_PROVINCEID'] != "0") {
		/* Restricted to.
		 */ 
		
		$parser->parsed['WHERE'][] =
			array(
					"expr_type" 		=> "bracket_expression",
					"sub_tree"			=> array(
							array(
									"expr_type" 		=> "colref",
									"base_expr"			=> $tablealias . ".provinceid",
									"sub_tree"			=> ""
							),
							array(
									"expr_type" 		=> "operator",
									"base_expr"			=> "=",
									"sub_tree"			=> ""
							),
							array(
									"expr_type" 		=> "const",
									"base_expr"			=> $_SESSION['SESS_PROVINCEID'],
									"sub_tree"			=> ""
							),
							array(
									"expr_type" 		=> "operator",
									"base_expr"			=> "OR",
									"sub_tree"			=> ""
							),
							array(
									"expr_type" 		=> "colref",
									"base_expr"			=> $tablealias . ".vatapplicable",
									"sub_tree"			=> ""
							),
							array(
									"expr_type" 		=> "operator",
									"base_expr"			=> "=",
									"sub_tree"			=> ""
							),
							array(
									"expr_type" 		=> "const",
									"base_expr"			=> "'Y'",
									"sub_tree"			=> ""
							)
					)
			);
		
	} else {
		return $sql;
	}
	
	$creator = new PHPSQLCreator($parser->parsed);
	$created = $creator->created;			
	
	return $created;
}
?>