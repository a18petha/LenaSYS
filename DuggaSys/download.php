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
		$vers 			= $_SESSION['coursevers'];
		echo "cid: " . $cid . ";vers: " . $vers;
		$courseName	=	'hey';
		$pathToVersionIndependence = '/courses/' . $cid . '/versionIndependence/';
		$pathToActiveVersionOfCourse = '/courses/' . $cid . '/' . $vers. '/';
		echo "pat1: "	.	$pathToVersionIndependence	.	"path2: "	. $pathToActiveVersionOfCourse;
		$filename   =   'course:_' . $courseName . '_All_files.zip';

		
  
		// Enter the name to creating zipped directory 
		$zipcreated = $filename; 
		// Create new zip class 
		$zip = new ZipArchive; 
   
		if($zip -> open($zipcreated, ZipArchive::CREATE ) === TRUE) { 
			chdir('../');
			$currcvd = getcwd();

			
			// Enter the name of directory 
			$pathdir = $currcvd.$pathToVersionIndependence;  


			// Store the path into the variable 
			if ($dir = opendir($pathdir)){ 
				while(false !== $file = readdir($dir)) { 
					if($file != "." && $file	!=	".."	&&	!is_dir($pathdir."/".$file)) { 
							$zip -> addFile($pathdir.$file, $file); 
					} 
				}
				closedir($dir);
			}

			// Enter the name of directory 2
			$pathdir = $currcvd.$pathToActiveVersionOfCourse;  
			
			// Store the path into the variable 
			if($dir = opendir($pathdir)){ 
				while(false !== $file = readdir($dir)) { 
					if($file != "." && $file	!=	".."	&&	!is_dir($pathdir."/".$file)) { 
							$zip -> addFile($pathdir.$file, $file); 
					} 
				} 
				closedir($dir);
			}	

			$zip ->close(); 
			
			//header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			header('Content-Type: application/zip');
			//header("Content-Transfer-Encoding: Binary");
			header('Content-Length: ' . filesize($filename));
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			//while (ob_get_level()){
			//	ob_end_clean();
			//}
			readfile($zip);
			//exit;

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