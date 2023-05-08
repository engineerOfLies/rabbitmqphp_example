#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');



function registerUser($name, $username, $password)
{
    // connect to MySQL database
    $db = mysqli_connect("localhost", "user490", "it490", "userData");

    // check connection
    if (!$db) {
        die("Connection failed: " . mysqli_connect_error());
    }

    echo "DB Connected Successfully";

    // prepare query
    $query = "INSERT INTO users (id, name, username, password) VALUES ('1', '$name', '$username', '$password')";

    // execute query
    if (mysqli_query($db, $query)) {
        return true;
    } else {
      echo "query failed";  
      return false;
    }

    // close database connection
    mysqli_close($db);
}

function loginUser($username, $password)
{
    // connect to MySQL database
    $db = mysqli_connect("localhost", "user490", "it490", "userData");

    // check connection
    if (!$db) {
        die("Connection failed: " . mysqli_connect_error());
    }

    echo "DB Connected Successfully\n\n";

    // prepare query
    //echo "Hitting this point before the query";
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    //echo "Hitting this point after the query";

    // execute query
    $result = mysqli_query($db, $query);  
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);  
    $count = mysqli_num_rows($result);  
    //echo $count;
    //echo "Hitting this echo";
          
	if($count == 1)
	{
    echo "Login successful!\n\n";
    return array(true);
  }else{
    echo "Login failed! homie\n\n";
    return array(false);
  }

    
}

function apiData($data)
{
  $conn = new mysqli('127.0.0.1', 'user490', 'it490', 'movie_website');

  foreach($data as $show)
  {
    $SID = intval($show['id']);
    $name = $show['name'];
    $img = $show['img_url'];
    $desc = $show['description'];
    $genres = $show['genre_ids'];

    $addQuery = "INSERT INTO tv_shows VALUES('$SID', '$name', '$desc', '$img', '$genres')";
    echo $show['name'];
    if(mysqli_query($conn, $addQuery))
    {
      echo "\nShows added to db";
    }else{
      echo "\nFailed to add shows to db";
    }
  }
}

function getAllShows()
{
  $conn = new mysqli('127.0.0.1', 'user490', 'it490', 'movie_website');
  $pullQuery = "SELECT name, description, img_url FROM tv_shows";
  $result = mysqli_query($conn, $pullQuery);

  $output = array();

  if(mysqli_query($conn, $pullQuery))
  {
    echo "\nshows returned";
    for($i = 0; $i < mysqli_num_rows($result); $i++)
    {
      $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
      $output["show".$i] = $row;
    }
  }else{
    echo "\nfuck off didn't work";
    return false;
  }

  return $output;
}

function checkFavorites($UIDX, $PIDX)
{
  $conn = new mysqli('127.0.0.1', 'user490', 'it490', 'movie_website');
  $UID = $UIDX;
  $SID= $SIDX;

  $checkForFavoriteQuery = "SELECT * FROM user_likes WHERE show_id = '$SID' and user_id = '$UID'";
  $result = mysqli_query($conn, $checkForFavoriteQuery);
  $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  $count = mysqli_num_rows($result);
  if($count >= 1)
  {
    return false;
  }
  else
  {
    return true;
  }
}


function addToFavorites($data)
{
  $conn = new mysqli('127.0.0.1', 'user490', 'it490', 'movie_website');
  $name = $data['name'];
  echo $name;
  $UID = $data['uid'];
  $getSID = "SELECT id FROM tv_shows WHERE name = '$name'";
  $result = mysqli_query($conn, $getPID);
  $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  $SID = $row['id'];
  echo "$UID . $SID";
  if(checkFavorites($UID, $SID))
  {
    $addSIDQuery = "INSERT INTO user_likes VALUES ('$UID', '$SID')";
    if(mysqli_query($conn, $addSIDQuery))
    {
      echo "Show added to likes";
    }
    else
    {
      echo "failed to add show into likes!";
    }
  }
  else
  {
    echo "\nunlike triggered\n";
    //delete from likes
    $deleteFromLikesQuery = "Delete FROM user_likes WHERE show_id = '$SID' and user_id = '$UID'";
    if(mysqli_query($conn, $deleteFromLikesQuery))
    {
      echo "\nlike has been removed\n";
    }
    else
    {
      echo "\ncould not remove like from table\n";
    }
  }
  
}

