#!upstart
description "Test Rabbit MQ server"

start on startup
stop on shutdown

respawn

exec /home/djkehoe/git/rabbitmqphp_example/testRabbitMQServer.php

post-start script
    PID=`status testRabbitMQServer | egrep -oi '([0-9]+)$' | head -n1`
    echo $PID > /var/run/testRabbitMQServer.pid
end script

post-stop script
    rm -f /var/run/testRabbitMQServer.pid
end script
