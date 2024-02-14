#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($username,$password)
{
	include "mysqlconnect.php";
	$query = "SELECT * from accounts WHERE
		username = '$username' AND password = '$password'";
	$result = $mydb->query($query);
	if ($result->num_rows == 1){
		echo "success";
		return array ("destination" => 'frontend', 'username' => $username, 'message' => "Account found");
	}else{
		echo "failed";
		return array ("destination" => 'frontend', 'username' => NULL, 'message' => "Account not found");
	}	
}

function requestProcessor($request)
{
  echo "------------------------".PHP_EOL;
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "Login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>

