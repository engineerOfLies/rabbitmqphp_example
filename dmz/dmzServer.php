#!/usr/bin/php
<?php
require_once(__DIR__.'/../src/include/path.inc');
require_once(__DIR__.'/../src/include/get_host_info.inc');
require_once(__DIR__.'/../src/include/rabbitMQLib.inc');

function callAPI($url) 
{

	// Create a new cURL resource
	$curl = curl_init();

	if (!$curl) {
		die("Couldn't initialize a cURL handle");
	}

	// Set the file URL to fetch through cURL
	curl_setopt($curl, CURLOPT_URL, $url);

	// Fail the cURL request if response code = 400 (like 404 errors)
	curl_setopt($curl, CURLOPT_FAILONERROR, true);

	// Returns the status code
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	//Supresses output?
	curl_setopt($curl, CURLOPT_HEADER, false);

	// Wait 10 seconds to connect and set 0 to wait indefinitely
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

	// Execute the cURL request for a maximum of 50 seconds
	curl_setopt($curl, CURLOPT_TIMEOUT, 50);

	// Do not check the SSL certificates
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	// Fetch the URL and save the content in $html variable
	$output = curl_exec($curl);

	// Check if any error has occurred
	if (curl_errno($curl))
	{
		echo 'cURL error: ' . curl_error($curl);
	}
	else
	{
		// cURL executed successfully
		//print_r(curl_getinfo($curl));
		// close cURL resource to free up system resources
		curl_close($curl);
		// will display the page contents i.e its html.
		return $output;
	}
}


function check_steam_id($id)
{
  $apikey = '6F640B29184C9FE8394A82EEAEFC9A8B';  //how tf do I store this securely???
  $steamid = 76561198118290580; //get the steamid from wherever else it's needed
  $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=6F640B29184C9FE8394A82EEAEFC9A8B&steamids=$id";

  $profileData= callAPI($url);

  $profileDecode = json_decode($profileData);
  //var_dump($profileDecode);
  foreach($profileDecode as $response=>$obj1)
  {
    foreach($obj1 as $players=>$obj2)
    {
        foreach($obj2 as $hidden=>$obj3)
        {
            foreach($obj3 as $param=>$passedVal)
            {
                if($param == 'communityvisibilitystate' && $passedVal == 3)
                {
                  echo "Given STEAM id valid, returning true".PHP_EOL;
                  return true;
                }
                //echo $passedVal."\n";
            }
        }
    }
  }
  echo "Given STEAM id is invalid or inaccessible, user error".PHP_EOL;
	return false;
}

function get_steam_username($id)
{
  $apikey = '6F640B29184C9FE8394A82EEAEFC9A8B';  //how tf do I store this securely???
  $steamid = 76561198118290580; //get the steamid from wherever else it's needed
  $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=6F640B29184C9FE8394A82EEAEFC9A8B&steamids=$id";

  $profileData= callAPI($url);

  $profileDecode = json_decode($profileData);
  //var_dump($profileDecode);
  foreach($profileDecode as $response=>$obj1)
  {
    foreach($obj1 as $players=>$obj2)
    {
        foreach($obj2 as $hidden=>$obj3)
        {
            foreach($obj3 as $param=>$passedVal)
            {
                if($param == 'personaname' && $passedVal != NULL)
                {
                  echo "Given username is valid, returning true".PHP_EOL;
                  return $passedVal;
                }
            }
        }
    }
  }
  echo "Given username is invalid or inaccessible, user error".PHP_EOL;
	return false;
}

function get_steam_avatar($id)
  {
    $apikey = '6F640B29184C9FE8394A82EEAEFC9A8B';  //how tf do I store this securely???
    $steamid = 76561198118290580; //get the steamid from wherever else it's needed
    $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=6F640B29184C9FE8394A82EEAEFC9A8B&steamids=$id";
  
    $profileData= callAPI($url);
  
    $profileDecode = json_decode($profileData);
    //var_dump($profileDecode);
    foreach($profileDecode as $response=>$obj1)
    {
      foreach($obj1 as $players=>$obj2)
      {
          foreach($obj2 as $hidden=>$obj3)
          {
              foreach($obj3 as $param=>$passedVal)
              {
                  if($param == 'avatarmedium' && $passedVal != NULL)
                  {
                    echo "Given avatar url is valid, returning true".PHP_EOL;
                    return $passedVal;
                  }
              }
          }
      }
    }
    echo "Given avatar url is invalid or inaccessible, id error".PHP_EOL;
    return false;
}


function get_user_library($id)
{
  $apikey = '6F640B29184C9FE8394A82EEAEFC9A8B';  //how tf do I store this securely???
  $steamid = 76561198118290580; //get the steamid from wherever else it's needed
  $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key=6F640B29184C9FE8394A82EEAEFC9A8B&include_played_free_games=true&include_free_sub=true&steamid=$id";

  $appidArray = array();

  $profileData= callAPI($url);

  $profileDecode = json_decode($profileData);
  //var_dump($profileDecode);
  foreach($profileDecode as $response=>$obj1)
  {
    foreach($obj1 as $games=>$obj2)
    {
        foreach($obj2 as $gameIndex=>$obj3)
        {
          if($param == 'appid' && $passedVal != NULL)
          {
            $appidArray[] = $passedVal;
          }
        }
    }
  }

  if(empty($appidArray))
  {
    echo "Library was empty or for loop malformed, error.".PHP_EOL;
    return false;
  }

  echo "Given library was populated, returning array".PHP_EOL;
	return $appidArray;
}

function get_app_info($id)
{
  //Acquire info and tags, Not implemented
	return false;
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
    case "get_steam_username":
      return get_steam_username($request['id']);
    case "get_steam_avatar":
      return get_steam_avatar($request['id']);
    case "get_user_library":
      return get_user_library($request['id']);
      case "get_app_info":
        return get_app_info($request['id']);
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