<?php 

//---------------------------------------------------------------------------------------------------------------
// showDuggaservice - Retrieve duggor, services save and update duggor
//---------------------------------------------------------------------------------------------------------------

date_default_timezone_set("Europe/Stockholm");

// Include basic application services!
include_once "../Shared/sessions.php";
include_once "../Shared/basic.php";

// Connect to database and start session
pdoConnect();
session_start();

if(isset($_SESSION['uid'])){
	$userid=$_SESSION['uid'];
}else{
	$userid="1";		
} 

$opt=getOP('opt');
$courseid=getOP('courseid');
$coursevers=getOP('coursevers');
$duggaid=getOP('did');
$moment=getOP('moment');
$answer=getOP('answer');

$debug="NONE!";	


$hr=false;
//------------------------------------------------------------------------------------------------
// Services
//------------------------------------------------------------------------------------------------

if(checklogin()){
	$query = $pdo->prepare("SELECT visibility FROM course WHERE cid=:cid");
	$query->bindParam(':cid', $courseid);
	$result = $query->execute();

	if($row = $query->fetch(PDO::FETCH_ASSOC)){
		$hr = ((checklogin() && hasAccess($userid, $courseid, 'r')) || $row['visibility'] != 0);
		if($hr&&$userid!="UNK"){
			// The code for modification using sessions
			if(strcmp($opt,"SAVDU")===0){
				// Log the dugga write
				makeLogEntry($userid,2,$pdo,$courseid." ".$coursevers." ".$duggaid." ".$moment." ".$answer);

				//Seperate timeSpent from $answer
				$temp = explode("-", $answer);
				$timeSpent = $temp[1];
				$answer = $temp[0];

				// check if the user already has a grade on the assignment
				$query = $pdo->prepare("SELECT grade from userAnswer WHERE uid=:uid AND cid=:cid AND moment=:moment AND vers=:coursevers;");
				$query->bindParam(':cid', $courseid);
				$query->bindParam(':coursevers', $coursevers);
				$query->bindParam(':uid', $userid);
				$query->bindParam(':moment', $moment);

				$query->execute();

				if($row = $query->fetch(PDO::FETCH_ASSOC)){
					$grade=$row['grade'];
				}
				if(($grade == 2) || ($grade == 3)||($grade == 4) || ($grade == 5)||($grade == 6)){
					//if grade equal G, VG, 3, 4, 5, or 6
					$debug="You have already been graded on this assignment";
				}else{
					// Update Dugga!
					$query = $pdo->prepare("UPDATE userAnswer SET useranswer=:useranswer, timeSpent=:timeSpent WHERE uid=:uid AND cid=:cid AND moment=:moment AND vers=:coursevers;");
					$query->bindParam(':cid', $courseid);
					$query->bindParam(':coursevers', $coursevers);
					$query->bindParam(':uid', $userid);
					$query->bindParam(':moment', $moment);
					$query->bindParam(':useranswer', $answer);
					$query->bindParam(':timeSpent', $timeSpent);
				}

				if(!$query->execute()){
					$debug="Error updating answer";
				}
			}
		}
	}
}

//------------------------------------------------------------------------------------------------
// Retrieve Information			
//------------------------------------------------------------------------------------------------

if($hr&&$userid!="UNK"){
	// See if we already have a result i.e. a chosen variant.
	$query = $pdo->prepare("SELECT aid,cid,quiz,useranswer,variant,moment,vers,uid,marked FROM userAnswer WHERE uid=:uid AND cid=:cid AND moment=:moment AND vers=:coursevers;");
	$query->bindParam(':cid', $courseid);
	$query->bindParam(':coursevers', $coursevers);
	$query->bindParam(':uid', $userid);
	$query->bindParam(':moment', $moment);
	$result = $query->execute();

	$savedvariant="UNK";
	$newvariant="";
	$variants=array();
	$savedanswer="UNK";

	if ($row = $query->fetch(PDO::FETCH_ASSOC)){
		$savedvariant=$row['variant'];
		$savedanswer=$row['useranswer'];
	}

	// Retrieve variant list
	$query = $pdo->prepare("SELECT vid,param FROM variant WHERE quizID=:duggaid;");
	$query->bindParam(':duggaid', $duggaid);
	$result=$query->execute();

	if(!$result) err("SQL Query Error: ".$pdo->errorInfo(),"Field Querying Error!");
	$i=0;
	

	foreach($query->fetchAll() as $row){
		$variants[$i]=array(
			'vid' => $row['vid'],
			'param' => $row['param']
			);
		$i++;
	}

	// If there are any variants, randomize
	if($savedvariant==""||$savedvariant=="UNK"){
		$randomno=rand(0,sizeof($variants)-1);
		if(sizeof($variants)>0){ 
			$newvariant=$variants[$randomno]['vid'];
		}
	}

	// Savedvariant now contains variant (from previous visit) "" (null) or UNK (no variant inserted)
	if(($savedvariant=="") && ($newvariant!="")){
		$query = $pdo->prepare("UPDATE userAnswer SET variant=:variant WHERE uid=:uid AND cid=:cid AND moment=:moment AND vers=:coursevers;");
		$query->bindParam(':cid', $courseid);
		$query->bindParam(':coursevers', $coursevers);
		$query->bindParam(':uid', $userid);
		$query->bindParam(':moment', $moment);
		$query->bindParam(':variant', $newvariant);
		
		if(!$query->execute()){
			$error=$query->errorInfo();
			$debug="Error updating entries".$error[2];
		}
		$savedvariant=$newvariant;
	}else if(($savedvariant=="UNK")&&($newvariant!="")){
		$query = $pdo->prepare("INSERT INTO userAnswer(uid,cid,quiz,moment,vers,variant) VALUES(:uid,:cid,:did,:moment,:coursevers,:variant);");
		$query->bindParam(':cid', $courseid);
		$query->bindParam(':coursevers', $coursevers);
		$query->bindParam(':uid', $userid);
		$query->bindParam(':did', $duggaid);
		$query->bindParam(':moment', $moment);
		$query->bindParam(':variant', $newvariant);
		
		if(!$query->execute()){
			$error=$query->errorInfo();
			$debug="Error updating entries".$error[2];
		}
		$savedvariant=$newvariant;
	}

	// Retrieve variant
	$param="UNK";
	foreach ($variants as $variant) {
		if($variant['vid']==$savedvariant){
			$param=$variant['param'];
		}
		    //$debug.=$variant['vid']." ".$savedvariant."\n"; 
	}
}else{
	$param="FORBIDDEN!!";
}

$array = array(
		"debug" => $debug,
		"param" => $param,
		"answer" => $savedanswer
	);

echo json_encode($array);
?>
