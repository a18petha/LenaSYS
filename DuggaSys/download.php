<?php
    /***********************DOCUMENTATION**************************
     * This file should make downloading of all material          *
     * of a singel course doable given we know the courseid       *
     * every file sould be put in one zip file and then downloaded*
     **************************************************************/
		// Used tutorial https://learncodeweb.com/php/select-and-download-multi-files-in-zip-format-with-php/
		include_once "../Shared/basic.php";
		include_once "../Shared/sessions.php";
		session_start();

		pdoConnect(); // Connect to database and start session
		
		// Get real path for our folder
		$cid				= $_SESSION['courseid'];
		$vers 			= $_SESSION('coursevers');
		$courseName	=	'hey';
		$pathToVersionIndependence = "courses/" . $cid . "/versionIndependence/";
		$pathToActiveVersionOfCourse = "courses/" . $cid . "/" . $vers . "/";
		$filename   =   'course/' . $courseName . '/All/files.zip';

		// Enter the name of directory 
		$pathdir = $pathToVersionIndependence;  
  
		// Enter the name to creating zipped directory 
		$zipcreated = $filename; 
  
		// Create new zip class 
		$zip = new ZipArchive; 
   
		if($zip -> open($zipcreated, ZipArchive::CREATE ) === TRUE) { 
      
    // Store the path into the variable 
    $dir = opendir($pathdir); 
       
    while($file = readdir($dir)) { 
        if(is_file($pathdir.$file)) { 
            $zip -> addFile($pathdir.$file, $file); 
        } 
		} 
		
		// Enter the name of directory 2
		$pathdir = $pathToActiveVersionOfCourse;  

		// Store the path into the variable 
		$dir = opendir($pathdir); 

		while($file = readdir($dir)) { 
			if(is_file($pathdir.$file)) { 
					$zip -> addFile($pathdir.$file, $file); 
			} 
	} 

    $zip ->close(); 
} 
		
		?>
<html>
<head>
</head>
<body>
<?php
    echo "<script>window.location.replace('sectioned.php?cid=" . $cid . "&coursevers=" . $vers . "');</script>"; //update page, redirect to "sectioned.php" with the variables sent for course id and version id
?>
</body>
</html>