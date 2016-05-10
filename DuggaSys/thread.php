 <?php

session_start();
include_once "forumservice.php";

pdoConnect();

if (file_exists("../.git/refs/heads/master")) {
	$versionFile = fopen("../.git/refs/heads/master", "r");
	$version = fgets($versionFile);
	fclose($versionFile);
} else {
	$version = "v0.7+";
}

?>
<!DOCTYPE html>
<html>
<head>
	<link rel="icon" type="image/ico" href="../Shared/icons/favicon.ico"/>
	<meta name="viewport" content="width=device-width, initial-scale=1 maximum-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Course Forum</title>

	<link type="text/css" href="../Shared/css/style.css" rel="stylesheet">
	<link type="text/css" href="../Shared/css/responsive.css" rel="stylesheet">
	<link type="text/css" href="../Shared/css/jquery-ui-1.10.4.min.css" rel="stylesheet">
	<link type="text/css" href="../Shared/css/markdown.css" rel="stylesheet">


	<script src="../Shared/js/jquery-1.11.0.min.js"></script>
	<script src="../Shared/js/jquery-ui-1.10.4.min.js"></script>

	<script src="../Shared/markdown.js"></script>

	<script src="../Shared/dugga.js"></script>
	<script src="forum.js"></script>
</head>
<body>
	<?php
	$noup="COURSE";
	$loginvar="COURSE";
	include '../Shared/navheader.php';
	setcookie("loginvar", $loginvar);
	?>

	<!-- content START -->
	<div id="content">
		<!-- Invisible box for confirming a thread's deletion -->
		<div id='threadDeleteConfirm' class="loginBox" style="display:none">
			<div id='login'>
				<div class='loginBoxheader'>
					<h3>Confirm thread deletion</h3>
					<div onclick="closeWindows()">x</div>
				</div>
				<div class="threadConfirmButtons">
					<input type='button' class='yes-delete-thread' value="Yes, delete thread" onclick="confirmDeleteThread()">
					<input type='button' class='no-delete-thread' value="No, keep thread" onclick="closeWindows()">
				</div>
			</div>
		</div>

		<!-- Section List -->
		<div id='threadHeader'>
			<div id="threadTopicWrapper">
				<div class="threadLockedIcon" style="float:left;"></div>
				<div class="threadTopic"></div>
				<div id="threadOptions">
				  <?php
				  if ($threadAccess==="super" || $threadAccess==="op") {
					echo "<div class='threadDeleteAndEdit' style='float:right;'></div>";
				  }
				  if ($threadAccess==="op" || $threadAccess==="super") {
					echo "<div class='opEditThread' style='float:right;'></div>";
				  }
				  ?>
				</div>
			</div>
      <div id="threadDescr"></div>
			<div id="threadDetails">
				Created <span id="threadDate"></span> by <span id="threadCreator"></span>
			</div>
		</div>

    <div id="threadComments"></div>

    <?php
    if (($threadId && $threadId !== "UNK") && ($threadAccess === "normal" || $threadAccess === "super" || $threadAccess === "op")) {
      echo "<div id='threadMakeComment'>";
      echo  "<div id='makeCommentHeader'>";
      echo    "Comment";
      echo  "</div>";
      echo  "<div id='makeCommentInputWrapper'>";
      include "forumEditor.php";
      echo "<input class='submit-button' id='commentSubmitButton' type='button' value='Submit' onclick='makeComment();'>";
	  echo "<input class='submit-button' id='endReplyButton' style='background-color: #C75050; visibility: hidden;'type='button' value='Cancel Reply' onclick='endReplyComment();'>";
      echo  "</div>";
      echo "</div>";
    }
    ?>

    <!-- Create thread -->
    <div id="createThreadWrapper">
      <div id="createThreadHeader">
        Create thread
      </div>
      <div id="createThreadBody">
        <div id="createThreadFormWrapper">
        <input type="text" name="threadTopic" id="threadTopicInput" placeholder="Topic"></input>

        <?php include "forumEditor.php"; ?>

          <div id="createThreadOptions">
            <div class="createThreadOptionLabel">Course:</div>
            <select class="createThreadOption" id="createThreadCourseList" name="courseList" onchange="updateClassList()"></select>

            <div class="createThreadOptionLabel">Access:</div>
            <div class="createThreadRadioWrapper createThreadOption">
              <input id="threadRadioPublic" class="createThreadRadio" name="threadAccessRadio" type="radio" value="public" onclick="createThreadPublicUI();" checked="checked">
              <label for="threadRadioPublic">Public</label>
            </div>

            <div class="createThreadRadioWrapper createThreadOption">
              <input id="threadRadioPrivate" class="createThreadRadio" name="threadAccessRadio" type="radio" value="private" onclick="createThreadPrivateUI();">
              <label for="threadRadioPrivate">Private</label>
            </div>

            <!-- If thread is private -->
            <div id="createThreadPrivateWrapper">
              <div class="createThreadOptionLabel">Class:</div>
              <select class="createThreadOption" id="createThreadClassList" name="classList" onchange="updateUsersList()"></select>
              <div class="createThreadOptionLabel">User:</div>
              <div class="createThreadOption" id="createThreadUsersWrapper"></div>
            </div>


            <div class="createThreadOptionLabel">Allow comments:</div>
            <div class="createThreadRadioWrapper createThreadOption">
              <input id="threadRadioOpen" class="createThreadRadio" name="threadAllowCommentsRadio" type="radio" value="open" checked="checked">
              <label for="threadRadioOpen">Open</label>
            </div>
            <div class="createThreadRadioWrapper createThreadOption">
              <input id="threadRadioLocked" class="createThreadRadio" name="threadAllowCommentsRadio" type="radio" value="locked">
              <label for="threadRadioLocked">Locked</label>
            </div>
          </div>
          <input id="submitThreadButton" class="submit-button createThreadButton" type="button" value="Submit" onclick="createThread();" style="background-color: rgb(97, 72, 117);">
        </div>
      </div>
    </div>
	</div>

	<!-- version identification -->
	<div id="version" class='version'>Master hash <br /><?php echo $version ?></div>
	<!-- content END -->
	<?php
	include '../Shared/loginbox.php';
	?>

</body>
</html>
