<?php
 include(__DIR__ . "/../lib/helpers.php");

    if(isset($_GET['id'])) {
        $sendID = $_GET["id"];
        $data = array('type' => 'fetch', 'data' => array('id' => $sendID));
        $result = send($data, "dmz");
        var_dump($result);
    }
?>