<?php
session_start();
if(isset($_SESSION["username"])) {
echo "<p style=
'color: white !important;
text-transform: uppercase;'
>
Welcome " . $_SESSION["username"]
 . "</p";
}
  include(__DIR__ . "/../lib/helpers.php");



$title = $_POST['title'];
// tt3896198 id
// f3d054e8 key
$name = "";
$poster = "";
if(isset($_POST['submit'])) {
  $data = array('type' => 'api', 'data' => array('title' => $title));
  $response = sendAPI($data, "rabbit");
  $count = count($response["message"]["Search"]);
  echo ($count) . "<br>";
  for ($i = 0; $i < $count; $i++) {
    $name = $response["message"]["Search"][$i]["Title"];
    $poster = $response["message"]["Search"][$i]["Poster"];
    // echo "$name";
    // echo "<br>";
    // echo "$poster";
    // echo "<br>";

  }
  // var_dump($response["message"]["Search"]);

}

?>
<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
      <div class="container">
      <form method="post">
      <div class="searchBox">
      <input class="searchInput" type="search" id="title" name="title" placeholder="Movie Title">
        <button type="submit" name="submit" class="searchButton"> Search </button>
      </div>
      </form>
      
      <div class="grid">
          <!-- FOR LOOP PHP -->
          <?php 
          for($i = 0; $i < $count; $i++) { 
            
          ?>
            <div class="movie-poster"></div>
            <?php } ?>
      </div>
    </div>
    </body>
</html>

<style>

  /* Movie Posters Container */
  .container {
    width: 100%;
    /* Adjust this for bigger bounding box */
    max-width: 1440px;
    margin: 0 auto;
  }

  .movie-poster {
    height: 300px;
    width: 100%;
    border: solid 2px black;
  }

  .grid {
    width: 100%;
    display: grid;
    /* Adjust minmax value (e.g 350px) to adjust the minimum size of the box */
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
  }

.searchBox {
    position: absolute;
    top: 0;
    right: 0;
    background: #2f3640;
    height: 40px;
    border-radius: 40px;
    padding: 10px;

}

.searchBox:hover > .searchInput {
    width: 240px;
    padding: 0 6px;
}

.searchBox:hover > .searchButton {
  color : white;
}

.searchButton {
    color: white;
    float: right;
    width: 40px;
    height: 40px;
    border: transparent;
    background: #2f3640;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: 0.4s;
}

.searchInput {
    border:none;
    background: none;
    outline:none;
    float:left;
    padding: 0;
    color: white;
    font-size: 12px;
    transition: 0.4s;
    line-height: 40px;
    width: 0px;

}

@media screen and (max-width: 620px) {
.searchBox:hover > .searchInput {
    width: 150px;
    padding: 0 6px;
}


</style>
