<?php
echo 'hello;'
    /***********************DOCUMENTATION**************************
     * This file should make downloading of all material          *
     * of a singel course doable given we know the courseid       *
     * every file sould be put in one zip file and then downloaded*
     **************************************************************/
		// Used tutorial https://learncodeweb.com/php/select-and-download-multi-files-in-zip-format-with-php/
		
		
			
				$db         = new PDO('mysql:dbname=lenasys;host=localhost', 'root', 'Hundar');
				$courseName	=	$db->query('SELECT coursename FROM course WHERE cid'.$_SESSION['courseid'].'');
				$filename   =   'course' + $courseName + 'All files.zip';
				
				$fileQry    =   $db->query('SELECT * FROM files WHERE cid'.$_SESSION['courseid'].'');
				 
				$zip = new ZipArchive;

				consol.log ( $courseName +$filename+$fileQry);
				if ($zip->open($filename,  ZipArchive::CREATE)){
						while($row  =   $fileQry->fetch_assoc()){
								$zip->addFile(getcwd().'/'.'uploads/'.$row['filename'], $row['filename']);
						}
						$zip->close();
						header("Content-type: application/zip"); 
						header("Content-Disposition: attachment; filename=$filename");
						header("Content-length: " . filesize($filename));
						header("Pragma: no-cache"); 
						header("Expires: 0"); 
						readfile("$filename");
						unlink($filename);
				}else{
					 echo 'Failed!';
				}
		
		
		?>