<?php

require 'init.php';

$selected_tags = array();

include 'templates/htmlhead.php';





if (isset($_POST['newappointment'])){
	$app=parseAppointmentData($_POST['newappointment']);
	if ($app){
		$app->save();
		$tags=explode(' ',$_POST['newappointment']['tags']);
		foreach ($tags as $tag){
			$app->addTag($tag);
		}		
	}
}

if (isset($_POST['editappointment'])){
	$app=parseAppointmentData($_POST['editappointment']);
	if ($app){
		$app->save();
	}
}

if (isset($_GET['tag'])){
	$selected_tags[]=$_GET['tag'];
}

if (isset($_GET['show'])){
	$app_id=$_GET['show'];
	$appointment=appointment::load($app_id);
	include 'templates/detail.php';
} else if (isset($_GET['edit'])) {
	$app_id=$_GET['edit'];
	$appointments = appointment::loadAll($selected_tags);
	$appointment=$appointments[$app_id];
	include 'templates/editdateform.php';
	include 'templates/overview.php';
} else if (isset($_GET['delete'])){
	$app_id=$_GET['delete'];
	if (isset($_GET['confirm']) && $_GET['confirm']=='yes'){
		$appointment=appointment::delete($app_id);	
		$appointments = appointment::loadAll($selected_tags);
	} else {
		$appointments = appointment::loadAll($selected_tags);
		$appointment=$appointments[$app_id];
		include 'templates/confirmdelete.php';
	}
	include 'templates/adddateform.php';
	include 'templates/overview.php';	
} else {
	$appointments = appointment::loadAll($selected_tags);
	include 'templates/adddateform.php';
	include 'templates/overview.php';
}

if (isset($_GET['debug']) && $_GET['debug']=='true'){
	echo "<textarea>";
	print_r($_POST);
	echo "</textarea>";
	if (isset($appointments)){
		echo "<textarea>";
		print_r($appointments);
		echo "</textarea>";
	}
}

include 'templates/bottom.php';

$db = null; // close database connection
?>
