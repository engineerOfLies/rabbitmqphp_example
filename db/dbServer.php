#!/usr/bin/php
<?php
require_once(__DIR__.'/../src/include/path.inc');
require_once(__DIR__.'/../src/include/get_host_info.inc');
require_once(__DIR__.'/../src/include/rabbitMQLib.inc');

$db = new mysqli('127.0.0.1','dbManager','shackle','mainDB');

if ($db->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}
echo "successfully connected to database".PHP_EOL;

////	LOGIN/REGISTER FUNCS	////

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
    
    if($row['steamID'] != NULL){
		$importedData = steam_getUserData($row['userid'], $row['steamID']);
		if($importedData)
			echo "Successfully imported user info.".PHP_EOL;
		else
			echo "Unsuccessfully imported user info.".PHP_EOL;
    }
    $sqlResponse->close();
    $db->next_result();
    
    $success = createSession($row["userid"], $sessid);
    if ($success)
    	return array("result"=>'1',"uid"=>$row["userid"]);
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
    	$sqlResponse->close();
    	$db->next_result();
    	return false;
    }
    $row = $sqlResponse->fetch_assoc();
	if($row["isactive"] == 0){
    	//session has already been logged out.
		$sqlResponse->close();
		$db->next_result();
    	return false;
    }
    $sqlResponse->close();
    $db->next_result();
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

////	USER STEAM FUNCS	////

function steam_getUserData($userid, $steamid){
	global $db;
	
	$query = "select lastSync from SteamUsers where userID='{$userid}';";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	if($sqlResponse->num_rows > 0){
		$row = $sqlResponse->fetch_assoc();
		$sync = strtotime($row['lastSync']);
		if(time() - $sync < 300){
			/*echo "Refusing to sync, it has not been 5 minutes.".PHP_EOL;
			$sqlResponse->close();
			$db->next_result();
			return false;*/
		}
	}
    $sqlResponse->close();
    $db->next_result();
	
	$client = new rabbitMQClient("testRabbitMQ.ini","dmzServer");
	$request = array();
	$request['type'] = "get_steam_profile";
	$request['id'] = $steamid;
	$response = $client->send_request($request);
	
	//unset($client);
	
	
	if($response == 0)
		return false;
	
	$un = $response['username'];
	$av = $response['avatar'];
	
	$query = "insert into SteamUsers (userID, steamName, avatar) values ('{$userid}', '{$un}', '{$av}') on duplicate key update steamName='{$un}', avatar='{$av}';";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	if(isset($response['library'])){
		$gamesAdded = array();
		$ulib = $response['library'];
		$query = "";
		$index = 0;
		foreach($ulib as $game){
			$gid = $game['appid'];
			array_push($gamesAdded, $gid);
			$pt = $game['playtime'];
			$query .= "insert into UserGames (userID, gameID, playTime) values ('{$userid}', '{$gid}', '{$pt}') on duplicate key update playTime='{$pt}'; ";
		}
		
		$sqlResponse = $db->multi_query($query);
		
		if ($db->errno != 0)
		{
			echo "failed to execute query:".PHP_EOL;
			echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		}
		/*foreach($gamesAdded as $game){
			
		}*/
	}
	return true;
}
function steam_giveUserData($userid){
	global $db;
	$query = "select steamName, avatar from SteamUsers where userID='{$userid}';";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	if ($sqlResponse->num_rows == 0){
		echo "Invalid userID or user has not set up Steam Link.";
		$sqlResponse->close();
		$db->next_result();
		return false;
	}
	$row = $sqlResponse->fetch_assoc();
	$response = array('steamName' => $row['steamName'], 'avatarLink' => $row['avatar']);
    $sqlResponse->close();
    $db->next_result();
	return $response;
}

function steam_setlink($sessid, $steamid){
	global $db;
	$query = "select userID from Sessions where sessionid='{$sessid}' and isactive='1';";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
    if ($sqlResponse->num_rows == 0){
    	//session does not exist.
		echo "Returning false, since it was not a valid sessionID.".PHP_EOL;
    	return false;
    }
    $row = $sqlResponse->fetch_assoc();
    $uid = $row["userID"];
    $sqlResponse->close();
    $db->next_result();
	
	//check if id is valid
	$client = new rabbitMQClient("testRabbitMQ.ini","dmzServer");
	$request = array();
	$request['type'] = "check_steam_id";
	$request['id'] = $steamid;
	$response = $client->send_request($request);
	
	//unset($client);
	
	
	var_dump($response);
	
	if($response == 0){
		echo "Returning false, since it was not a valid SteamID.".PHP_EOL;
		return false;
	}
    
    //put in database if valid
    $query = "update Users set steamID='{$steamid}' where userid='{$uid}';";
    $sqlResponse = $db->query($query);
    
    if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	$importedData = steam_getUserData($uid, $steamid);
	if($importedData)
		echo "Successfully imported user info.".PHP_EOL;
	else
		echo "Unsuccessfully imported user info.".PHP_EOL;
		
	echo "Returning true.".PHP_EOL;
	
	return true;
}

////	API STEAM FUNCS		////

function refresh_steamtopgames($arr){
	global $db;
	$query = "delete from DailySteamTopGames;";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	$query = "";
	foreach($arr as $game){
		$appid = $arr['appid'];
		$name = $arr['name'];
		$developer = $arr['developer'];
		$price = $arr['price'];
		$price = $price / 100.0;
		$genre = $arr['genre'];
		$tags = $arr['tags'];
		$query .= "insert into DailySteamTopGames (appid, name, developer, price, genre, tags) values ('{$appid}','{$name}','{$developer}','{$price}','{$genre}','{$tags}'); ";
	}
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	echo "Returning true.".PHP_EOL;
	return true;
}

////	BASICS TO RUN	////

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
    case "set_steam_link":
    	return steam_setlink($request['sessionId'],$request['steamId']);
    case "get_steam_profile":
    	return steam_giveUserData($request['userId']);
    case "refresh_steamtopgames":
    	return refresh_steamtopgames($request['games']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer(__DIR__.'/../src/include/testRabbitMQ.ini',"databaseServer");	//For actual running.

//$server = new rabbitMQServer(__DIR__.'/../src/include/testRabbitMQ.ini',"testServer");	//For testing on localhost.

$server->process_requests('requestProcessor');
exit();
?>

