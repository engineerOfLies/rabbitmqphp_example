#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$db = new mysqli('127.0.0.1','dbManager','shackle','mainDB');

if ($db->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}
echo "successfully connected to database".PHP_EOL;

function doLogin($username,$password,$sessid)
{
    global $db;
    $query = "select * from Users where username='{$username}';";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return array("result"=>'0',"msg"=>"Error executing query.");
	}
    if ($sqlResponse->num_rows == 0){
    	$errmsg = "Username not found.";
    	return array("result"=>'0',"msg"=>$errmsg);
    }
    $row = $sqlResponse->fetch_assoc();
    if($row["password"] != $password){
    	$errmsg = "Incorrect password.";
    	return array("result"=>'0',"msg"=>$errmsg);
    }
    $success = createSession($row["userid"], $sessid);
    if ($success)
    	return array("result"=>'1'/*,"uid"=>$row["userid"]*/);
    return array("result"=>'0',"msg"=>"Error registering session.");
}
function doRegister($username, $password, $email){
	global $db;
	$query = "insert into Users (username, email, password) values ('{$username}', '{$email}', '{$password}');";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	return true;
}

function createSession($uid, $sessid){
	global $db;
	$query = "insert into Sessions (sessionid, userid) values ('{$sessid}','{$uid}');";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	return true;
}
function validateSession($sessid){
	global $db;
	$query = "select * from Sessions where sessionid='{$sessid}';";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
    if ($sqlResponse->num_rows == 0){
    	//session does not exist.
    	return false;
    }
	if($row["isactive"] == 0){
    	//session has already been logged out.
    	return false;
    }
	return true;
}
function logout($sessid){
	global $db;
	$query = "update Sessions set isactive='0' where sessionid='{$sessid}';";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	return true;
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password'],$request['sessionId']);
    //case "create_session":
    //  return createSession($request['userId'], $request['sessionId']);
    case "validate_session":
      return validateSession($request['sessionId']);
    case "logout":
      return logout($request['sessionId']);
    case "register":
      return doRegister($request['username'],$request['password'],$request['email']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","databaseServer");	//For actual running.
//$server = new rabbitMQServer("testRabbitMQ.ini","testServer");	//For testing on localhost.

$server->process_requests('requestProcessor');
exit();
?>

