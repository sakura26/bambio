<?php
include_once "config.php";
include_once "secret.php";

include_once "functions.php";
include_once "class.event.php";
include_once "class.user.php";

//init
session_start();
//set all default user/cart data
if (isset($_SESSION["user"])){  
  $user = json_decode($_SESSION["user"], true);
}