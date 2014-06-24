<?php

require 'init.php';

$selected_tags = array();

include 'templates/head.php';

/* if data for a new appointment is recieved, handle it */
if (isset($_POST['newappointment'])){
	$appointment=parseAppointmentData($_POST['newappointment']); // create new appointment
	if ($appointment){
		$appointment->save(); // save new appointment
		$tags=explode(' ',$_POST['newappointment']['tags']);
		foreach ($tags as $tag){
			$appointment->addTag($tag); // add tags
		}		
	} else { // if appointment data is invalid
		unset($_POST['addsession']); // do not add sessions
		unset($_POST['addlink']); // do not add links
	}	
}

/* if sessiondata is provided: create session */
if (isset($_POST['newsession'])){
	$session=parseSessionData($_POST['newsession']); // try to create session
	if ($session){ // if successfull:
		$session->save(); // save session
		$appointment=appointment::load($session->aid);		
	}
}

/* if linkdata is provided: create session */
if (isset($_POST['newlink'])){
	$link=parseLinkData($_POST['newlink']); // try to create session
	if ($link){ // if successfull:
		$link->save(); // save session
		$appointment=appointment::load($link->aid);
		$appointment->addUrl($link);
	}
}

/* if edited appointment data is provided: save! */
if (isset($_POST['editappointment'])){
	$appointment=parseAppointmentData($_POST['editappointment']);
	if ($appointment){
		$appointment->save();
		$appointment->removeAllTags();		
		$tags=explode(' ',$_POST['editappointment']['tags']);
		foreach ($tags as $tag){						
			$appointment->addTag($tag); // add tags
		}		
	}	
	$appointment->loadRelated();
}

/* if a tag is provided: use it */
if (isset($_GET['tag'])){
	$selected_tags[]=$_GET['tag'];
}

/* session shall be deleted. */
if (isset($_GET['deletesession'])){
	$sid=$_GET['deletesession'];
	session::delete($sid);
}

/* link shall be removed from appointment */
if (isset($_GET['deletelink'])){
	$uid=$_GET['deletelink'];
	$aid=$_GET['show'];
	$appointment=appointment::load($aid);
	$appointment->removeUrl((int)$uid);
}

if (isset($_POST['addsession'])){
	include 'templates/addsession.php';
	include 'templates/detail.php';	
	
} else if (isset($_POST['addlink'])){
	include 'templates/addlink.php';
	include 'templates/detail.php';	
	
} else if (isset($_GET['show'])){
	$app_id=$_GET['show'];
	$appointment=appointment::load($app_id);
	include 'templates/detail.php';
	
} else if (isset($_POST['clone'])) {
	$app_id=$_POST['clone'];
	$appointment=appointment::load($app_id);
	unset($appointment->id);
	$appointment->save();
	foreach ($appointment->urls as $url){
		$url->aid=$appointment->id;
		$appointment->addUrl($url);
	}
	foreach ($appointment->tags as $tag){
		$tag->aid=$appointment->id;
		$appointment->addTag($tag);
	}	
	$appointments = appointment::loadCurrent($selected_tags);
	include 'templates/editdateform.php';
	include 'templates/overview.php';

} else if (isset($_POST['edit'])) {
	$app_id=$_POST['edit'];
	$appointments = appointment::loadAll($selected_tags);
	$appointment=$appointments[$app_id];
	include 'templates/editdateform.php';
	include 'templates/overview.php';
	
} else if (isset($_POST['delete'])){
	$app_id=$_POST['delete'];
	if (isset($_POST['confirm'])){
		if ($_POST['confirm']=='yes'){
			$appointment=appointment::delete($app_id);	
		}
		$appointments = appointment::loadCurrent($selected_tags);
		include 'templates/adddateform.php';
		include 'templates/overview.php';
	} else {
		$appointment=appointment::load($app_id);
		include 'templates/confirmdelete.php';
		include 'templates/detail.php';
	}
	
} else if (isset($_GET['past'])){
	$appointments = appointment::loadAll($selected_tags);
	include 'templates/adddateform.php';
	include 'templates/overview.php';	
} else {
	$appointments = appointment::loadCurrent($selected_tags);
	include 'templates/adddateform.php';
	include 'templates/overview.php';
}

if (isset($_SESSION['debug']) && $_SESSION['debug']=='true'){
	echo "<textarea>";
	print_r($_POST);
	echo "</textarea>";
	if (isset($appointments)){
		echo "<textarea>";
		print_r($appointments);
		echo "</textarea>";
	}
	if (isset($appointment)){
		echo "<textarea>";
		print_r($appointment);
		echo "</textarea>";
	}
} 

include 'templates/bottom.php';

$db = null; // close database connection
?>
