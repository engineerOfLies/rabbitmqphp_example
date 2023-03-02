<?php
    include(__DIR__."/../../lib/helpers.php");

    $req = json_decode(file_get_contents("php://input"), true);
    $data = array("data" => array("username" => $req["username"], "movie_id" => $req["movie_id"], "poster" => $req["poster"], "title" =>$req["title"]), "type" => $req["type"]); 
    $res = send($data, "rabbit");

    echo json_encode($res);
?>