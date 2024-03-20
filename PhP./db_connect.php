<?php
$db_host = "192.168.191.133";
$db_username = "TestUser";
$db_password = "12345";
$db_name = "form";

$mq_host = "127.0.0.1";
$mq_port = "5672";
$mq_username = "test";
$mq_password = "test";

// Connect to MySQL
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

if (!$conn) {
    die("MySQL connection failed: " . mysqli_connect_error());
} else {
    echo "MySQL connected successfully\n";
    echo "Connection established\n";
}

// Connect to RabbitMQ
$mq_conn = new AMQPStreamConnection($mq_host, $mq_port, $mq_username, $mq_password);

if (!$mq_conn) {
    die("RabbitMQ connection failed\n");
} else {
    echo "RabbitMQ connected successfully\n";
}
?>
