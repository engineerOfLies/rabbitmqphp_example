<?php

require_once "get_host_info.inc";

class rabbitMQServer
{
    public $machine = "";
    public $BROKER_HOST;
    public $BROKER_PORT;
    public $USER;
    public $PASSWORD;
    public $VHOST;
    public $exchange;
    public $queue;
    public $routing_key = "*";
    public $exchange_type = "topic";
    public $auto_delete = false;

    function __construct($machine, $server = "rabbitMQ")
    {
        $this->machine = getHostInfo([$machine]);
        $this->BROKER_HOST = $this->machine[$server]["BROKER_HOST"];
        $this->BROKER_PORT = $this->machine[$server]["BROKER_PORT"];
        $this->USER = $this->machine[$server]["USER"];
        $this->PASSWORD = $this->machine[$server]["PASSWORD"];
        $this->VHOST = $this->machine[$server]["VHOST"];
        if (isset($this->machine[$server]["EXCHANGE_TYPE"])) {
            $this->exchange_type = $this->machine[$server]["EXCHANGE_TYPE"];
        }
        if (isset($this->machine[$server]["AUTO_DELETE"])) {
            $this->auto_delete = $this->machine[$server]["AUTO_DELETE"];
        }
        $this->exchange = $this->machine[$server]["EXCHANGE"];
        $this->queue = $this->machine[$server]["QUEUE"];
    }

    function process_message($msg)
    {
        // send the ack to clear the item from the queue
        if ($msg->getRoutingKey() !== "*") {
            return;
        }
        $this->conn_queue->ack($msg->getDeliveryTag());
        try {
            if ($msg->getReplyTo()) {
                // message wants a response
                // process request
                $body = $msg->getBody();
                $payload = json_decode($body, true);
                $response;
                if (isset($this->callback)) {
                    $response = call_user_func($this->callback, $payload);
                }

                $params = [];
                $params["host"] = $this->BROKER_HOST;
                $params["port"] = $this->BROKER_PORT;
                $params["login"] = $this->USER;
                $params["password"] = $this->PASSWORD;
                $params["vhost"] = $this->VHOST;
                $conn = new AMQPConnection($params);
                $conn->connect();
                $channel = new AMQPChannel($conn);
                $exchange = new AMQPExchange($channel);
                $exchange->setName($this->exchange);
                $exchange->setType($this->exchange_type);

                $conn_queue = new AMQPQueue($channel);
                $conn_queue->setName($msg->getReplyTo());
                $replykey = $this->routing_key . ".response";
                $conn_queue->bind($exchange->getName(), $replykey);
                $exchange->publish(
                    json_encode($response),
                    $replykey,
                    AMQP_NOPARAM,
                    ["correlation_id" => $msg->getCorrelationId()]
                );

                return;
            }
        } catch (Exception $e) {
            // ampq throws exception if get fails...
            echo "error: rabbitMQServer: process_message: exception caught: " .
                $e;
        }
        // message does not require a response, send ack immediately
        $body = $msg->getBody();
        $payload = json_decode($body, true);
        if (isset($this->callback)) {
            call_user_func($this->callback, $payload);
        }
        echo "processed one-way message\n";
    }

    function process_requests($callback)
    {
        try {
            $this->callback = $callback;
            $params = [];
            $params["host"] = $this->BROKER_HOST;
            $params["port"] = $this->BROKER_PORT;
            $params["login"] = $this->USER;
            $params["password"] = $this->PASSWORD;
            $params["vhost"] = $this->VHOST;
            $conn = new AMQPConnection($params);
            $conn->connect();

            $channel = new AMQPChannel($conn);

            $exchange = new AMQPExchange($channel);
            $exchange->setName($this->exchange);
            $exchange->setType($this->exchange_type);

            $this->conn_queue = new AMQPQueue($channel);
            $this->conn_queue->setName($this->queue);
            $this->conn_queue->bind($exchange->getName(), $this->routing_key);

            $this->conn_queue->consume([$this, "process_message"]);

            // Loop as long as the channel has callbacks registered
            while (count($channel->callbacks)) {
                $channel->wait();
            }
        } catch (Exception $e) {
            trigger_error(
                "Failed to start request processor: " . $e,
                E_USER_ERROR
            );
        }
    }
}

class rabbitMQClient
{
    public $machine = "";
    public $BROKER_HOST;
    public $BROKER_PORT;
    public $USER;
    public $PASSWORD;
    public $VHOST;
    public $exchange;
    public $queue;
    public $routing_key = "*";
    public $response_queue = [];
    public $exchange_type = "topic";

