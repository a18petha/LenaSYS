<?php
date_default_timezone_set("Europe/Stockholm");

// Include basic application services!
include_once "../Shared/sessions.php";
include_once "../Shared/basic.php";

// Connect to database and start session
pdoConnect();
session_start();

$log_uuid = getOP('log_uuid');
$log_timestamp = getOP('log_timestamp');

logServiceEvent($log_uuid, EventTypes::ServiceClientStart, "forumservice.php", $log_timestamp);
logServiceEvent($log_uuid, EventTypes::ServiceServerStart, "forumservice.php");

if(isset($_SESSION['uid'])){
	$userid=$_SESSION['uid'];
}else{
	$userid="UNK";
}

$cid = getOP('cid');
$uid = getOP('uid');
$opt = getOP('opt');
$threadId = getOP('threadId');
$userID = getOP('userID');
$text = getOP('text');
$courseId = getOP('courseId');
$topicT = getOP('topic');
$descriptionT = getOP('description');

$debug="NONE!";

//------------------------------------------------------------------------------------------------
// Services
//------------------------------------------------------------------------------------------------

if(strcmp($opt,"ACCESSCHECK")===0){
	$threadAccess = null;

	$query = $pdo->prepare("SELECT uid, username, superuser, class FROM user WHERE uid=:userid;");
	$query->bindParam(':userid', $userid);

	if(!$query->execute()){
		$error=$query->errorInfo();
		exit($debug);
	}else{
		$user = $query->fetch(PDO::FETCH_ASSOC);
	}

	$query = $pdo->prepare("SELECT uid, hidden FROM thread WHERE threadid=:threadId;");
	$query->bindParam(':threadId', $threadId);

	if(!$query->execute()){
		$error=$query->errorInfo();
		exit($debug);
	}else{
		$thread = $query->fetch(PDO::FETCH_ASSOC);
	}

	if (!$thread['hidden']){
		$threadAccess = "public";
	}

	if ($thread['uid']===$userid){
		$threadAccess = "op";
	}else {
		// Check if user is super
		if (isSuperUser($userid))
		{
			$threadAccess = "super";
		}else{
			// Check if user is super
			$query = $pdo->prepare("SELECT uid FROM threadaccess WHERE threadid=:threadId AND uid=:userid;");
			$query->bindParam(':threadId', $threadId);
			$query->bindParam(':userid', $userid);

			if(!$query->execute()){
				$error=$query->errorInfo();
				exit($debug);
			}else{
				$return = $query->fetch(PDO::FETCH_ASSOC);

				if ($return)
				{
					$threadAccess = "normal";
				}
			}
		}
	}
}else if(strcmp($opt,"CREATETHREAD")===0){
	$query = $pdo->prepare("INSERT INTO thread (cid, uid, topic, description) VALUES (:courseId, :userID, :topic, :description)");
	$query->bindParam(':courseId', $courseId);
	$query->bindParam(':userID', $userID);
	$query->bindParam(':topic', $topicT);
	$query->bindParam(':description', $descriptionT);

	if(!$query->execute()){
		$error=$query->errorInfo();
		exit($debug);
	}else{
		$thread = $query->fetch(PDO::FETCH_ASSOC);
	}
}else if(strcmp($opt,"MAKECOMMENT")===0)
{
	$query = $pdo->prepare("INSERT INTO threadcomment (threadid, uid, text) VALUES (:threadID, :userID, :text)");
	$query->bindParam(':threadID', $threadId);
	$query->bindParam(':userID', $userID);
	$query->bindParam(':text', $text);
	if(!$query->execute()){
		$error=$query->errorInfo();
		exit($debug);
	}else{
		$comments = $query->fetch(PDO::FETCH_ASSOC);
	}
}

//------------------------------------------------------------------------------------------------
// Retrieve Information
//------------------------------------------------------------------------------------------------

else if(strcmp($opt,"GETTHREAD")===0){
	$query = $pdo->prepare("SELECT * FROM thread WHERE threadid=:threadId");
	$query->bindParam(':threadId', $threadId);

	if(!$query->execute()){
		$error=$query->errorInfo();
		exit($debug);
	}else{
		$thread = $query->fetch(PDO::FETCH_ASSOC);
	}
}else if(strcmp($opt,"GETCOMMENTS")===0){
	$query = $pdo->prepare("SELECT * FROM threadcomment WHERE threadid=:threadId ORDER BY datecreated ASC;");
	$query->bindParam(':threadId', $threadId);

	if(!$query->execute()){
		$error=$query->errorInfo();
		exit($debug);

	}else{
		$comments = $query->fetchAll(PDO::FETCH_ASSOC);
	}
}

$array = array(
	'thread' => $thread,
	'comments' => $comments,
	'user' => $user,
	'threadAccess' => $threadAccess
	);

echo json_encode($array);
logServiceEvent($log_uuid, EventTypes::ServiceServerEnd, "forumservice.php");
?>
