<html>
    <head>
        <title> Steam Profile</title>
        <!-- link to css stylesheet, has all the formatting--> 
		<link rel="stylesheet" href="../css/profile.css">
	
    </head>

    <body onload="getuserinfo()">
        <!--onload="getuserinfo()"-->

  <div class="navigationbar">
    <a href="index.html">Welcome</a>
    <a href="steamid.html">SteamID</a>
    <a class="active" href="profile.php">Steam Profile</a>
    <a href="news.php">Game News</a>

    <div class="logout">
        <form method="POST" action="/html/loginbase.php">
            <div class="logoutarea">
                <input type="submit" name="logoutbutton" class="button" value="Log Out">
            </div>
        </form>
    </div>
  </div>

        <div class = "profilebox">
            <h1> Profile Page</h1>
            <div class="userinfo">
                

            <script>
                // document.getElementById('u').onload = function() {getuserinfo()}; 

                function getuserinfo(){
                    <?php  

                        require_once('../src/include/loginbase.inc'); 

                        $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
                        session_start();
                        $uid = $_SESSION['uid'];
                        $request = array(); 
                        $request['type'] = "get_steam_profile";
                        $request['userId'] = $uid;
                        
                        $response = $client->send_request($request);
                        //$response = $client->publish($request);
                        
                        // session_commit();
        
                        echo $response['steamName'];
                        $name = $response['steamName'];
                        $ava = $response['avatarLink'];
                   ?>
                }
            </script>
                    
                <p> Steam Name: <?php echo $name; ?> </p>
                    
                <p> Avatar: </p> 
                <img src='<?php echo $ava; ?>' style="width: 150px">
            </div>
    </div>

    </body>
</html>