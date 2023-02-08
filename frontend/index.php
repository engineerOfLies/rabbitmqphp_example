<?php
// session_start();
// if(isset($_SESSION["username"])) {
//   echo $_SESSION["username"];
// }
  include(__DIR__ . "/../lib/helpers.php");
  include(__DIR__ . "/../rabbit/rabbitmq.php"); 
function getData($id, $key)
{
    $path = "http://www.omdbapi.com/?i=$id&apikey=$key";
    $json = file_get_contents($path);
    return json_decode($json, TRUE);
}

// $response = sendAPI($data, "rabbit");

$id = $_POST['id'];
$key = $_POST['key'];

// tt3896198 id
// f3d054e8 key
if(isset($_POST['submit'])) {
  $idk = getData($id, $key);
  $data = array('type' => 'api', 'data' => array('id' => $idk));
  $response = sendAPI($data, "rabbit");
  echo "<pre>";
  print_r($idk);
  echo "</pre>";
}

?>

<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
    <div class="form">
      <form method="post">
        id
        <input name="id" id="id" required/>
        key
        <input name="key" id="key" required/>
        <button type="submit" name="submit">get data</button>
      </form>
    </div>
    </body>
</html>

