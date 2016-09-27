<?php
include_once "functions.php";

//id, nickname, password, email, cellphone, IMEI, desc, lastlogin_at, created_at, updated_at, deleted_at
//email_verified_at, email_verify, cellphone_verified_at, cellphone_verify
//TODO: cronjob to clean not verified accounts
function user_add ( $user ){
	//check param
	if (isset($user["email"]) && !filter_var($user["email"], FILTER_VALIDATE_EMAIL)){
	  error_log( date('[Y-m-d H:i e] '). "NOTICE: user_add: bad email format " .$user["email"]. PHP_EOL, 3, LOGFILE_API);
	  return null;
	}
	if (isset($user["email"]) && user_get($user["email"]) != null) {
	  error_log( date('[Y-m-d H:i e] '). "NOTICE: user_add: duplicate email " .$user["email"]. PHP_EOL, 3, LOGFILE_API);
	  return null;
	}
	if (isset($user["IMEI"]) && preg_match('/^\d{15}$/', $user["IMEI"]) != 1){
	  error_log( date('[Y-m-d H:i e] '). "NOTICE: user_add: bad IMEI format " .$user["IMEI"]. PHP_EOL, 3, LOGFILE_API);
	  return null;
	}
	if (isset($user["email"]) && user_get($user["IMEI"]) != null) {
	  error_log( date('[Y-m-d H:i e] '). "NOTICE: user_add: duplicate IMEI " .$user["IMEI"]. PHP_EOL, 3, LOGFILE_API);
	  return null;
	}
	if (!isset($user["password"]) || $user["password"]=="")
		$user["password"] = generateRandomString(6);

	$conn = db_get();
	$stmt = $conn->prepare("INSERT INTO user (nickname, password, email, IMEI, `desc`, created_at) VALUES (?, MD5(?), ?, ?, ?, ?);");
	$stmt->bind_param("ssssss", $user["nickname"], $salt_pass = PASSWD_SALT.$user["password"], $user["email"], $user["IMEI"], $user["desc"], dt_now());
	$stmt->execute();
	$id = $stmt->insert_id;
	$conn->close();
	error_log( date('[Y-m-d H:i e] '). "NOTICE: user_add: success($id), email=".$user["email"].", IMEI=".$user["IMEI"]. PHP_EOL, 3, LOGFILE_API);
	return user_get($id);
}

function user_add_imei ( $imei, $nickname="NoName" ){
	$user["IMEI"]=$imei;
	$user["nickname"]=$nickname;
	return user_add($user);
}

function user_get ( $id ){ //user_id/IMEI/email
	$conn = db_get();
	$user = null;
	if (filter_var($id, FILTER_VALIDATE_EMAIL)) {
		$stmt = $conn->prepare("SELECT * FROM user WHERE email=? AND deleted_at IS null;");
		$stmt->bind_param("s", $id);
	}
	else if (preg_match('/^\d{15}$/', $id) == 1){  //IMEI 15digits
		$stmt = $conn->prepare("SELECT * FROM user WHERE IMEI=? AND deleted_at IS null;");
		$stmt->bind_param("s", $id);
	}
	else if (preg_match('/^\d*$/', $id) == 1){  //id
		$stmt = $conn->prepare("SELECT * FROM user WHERE id=? AND deleted_at IS null;");
		$stmt->bind_param("i", $id);
	}
	else{
		//not known format, fail
		return null;
	}
	$stmt->execute();
	$result = $stmt->get_result();
	if (!($user = $result->fetch_assoc())) 
	    return null;
	$conn->close();
	unset($user["password"]);
	unset($user["IMEI"]);
	unset($user["email_verify"]);
	unset($user["cellphone_verify"]);
	return $user;
}

function user_login_imei ( $imei ){
	$u = user_get($imei);
	if ($u == null)
		return false;
	//login success, save session
	$_SESSION["user"] = json_encode($u);
	$user = $u;
	//update lastlogin_at
	$conn = db_get();
	$stmt = $conn->prepare("UPDATE user SET lastlogin_at=? WHERE id=? AND deleted_at IS null;");
	$stmt->bind_param("si", $n = dt_now(), $user["id"]);
	$stmt->execute();
	$conn->close();
	error_log( date('[Y-m-d H:i e] '). "NOTICE: user_login_imei: success(".$user["id"]."), email=".$user["email"].", IMEI=".$user["IMEI"]. PHP_EOL, 3, LOGFILE_API);
	return true;
}