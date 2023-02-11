<?php
session_start();
if(isset($_SESSION["username"])) {
echo "<p style=
'color: black !important;
text-transform: uppercase;'
>
Welcome " . $_SESSION["username"]
 . "</p";
}
  include(__DIR__ . "/../lib/helpers.php");



$title = $_POST['title'];
$name = "";
$poster = "";
if(isset($_POST['submit'])) {
  $data = array('type' => 'search', 'data' => array('title' => $title));
  $response = sendAPI($data, "rabbit");
  $count = count($response["message"]);

}

if(isset($_POST['grabID'])) {
  $sendID = $_POST['grabID'];
  $data = array('type' => 'fetch', 'data' => array('id' => $sendID));
  $response = sendAPI($data, "rabbit");
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
          <?php 
          for($i = 0; $i < $count; $i++) { 
            for($j = 0; $j < 10; $j++) {
              $banner = $response["message"][$i]["Search"][$j]["Poster"];
              $id = $response["message"][$i]["Search"][$j]["imdbID"];
          ?>
            <div class="movie-poster">
              <img src="<?php echo $banner;
               ?>" loading="lazy"
               >
               <form method="post">
                <input type="submit" value=<?php
                echo $id; ?> name="grabID"> 
            </div> 
            <?php } } ?>
      </div>
    </div>
    </body>
</html>

<style>
  body {
    background: #5D4157;  /* fallback for old browsers */
    background: -webkit-linear-gradient(to right, #A8CABA, #5D4157);  /* Chrome 10-25, Safari 5.1-6 */
    background: linear-gradient(to right, #A8CABA, #5D4157); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */

  }
  /* Movie Posters Container */
  .container {
    width: 100%;
    /* Adjust this for bigger bounding box */
    max-width: 1440px;
    margin: 0 auto;
  }

  .movie-poster {
    max-height: 444px;
    height: 100%;
    width: 300px;
  }
  img {
    height: 100%;
    width: 100%;

  }
  .grid {
    width: 100%;
    display: grid;
    /* Adjust minmax value (e.g 350px) to adjust the minimum size of the box */
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
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
