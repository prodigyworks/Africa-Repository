<?php
include ("SimpleImage.php");

function getImageData($name, $maxHeight = 300, $maxWidth = 300) {
	if (! defined('MAX_FILE_SIZE')) {
		define ('MAX_FILE_SIZE', 1024 * 500); 
	}
	
	$imageid = 0;
	 
	// make sure it's a genuine file upload
	if (is_uploaded_file($_FILES[$name]['tmp_name'])) {
	  // replace any spaces in original filename with underscores
	  $filename = str_replace(' ', '_', $_FILES[$name]['name']);
	  // get the MIME type 
	  $mimetype = $_FILES[$name]['type'];
	  
	  if ($mimetype == 'image/pjpeg') {
	  	  $mimetype= 'image/jpeg';
	  }
	  
	  // create an array of permitted MIME types
	  
	  $permitted = array('image/gif', 'image/jpeg', 'image/png', 'image/x-png');
	
	 // upload if file is OK
	 if (in_array($mimetype, $permitted)) {
	 	
	   switch ($_FILES[$name]['error']) {
	     case 0:
	       // get the file contents

	      // Temporary file name stored on the server
//		      $tmpName  = $_FILES[$name]['tmp_name'];  
//		       
//		      // Read the file 
//		      $fp = fopen($tmpName, 'r');
//		      $image = fread($fp, filesize($tmpName));
//		      fclose($fp);
	       
	       // get the width and height
	       $size = getimagesize($_FILES[$name]['tmp_name']);
	       $width = $size[0];
	       $height = $size[1];
	       
	       if ($width > $maxWidth || $height > $maxHeight) {
		       $image = new SimpleImage();
		       $image->load($_FILES[$name]['tmp_name']);
		       
		       if (($width - $maxWidth) > ($height - $maxHeight)) {
			       $image->resizeToWidth($maxWidth);
			       
		       } else {
			       $image->resizeToHeight($maxHeight);
		       }
		       
			   ob_start();
			   $image->output();
			   $binimage = ob_get_clean();
	       		  
	       } else {
		       $binimage = file_get_contents($_FILES[$name]['tmp_name']);
	       }
	       
	       $image = mysql_real_escape_string($binimage);
	       $filename = mysql_escape_string($_FILES[$name]['name']);
	       $description = mysql_escape_string($_POST['description']) ;
	       
			$result = mysql_query("INSERT INTO {$_SESSION['DB_PREFIX']}images " .
					"(description, name, mimetype, image, imgwidth, imgheight, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
					"VALUES " .
					"('$description', '$filename', '$mimetype', '$image', $width, $height, NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")");
					
			if (! $result) {
				throw new Exception("Cannot persist image data ['$filename']:" . mysql_error());
			} 
			
    		$imageid = mysql_insert_id();
		   
          break;
        case 3:
        case 6:
        case 7:
        case 8:
			throw new Exception("Error uploading $filename. Please try again.");

          break;
        case 4:
			throw new Exception("You didn't select a file to be uploaded.");
      }
	 } else {
			throw new Exception("$filename is not an image.");
	 }
	}
	
	return $imageid;
}


function getFileData($name) {
	if (! defined('MAX_FILE_SIZE')) {
		define ('MAX_FILE_SIZE', 1024 * 500); 
	}
	
	$imageid = 0;
	 
	// make sure it's a genuine file upload
	if (is_uploaded_file($_FILES[$name]['tmp_name'])) {
	  // replace any spaces in original filename with underscores
	  $filename = str_replace(' ', '_', $_FILES[$name]['name']);
	  // get the MIME type 
	  $mimetype = $_FILES[$name]['type'];
	
	 // upload if file is OK
	 if ($_FILES[$name]['size'] > 0 && $_FILES[$name]['size'] <= MAX_FILE_SIZE) {
	     	
		   switch ($_FILES[$name]['error']) {
		     case 0:
		       // get the file contents
	
		      // Temporary file name stored on the server
		      $tmpName  = $_FILES[$name]['tmp_name'];  
		       
		      // Read the file 
		      $fp = fopen($tmpName, 'r');
		      $image = fread($fp, filesize($tmpName));
		      fclose($fp);
		       
		       // get the width and height
		       $binimage = file_get_contents($_FILES[$name]['tmp_name']);
		       $image = mysql_real_escape_string($binimage);
		       $size = $_FILES['document']['size'];
		       $filename = mysql_escape_string($_FILES[$name]['name']);
		       
				$result = mysql_query("INSERT INTO {$_SESSION['DB_PREFIX']}documents " .
						"(name, filename, mimetype, image, size, createdby, createddate, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
						"VALUES " .
						"('$filename', '$filename', '$mimetype', '$image', '$size', " . getLoggedOnMemberID() . ", NOW(), NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")");
						
				if (! $result) {   
					throw new Exception("Cannot persist document data ['$filename']:" . mysql_error());
				} 
				
	    		$imageid = mysql_insert_id();
			   
	          break;
	        case 3:
	        case 6:
	        case 7:
	        case 8:
				throw new Exception("Error uploading $filename. Please try again.");
	          break;
	        case 4:
				throw new Exception("You didn't select a file to be uploaded.");
	      }
	      
	    } else {
			throw new Exception("$filename is either too big or not an image.");
	    }
	    
	} else {
		throw new Exception("$name not found");
	}
	
	return $imageid;
}
?>