    function __construct($machine, $server = "rabbitMQ")
    {
        $this->machine = getHostInfo([$machine]);
        $this->BROKER_HOST = $this->machine[$server]["BROKER_HOST"];
        $this->BROKER_PORT = $this->machine[$server]["BROKER_PORT"];
        $this->USER = $this->machine[$server]["USER"];
        $this->PASSWORD = $this->machine[$server]["PASSWORD"];
        $this->VHOST = $this->machine[$server]["VHOST"];
        if (isset($this->machine[$server]["EXCHANGE_TYPE"])) {
            $this->exchange_type = $this->machine[$server]["EXCHANGE_TYPE"];
        }
        if (isset($this->machine[$server]["AUTO_DELETE"])) {
            $this->auto_delete = $this->machine[$server]["AUTO_DELETE"];
        }
        $this->exchange = $this->machine[$server]["EXCHANGE"];
        $this->queue = $this->machine[$server]["QUEUE"];
    }

    function process_response($response)
    {
    	echo "testProcess 1";
        $uid = $response->getCorrelationId();
        echo "testProcess 2";
        if (!isset($this->response_queue[$uid])) {
            echo "unknown uid\n";
            return true;
        }
        echo "testProcess 3";
        $this->conn_queue->ack($response->getDeliveryTag());
        echo "testProcess 4";
        $body = $response->getBody();
        echo "testProcess 5";
        $payload = json_decode($body, true);
        echo "testProcess 6";
        if (!isset($payload)) {
            $payload = "[empty response]";
        }
        echo "testProcess 7";
        $this->response_queue[$uid] = $payload;
        echo "testProcess 8";
        return false;
    }

    function send_request($message)
    {
        //$uid = uniqid();
        $uid = "65d4b49f195c6";

        $json_message = json_encode($message);
        try {
            $params = [];
            $params["host"] = $this->BROKER_HOST;
            $params["port"] = $this->BROKER_PORT;
            $params["login"] = $this->USER;
            $params["password"] = $this->PASSWORD;
            $params["vhost"] = $this->VHOST;

            $conn = new AMQPConnection($params);
            $conn->connect();

            $channel = new AMQPChannel($conn);

            $exchange = new AMQPExchange($channel);
            $exchange->setName($this->exchange);
            $exchange->setType($this->exchange_type);

            $callback_queue = new AMQPQueue($channel);
            $callback_queue->setName($this->queue . "_response");
            $callback_queue->declare();
            $callback_queue->bind(
                $exchange->getName(),
                $this->routing_key . ".response"
            );

            $this->conn_queue = new AMQPQueue($channel);
            $this->conn_queue->setName($this->queue);
            $this->conn_queue->bind($exchange->getName(), $this->routing_key);

            $exchange->publish(
                $json_message,
                $this->routing_key,
                AMQP_NOPARAM,
                [
                    "reply_to" => $callback_queue->getName(),
                    "correlation_id" => $uid,
                ]
            );
            $this->response_queue[$uid] = "waiting";
            
            echo "it stops here?".PHP_EOL;
            //this is the problem line vvv
            var_dump( $this);
            
            $callback_queue->consume([$this, "process_response"]);
	    echo "test7";
            $response = $this->response_queue[$uid];
            echo "test8";
            unset($this->response_queue[$uid]);
            echo "test5";
            return $response;
        } catch (Exception $e) {
            die(
                "failed to send message to exchange: " . $e->getMessage() . "\n"
            );
        }
    }

    /**
	  @brief send a one-way message to the server.  These are
	  auto-acknowledged and give no response.

	  @param message the body of the request.  This must make sense to the
	  server
	 */
    function publish($message)
    {
        $json_message = json_encode($message);
        try {
            $params = [];
            $params["host"] = $this->BROKER_HOST;
            $params["port"] = $this->BROKER_PORT;
            $params["login"] = $this->USER;
            $params["password"] = $this->PASSWORD;
            $params["vhost"] = $this->VHOST;
            $conn = new AMQPConnection($params);
            $conn->connect();
            $channel = new AMQPChannel($conn);
            $exchange = new AMQPExchange($channel);
            $exchange->setName($this->exchange);
            $exchange->setType($this->exchange_type);
            $this->conn_queue = new AMQPQueue($channel);
            $this->conn_queue->setName($this->queue);
            $this->conn_queue->bind($exchange->getName(), $this->routing_key);
            return $exchange->publish($json_message, $this->routing_key);
        } catch (Exception $e) {
            die(
                "failed to send message to exchange: " . $e->getMessage() . "\n"
            );
        }
    }
}
?>

