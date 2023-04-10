<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$request = array();
$request['type'] = "getTvShows";


$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$response = $client->send_request($request);


if ($response)
{
    $tvArray = $response;
}
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style2.css">
    <title>Movie App</title>
</head>
<script type='text/javascript' src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js">
        $(document).ready(function(){
          $('.likeBtn').click(function(){         
            var postid = $(this).attr('data-sName');
            //alert (postid);
            $.ajax({
              type     : 'post',
              url      : 'addLike.php',
              data     : {
                  "sName": postid
              },
              success : function(data) {
                 //alert("fuckin worked");
              },
              error(){
                alert("error");
              } 
            }); 
	  });
    });
</script>
<body>
<html>
  <head>
    <style>
      /* CSS for navigation bar */
      ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background-color: #333;
      }

      li {
        float: left;
      }

      li a {
        display: block;
        color: white;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
      }

      li a:hover {
        background-color: #111;
      }
    </style>
  </head>
  <body>
    <!-- Navigation bar HTML -->
    <ul>
      <li><a href="tvHome.php">Home</a></li>
      <li><a href="movie.html">Movie Search</a></li>
      <li><a href="profile.html">Profile</a></li>
      <li><a href="list.html">My List</a></li>
      <li><a href="discussion.html">Discussion</a></li>
    </ul>
    <!-- Rest of the HTML content -->
    <div>
      <h1>Welcome to my website!</h1>
      <p>Here's some more content...</p>
    </div>
  </body>
</html>


    
    
    <main id="main"></main>
    <!--
    <div class="pagination">
        <div class="page" id="prev">Previous Page</div>
        <div class="current" id="current">1</div>
        <div class="page" id="next">Next Page</div>

    </div>-->
    <?php
    for ($x = 0; $x <= count($tvArray); $x++)
    {
        echo '<div class=movie>
                <img src='.$tvArray['show'.$x]['img_url'].' alt ='.$tvArray['show'.$x]['name'].'>
                <div class=movie-info>
                    <h3>'.$tvArray['show'.$x]['name'].'</h3>
                </div>
                <div class=overview>
                    <h3>Overview</h3>
                    '.$tvArray['show'.$x]['description'].'
                    <br>
                </div>
                </div>
                <a href="#" class="likeBtn" data-sName="'.$tvArray['show'.$x]['name'].'" >Like/Unlike</a>
';
    }
    ?>

</body>
</html>


