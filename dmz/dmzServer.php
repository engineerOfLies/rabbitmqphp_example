#!/usr/bin/php
<?php
require_once(__DIR__.'/../src/include/path.inc');
require_once(__DIR__.'/../src/include/get_host_info.inc');
require_once(__DIR__.'/../src/include/rabbitMQLib.inc');


function check_steam_id($id)
{
  
	if (FALSE)
	{
    //steam states the ID does not exist, or potentially is private?
		echo "Given STEAM id is invalid or inaccessible, user error".php_EOL;
		return false;
	}
  echo "Given STEAM id valid, returning true".php_EOL;
  
  //Steam states the ID is valid, respond to db/Gustavson positively
  //tell db to store the valid steamid
	return true;
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);

  if(!isset($request['type']))
  {
    return "ERROR: Unsupported or Unknown message type";
  }

  switch ($request['type'])
  {
	  case "check_steam_id":
	    return check_steam_id($request['id']);
    //case "get_game_news":
      //return get_game_news:($request[])
      //return a collection of news information based on passed in array of idays


    //case "login":
      //return doLogin($request['username'],$request['password'],$request['sessionId']);
    //case "validate_session":
      //return validateSession($request['sessionId']);
    //case "register":
      //return doRegister($request['username'],$request['password'],$request['email']);
  }
  return array("returnCode" => '0', 'message'=>"DMZ Server received request and processed");
}

$server = new rabbitMQServer(__DIR__.'/../src/include/testRabbitMQ.ini',"dmzServer");	//For actual running.

$server->process_requests('requestProcessor');
exit();
?>