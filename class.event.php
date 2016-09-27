<?php
include_once "functions.php";

//id  ,owner_id, subject, desc ,start_time ,end_time ,location ,location_lat ,location_long ,food_subject ,max_guest ,min_guest ,tags 
//guest_list  {["guest_id", "desc"]}  => event_guest
//TODO: location convert
function event_add ( $event ){
	$conn = db_get();
	$stmt = $conn->prepare("INSERT INTO event (owner_id, subject, `desc`, start_time, end_time, location, location_lat, location_long, food_subject, max_guest, min_guest, tags, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
	$stmt->bind_param("isssssddsiiss", $event["owner_id"], $event["subject"], $event["desc"], $start=dt_epoch($event["start_time"]), $end=dt_epoch($event["end_time"]), $event["location"], $event["location_lat"], $event["location_long"], $event["food_subject"], $event["max_guest"], $event["min_guest"], $event["tags"], dt_now());
	$stmt->execute();
	$id = $stmt->insert_id;
	$conn->close();
	if (isset($event["guest_list"])){
		foreach ($event["guest_list"] as $value){
			event_join($id, $value["guest_id"], $value["desc"]);
		}
	}
	error_log( date('[Y-m-d H:i e] '). "NOTICE: event_add: success(".$id."), subject:".$event["subject"].", ". PHP_EOL, 3, LOGFILE_API);
	return event_get($id);
}

function event_join ( $event_id, $guest_id, $desc ){
	//todo: check event/user exist
	$conn = db_get();
	$stmt = $conn->prepare("INSERT INTO event_guest (event_id, guest_id, `desc`, created_at) VALUES (?, ?, ?, ?);");
	$stmt->bind_param("iiss", $event_id, $guest_id, $desc, dt_now());
	$stmt->execute();
	$id = $stmt->insert_id;
	$conn->close();
	return $id;
}

function event_guest ( $event_id ){
	//todo: check event/user exist
	$conn = db_get();
	$guestpair=null;
	$stmt = $conn->prepare("SELECT * FROM event_guest WHERE event_id=?;");
	$stmt->bind_param("i", $event_id);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($g = $result->fetch_assoc()){
	    $guestpair[]=$g;
	}
	$conn->close();
	return $guestpair;
}

function event_get ( $id ){
	$conn = db_get();
	$event = null;
	$stmt = $conn->prepare("SELECT * FROM event WHERE id=? AND deleted_at IS null;");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	if (!($event = $result->fetch_assoc())) 
	    return null;
	$conn->close();
	$event["guest_list"]=event_guest($id);
	return $event;
}

function event_list (){
	$conn = db_get();
	$events = null;
	$stmt = $conn->prepare("SELECT * FROM event WHERE deleted_at IS null;");
	$stmt->execute();
	$result = $stmt->get_result();
	while (($event = $result->fetch_assoc())) 
	    $events[] = $event;
	$conn->close();
	return $events;
}

function event_list_owner ( $id ){
	$conn = db_get();
	$events = null;
	$stmt = $conn->prepare("SELECT * FROM event WHERE owner_id = ? AND deleted_at IS null;");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	while (($event = $result->fetch_assoc())) 
	    $events[] = $event;
	$conn->close();
	return $events;
}

function event_search_geo ($lat, $long, $radius=5){
	//find upper and lower bound, return a square now
	//http://wp.mlab.tw/?p=2200
	//TODO: use DB to do it
	//http://mysqlserverteam.com/mysql-5-7-and-gis-an-example/
	$lat_diff = $radius/110.574;
	$long_distance = 111.32*cos($lat*pi()/180);
	$long_diff = $radius/$long_distance;
	$n = $lat + abs($lat_diff);
	$s = $lat - abs($lat_diff);
	$e = $long + abs($long_diff);
	$w = $long - abs($long_diff);

	$conn = db_get();
	$events = null;
	$stmt = $conn->prepare("SELECT * FROM event WHERE location_lat > ? AND location_lat < ? AND location_long > ? AND location_long < ? AND deleted_at IS null;");
	$stmt->bind_param("dddd", $s, $n, $w, $e);
	$stmt->execute();
	$result = $stmt->get_result();
	while (($event = $result->fetch_assoc())) 
	    $events[] = $event;
	$conn->close();
	return $events;
}

function event_search_date ($start, $end){
	$conn = db_get();
	$events = null;
	$stmt = $conn->prepare("SELECT * FROM event WHERE start_time >= ? AND end_time <= ? AND deleted_at IS null;");
	$stmt->bind_param("ss", $s=dt_epoch($start), $e=dt_epoch($end));
	$stmt->execute();
	$result = $stmt->get_result();
	while (($event = $result->fetch_assoc())) 
	    $events[] = $event;
	$conn->close();
	return $events;
}

//id  ,owner_id, subject, desc ,start_time ,end_time ,location ,location_lat ,location_long ,food_subject ,max_guest ,min_guest ,tags 
//guest_list  {["guest_id", "desc"]}  => event_guest
/*function event_search ($event_temp){
	$res = null;
	$query_field = null;
	$query_paras = null;
	if (isset($event_temp["id"])){
		$res[] = event_get($event_temp["id"]);
		return $res;
	}
	if (isset($event_temp["start_time"])){
		$query_field[] = "start_time > ?";
		$query_paras[] = $event_temp["start_time"];
	}
	if (isset($event_temp["end_time"])){
		$query_field[] = "end_time < ?";
		$query_paras[] = $event_temp["end_time"];
	}
	if (isset($event_temp["min_guest"])){
		$query_field[] = "min_guest > ?";
		$query_paras[] = $event_temp["min_guest"];
	}
	if (isset($event_temp["max_guest"])){
		$query_field[] = "max_guest < ?";
		$query_paras[] = $event_temp["max_guest"];
	}
	if (isset($event_temp["max_guest"])){
		$query_field[] = "max_guest < ?";
		$query_paras[] = $event_temp["max_guest"];
	}

	$conn = db_get();
	$events = null;
	$stmt = $conn->prepare("SELECT * FROM event WHERE ".$query_field." AND deleted_at IS null;");
	$stmt->bind_param("iiii", $s, $n, $w, $e);
	$stmt->execute();
	$result = $stmt->get_result();
	while (!($event = $result->fetch_assoc())) 
	    $events[] = $event;
	$conn->close();
	return $events;
}*/