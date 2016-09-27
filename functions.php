<?php
//time
function dt_now() {		//取得現在時間字串
	return date('Y-m-d H:i:s', time());
}
function dt_epoch($epoch){
	return date('Y-m-d H:i:s', $epoch);
}

//db
function db_get() {		//取得資料庫連線
	$conn = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWD, DB_NAME);
	if ($conn->connect_error) 
	    die("Connection failed: " . $conn->connect_error); //TODO: goto error page
	$conn->query(" SET NAMES UTF8;");
	return $conn;
}

//strings
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//pages
function page_goback() {		//回到上一頁
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  die();
}
function page_goto($page) {		//前往某頁面
  header('Location: ' . $page);
  die();
}
function page_error($msg, $goto=null) {		//前往錯誤訊息頁面
	if ($goto==null)
  		header('Location: ' . "error.php?msg=".urlencode($msg));
  	else
  		header('Location: ' . "error.php?msg=".urlencode($msg)."&priv=".urlencode($goto));
  die();
}
function page_errorback($msg) {		//前往錯誤訊息頁面，並附上上一頁連結
	page_error($msg, $_SERVER['HTTP_REFERER']);
}

function json_error($msg=null) {		//回應JSON訊息
	if ($msg==null)
		echo '{"status":"error"}';
	else
		echo '{"status":"error", "message":"'.$msg.'"}';
	die();
}
function json_success($msg=null) {		//回應JSON訊息
	if ($msg==null)
		echo '{"status":"success"}';
	else
		echo '{"status":"success", "message":"'.$msg.'"}';
	die();
}
function json_success_array($data_array, $msg=null) {
	$data_array["status"]="success";
	if ($msg!=null)
		$data_array["message"]=$msg;
	echo json_encode($data_array);
	die();
}