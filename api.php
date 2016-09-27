<?php
include 'main.php';
//TODO: permission

if (isset($_GET["fun"]))
	$function = $_GET["fun"];
else
	json_error("function not match");


//===========user=============
//function user_add ( $user ){
//function user_add_imei ( $imei, $nickname="NoName" ){
//function user_get ( $id ){
if ($function == "user_add"){
	$q = $_POST["user"];
	if (isset($q))
		$new_user = json_decode($q,true);
	else
		json_error("user data not given");
	if (isset($new_user)){
		$res = user_add($new_user);
		if ($res == null)
			json_error("User insert fail");
		else
			json_success();
	}
	else{
		json_error("User data corrupted");
	}
	json_error("err...?");
}

if ($function == "user_add_imei"){
	$imei = $_GET["IMEI"];
	$nickname = $_GET["nickname"];
	if (isset($imei) && isset($nickname)){
		$res = user_add_imei($imei, $nickname);
		if ($res == null)
			json_error("User insert fail");
		else
			json_success();
	}
	else{
		json_error("User data corrupted");
	}
	json_error("err...?");
}

if ($function == "user_get"){
	//TODO: permission
	$q = $_GET["id"];
	if (isset($q)){
		$user = user_get($q);
		if ($user!=null){
			json_success_array($user);
		}
		json_error("user not found");
	}
	else{
		json_error("user id not given");
	}
}

//===========login=============
if ($function == "user_login_imei"){
	$q = $_POST["IMEI"];
	if (user_login_imei ( $q ))
		json_success();
	else
		json_error();
}
//todo: email login/ SMS login/ password login


//===========event=============
//function event_add ( $event )
//function event_join ( $event_id, $guest_id, $desc ){
//function event_guest ( $event_id ){
//function event_get ( $id ){
if ($function == "event_add"){
	$q = $_POST["event"];
	if (isset($q))
		$event = json_decode($q,true);
	else
		json_error("event data not given");
	if (isset($event)){
		$res = event_add($event);
		if ($res == null)
			json_error("Event insert fail");
		else
			json_success();
	}
	else{
		json_error("Event data corrupted");
	}
	json_error("err...?");
}

if ($function == "event_get"){
	$q = $_GET["id"];
	if (isset($q)){
		$event = event_get($q);
		if ($event!=null){
			json_success_array($event); 
		}
		json_error("event not found");
	}
	else{
		json_error("event id not given");
	}
}

if ($function == "event_list"){
	$events = event_list();
	echo json_encode($events);
	die();
}

if ($function == "event_list_owner"){
	$id = $_GET["id"];
	$events = event_list_owner( $id );
	echo json_encode($events);
	die();
}

if ($function == "event_join"){
	$event_id = $_GET["event_id"];
	$guest_id = $_GET["guest_id"];
	$desc = $_GET["desc"];
	if (isset($event_id) && isset($guest_id)){
		event_join($event_id, $guest_id, $desc);
		json_success();
	}
	else
		json_error("event/user data not given");
}


//===========search=============
if ($function == "event_search_geo"){
	$lat = $_GET["lat"];
	$long = $_GET["long"];
	if (isset($_GET["radius"]))
		$radius = $_GET["radius"];
	else
		$radius = 5;
	if (isset($lat) && isset($long)){
		$events = event_search_geo($lat, $long, $radius);
		echo json_encode($events);
		die();
	}
	else{
		json_error("geo data not given");
	}
}

if ($function == "event_search_date"){
	if (isset($_GET["start_time"]))
		$start_time = $_GET["start_time"];
	else
		$start_time = time();
	if (isset($_GET["end_time"]))
		$end_time = $_GET["end_time"];
	else
		$end_time = time()+(7 * 24 * 60 * 60);
	//DEBUG
	echo "start:".dt_epoch($start_time)." end:".dt_epoch($end_time)."<br>";
	echo "SELECT * FROM event WHERE start_time >= ? AND end_time <= ? AND deleted_at IS null;<br>";
	if (isset($start_time)){
		$events = event_search_date($start_time, $end_time);
		echo json_encode($events);
		die();
	}
	else{
		json_error("start_time not given");
	}
}

//TODO general search

json_error("api function without match: ".$function);