function getFavs($data)
{
  $conn = new mysqli('127.0.0.1', 'user490', 'it490', 'movie_website');
  $UID = $data['uid'];
  $getLikesQuery = "SELECT show_id FROM user_likes WHERE user_id = '$UID'";
  $result = mysqli_query($conn, $getLikesQuery);
  $sids = array();
  for($i = 0; $i < mysqli_num_rows($result); $i++)
  {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $sids["show" . $i] = $row;
  }
  $output = array();
  for($i = 0; $i < count($sids); $i++)
  {
    $thisSID = $sids["show". $i]['show_id'];
    $getShowInfoQuery = "SELECT img_URL, name, description FROM tv_shows WHERE id = '$thisSID'";
    $results = mysqli_query($conn, $getShowInfoQuery);
    $joe= mysqli_fetch_array($results, MYSQLI_ASSOC);
    $output["show" . $i]= $joe;
  }
  return $output;
}

function recommend($data)
{
  $conn = new mysqli('127.0.0.1', 'user490', 'it490', 'movie_website');
  $UID = $data['uid'];
  $getLikesQuery = "SELECT show_id FROM user_likes WHERE user_id = '$UID'";
  $result = mysqli_query($conn, $getLikesQuery);
  $sids = array();
  for($i = 0; $i < mysqli_num_rows($result); $i++)
  {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $sids["show" . $i] = $row;
  }
  $genres = array();
  for($i = 0; $i < count($sids); $i++)
  {
    $thisSID = $sids["show". $i]['show_id'];
    $getShowInfoQuery = "SELECT genre_id FROM tv_shows WHERE id = '$thisSID'";
    $results = mysqli_query($conn, $getShowInfoQuery);
    $joe= mysqli_fetch_array($results, MYSQLI_ASSOC);
    //print_r($joe['color'])
    $genre = $joe['genre_id'];
    //print_r($color);
    $genres["genreoo" . $i]= $joe;
  }
  $newGenres = array();
  

  $newGenres = array_values($genres);

  $GENRES = array_unique($newGenres, SORT_REGULAR);
  $newGENRES = array_values($GENRES);
  //print_r($colors);
  //print_r($newColors);
  //print_r($COLORS);
  //print_r($newCOLORS);
  $output = array();
  $index = 0;
  for($j = 0; $j < count($newGENRES); $j++)
  {
    $genre = $newGENRES[$j]['genre_id'];
    //echo "\n$color";
    $getAllByGenreQuery = "SELECT img_URL, name, description FROM tv_shows WHERE genre_id = '$genre'";
    $resultX = mysqli_query($conn, $getAllByGenreQuery);
    for($x = $index; $x < mysqli_num_rows($resultX) + $index; $x++)
    {
      $shmoe= mysqli_fetch_array($resultX, MYSQLI_ASSOC);
      
      $output["show" .$x]= $shmoe;

      //print_r($output);
    }
    $index = $x;
  }
  //print_r($output);
  return $output;

}

function requestProcessor($request)
{
  //var_dump($request);  
  if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }

    switch ($request['type']) {
        case "Registration":
          echo "received registration request\n\n";
          passthru("python3 logging_producer.py "."'User data sent to the database'");
          return registerUser($request['name'], $request['username'], $request['password']);
          break;
        
        case "Login":
          echo "received login request\n\n";
          passthru("python3 logging_producer.py "."'User logged into website'");
          return loginUser($request['username'], $request['password']);
          break;

        case "apiData":
          echo "received api data\n\n";
          return apiData($request['data']);
          break;
        
        case "getTvShows":
          echo "received tv shows request\n\n";
          return getAllShows();
          break;
    }

    return array("returnCode" => '0', 'message' => "Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "testRabbitMQServer BEGIN" . PHP_EOL;
passthru("python3 logging_producer.py "."'Rabbit MQ Server Side Started'");
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END" . PHP_EOL;
exit();

?>
