#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($username,$password)
{
	include "testRabbitMQClient (copy).php"; 
	return $response;
}

function requestProcessor($request)
{
  echo "=======================================".PHP_EOL;
  echo "received request".PHP_EOL;
  echo "---------------------------------------\n";
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "Login":
	    return doLogin($request['username'],$request['password']);
	    break;
    case "validate_session":
	    return doValidate($request['sessionId']);
	    break;
  }
  return $finalResponse;
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");
echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

