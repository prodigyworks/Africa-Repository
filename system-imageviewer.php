<?php
	require_once('system-db.php');
	
	start_db();

	$id = $_GET['id'];
	
	if(!isset($id)){
	     logError("Please select your image!");
	     
	} else {
		$dirname = "uploads/image$id";
		
		if (is_dir($dirname)) {
			if ($handle = opendir($dirname)) {
			    while (false !== ($entry = readdir($handle))) {
			        if ($entry != "." && $entry != "..") {
			            header("location: " . getSiteConfigData()->domainurl . "/$dirname/$entry");
			        }
			    }
			    closedir($handle);
			}
			
		} else {
			$query = mysql_query(
					"SELECT mimetype, name, image " .
					"FROM {$_SESSION['DB_PREFIX']}images " .
					"WHERE id= ". $id
				);
			$row = mysql_fetch_array($query);
			
			$content = $row['image'];
			$name = $row['name'];
			
			header('Content-type: ' . $row['mimetype']);
			
			try {
				if (! is_dir($dirname)) {
					error_reporting(0);
					
					mkdir($dirname);
					chmod($dirname, 0777);
				}

				file_put_contents("$dirname/$name", ($content));
				
			} catch (Exception $e) {
				logError($e->getMessage(), false);
			}
			
			
		    echo $content;
		}
	}
?> 