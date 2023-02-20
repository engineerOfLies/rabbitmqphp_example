<?php
session_start();
include(__DIR__ . "/../lib/helpers.php");
include (__DIR__."/components/navbar.php");
$count = 0;
if(isset($_SESSION["username"])) {
echo "<p style=
'color: black !important;
text-transform: uppercase;'
>
Welcome " . $_SESSION["username"]
 . "</p";
}

$data = array('type' => 'onload', 'data' => array('message' => "does this work"));
$response = send($data, "dmz");
var_dump($response['message']['results'][0]['poster_path']);
echo "image.tmdb.org/t/p/w500" . $response['message']['results'][0]['poster_path'];
$count = count($response['message']['results']);
var_dump($count);
?>
<!DOCTYPE html>
<html>
    <head>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #272727;
            color: white;
        }
    </style>
      <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body>
      <div class="container">
      
      <div class="grid">
          <?php 
          for($i = 0; $i < $count; $i++) { 
              $banner = $response["message"]["results"][$i]["poster_path"];
              $id = $response["message"]["results"][$i]['id'];
          ?>
          <div class="movie-poster">
            <!-- the 'id' parameter is what we'll check on the 
            'new-page.php' that we're linking the poster to
          -->
          <a href="movie.php?id=<?php echo $id?>">
            
              <img src="https://image.tmdb.org/t/p/original<?php echo $banner;
               ?>" loading="lazy"
               >
               <!-- convert this to a link -->
               <a href="movie.php?id=<?php echo $id?>"></a>
               <!-- <form method="post">
                <input type="submit" value= ?> name="grabID">  -->
           
            </a>
            </div> 
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

</style>
