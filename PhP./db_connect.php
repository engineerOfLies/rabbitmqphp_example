<?php
$db_host = "remote_mysql_host";
$db_name = "remote_mysql_db";
$db_username = "remote_mysql_username";
$db_password = "remote_mysql_password";

$mq_host = "rabbitmq_host";
$mq_port = "rabbitmq_port";
$mq_username = "rabbitmq_username";
$mq_password = "rabbitmq_password";

// Connect to MySQL
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

if (!$conn) {
    die("MySQL connection failed: " . mysqli_connect_error());
} else {
    echo "MySQL connected successfully\n";
}

// Connect to RabbitMQ
$mq_conn = new AMQPStreamConnection($mq_host, $mq_port, $mq_username, $mq_password);

if (!$mq_conn) {
    die("RabbitMQ connection failed\n");
} else {
    echo "RabbitMQ connected successfully\n";
}
?>
