<html>
    <head>
        <title> Game News </title>
        <!-- link to css stylesheet, has all the formatting--> 
		<link rel="stylesheet" href="../css/news.css">
	
    </head>

    <body onload="getnews()">

  <div class="navigationbar">
    <a href="index.html">Welcome</a>
    <a href="steamid.html">SteamID</a>
    <a href="profile.php"> Steam Profile</a>
    <a class="active" href="news.php">Game News</a>

    <div class="logout">
        <form method="POST" action="/html/loginbase.php">
            <div class="logoutarea">
                <input type="submit" name="logoutbutton" class="button" value="Log Out">
            </div>
        </form>
    </div>
  </div>

        <div class = "newsbox">
            <h1> Game News</h1>

            <script>
                function getnews(){
                    <?php  

                        require_once('../src/include/loginbase.inc'); 

                        $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
                        session_start();
                        $uid = $_SESSION['uid'];
                        $request = array(); 
                        $request['type'] = "get_user_news";
                        $request['userId'] = $uid;
                        
                        $response = $client->send_request($request);
        
                       // $game = array($response['game']);
                       // $title = array($response['title']);
                       // $link = array($response['link']);
                       // $game = $response[0]['game'];
                       // $title = $response[0]['title'];
                       // $link = $response[0]['link'];

                   ?>
                }
            </script>

                <?php foreach($response as $game) { ?>

                <div class="userinfo">      

                    <p> Game Name: <?php echo $game['game']; ?> </p>
                    <p> Article Title: <?php echo $game['title']; ?> </p>
                    <p> Link to Article: <?php echo $game['link']; ?> </p>

                </div>
                <?php } ?>
                </div>

    </body>
</html>