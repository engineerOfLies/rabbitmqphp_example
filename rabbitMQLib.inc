<?php

require_once('get_host_info.inc');

class rabbitMQServer
{
	private $machine = "";
	public  $BROKER_HOST;
	private $BROKER_PORT;
	private $USER;
	private $PASSWORD;
	private $VHOST;
	private $exchange;
	private $queue;
	private $routing_key = '';
	private $exchange_type = "topic";
	private $auto_delete = false;

	function __construct($machine, $server = "rabbitMQ")
	{
		$this->machine = getHostInfo(array($machine));
		$this->BROKER_HOST   = $this->machine[$server]["BROKER_HOST"];
		$this->BROKER_PORT   = $this->machine[$server]["BROKER_PORT"];
		$this->USER     = $this->machine[$server]["USER"];
		$this->PASSWORD = $this->machine[$server]["PASSWORD"];
		$this->VHOST = $this->machine[$server]["VHOST"];
		if (isset( $this->machine[$server]["EXCHANGE_TYPE"]))
		{
			$this->exchange_type = $this->machine[$server]["EXCHANGE_TYPE"];
		}
		if (isset( $this->machine[$server]["AUTO_DELETE"]))
		{
			$this->auto_delete = $this->machine[$server]["AUTO_DELETE"];
		}
		$this->exchange = $this->machine[$server]["EXCHANGE"];
		$this->queue = $this->machine[$server]["QUEUE"];
	}

	function process_message($msg)
	{
		// send the ack to clear the item from the queue
		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);        
		try
		{
			if ($msg->get('reply_to'))
			{
				// message wants a response

				// process request
				$body = $msg->body;
				$payload = json_decode($body, true);
				$response;
				if (isset($this->callback))
				{
					$response = call_user_func($this->callback,$payload);
				}
				$responseMSG = new AMQPMessage(
						json_encode($response),
						array('correlation_id' =>
							$msg->get('correlation_id'))
						);
				$msg->delivery_info['channel']->basic_publish(
						$responseMSG, '', $msg->get('reply_to'));
				return;
			}
		}
		catch(Exception $e)
		{
			// ampq throws exception if get fails...
            echo "error: rabbitMQServer: process_message: exception caught: ".$e;
		}
		// message does not require a response, send ack immediately
		$body = $msg->body;
		$payload = json_decode($body, true);
		if (isset($this->callback))
		{
			call_user_func($this->callback,$payload);
		}
	}

	function process_requests($callback)
	{
		try
		{
			$this->callback = $callback;
			$conn = new AMQPConnection($this->BROKER_HOST,
					$this->BROKER_PORT,$this->USER,$this->PASSWORD,$this->VHOST);
			$channel = $conn->channel();
			$channel->queue_declare($this->queue, false, true, false,false);
			$channel->exchange_declare($this->exchange,$this->exchange_type, true, $this->auto_delete);
			$channel->queue_bind($this->queue, $this->exchange,$this->routing_key);
			$channel->basic_qos(0,5,null);
			$channel->basic_consume($this->queue,"delivery_tag", false, false, false, 
					false,array($this,'process_message'));

			function shutdown($channel, $connection)
			{
				$channel->close();
				$connection->close();
			}

			register_shutdown_function('shutdown', $channel,
					$conn);

			// Loop as long as the channel has callbacks registered
			while (count($channel->callbacks))
			{
				$channel->wait();
			}
		}
		catch (Exception $e)
		{
			trigger_error("Failed to start request processor: ".$e,E_USER_ERROR); 
		}
	}
}

class rabbitMQClient
{
	private $machine = "";
	public  $BROKER_HOST;
	private $BROKER_PORT;
	private $USER;
	private $PASSWORD;
	private $VHOST;
	private $exchange;
	private $queue;
	private $routing_key = '';
	private $response_queue = array();
	private $exchange_type = "topic";

	function __construct($machine, $server = "rabbitMQ")
	{
		$this->machine = getMachineInfo(array($machine));
		$this->BROKER_HOST   = $this->machine[$server]["BROKER_HOST"];
		$this->BROKER_PORT   = $this->machine[$server]["BROKER_PORT"];
		$this->USER     = $this->machine[$server]["USER"];
		$this->PASSWORD = $this->machine[$server]["PASSWORD"];
		$this->VHOST = $this->machine[$server]["VHOST"];
		if (isset( $this->machine[$server]["EXCHANGE_TYPE"]))
		{
			$this->exchange_type = $this->machine[$server]["EXCHANGE_TYPE"];
		}
		if (isset( $this->machine[$server]["AUTO_DELETE"]))
		{
			$this->auto_delete = $this->machine[$server]["AUTO_DELETE"];
		}
		$this->exchange = $this->machine[$server]["EXCHANGE"];
		$this->queue = $this->machine[$server]["QUEUE"];
	}

	function process_response($response)
	{
		$uid = $response->get('correlation_id');
		$body = $response->body;
		$payload = json_decode($body, true);
		if (!(isset($payload)))
		{
			$payload = "[empty response]";
		}
		$this->response_queue[$uid] = $payload;
	}

	function send_request($message)
	{
		$uid = uniqid();
		$json_message = json_encode($message);
		try
		{
			$conn = new AMQPConnection($this->BROKER_HOST, $this->BROKER_PORT,
					$this->USER,$this->PASSWORD,$this->VHOST);
			$channel = $conn->channel();

			/*setup response queue*/
			list($callback_queue, , ) = $channel->queue_declare(
					"", false, false, true, false);
			$channel->basic_consume(
					$callback_queue, '', false, false, false,
					false,array($this, 'process_response'));

			/*setup request*/
			$msg = new AMQPMessage($json_message,
					array('content_type' => 'text/plain',
						'delivery_mode' => 2,
						'correlation_id' => $uid,
						'reply_to' => $callback_queue));
			$channel->basic_publish($msg, $this->exchange,$this->routing_key);
			/**
			  @todo: build in a timeout
			 */
			while(!(isset($this->response_queue[$uid])))
			{
				$channel->wait();
			}
			$response = $this->response_queue[$uid];
			unset($this->response_queue[$uid]);
			$channel->close();
			$conn->close();
			return $response;
		}
		catch(Exception $e)
		{
			die("failed to send message to exchange: ". $e->getMessage()."\n");
		}
		return $payload;
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
		try
		{
			$conn = new AMQPConnection($this->BROKER_HOST, $this->BROKER_PORT,
					$this->USER,$this->PASSWORD,$this->VHOST);
			$channel = $conn->channel();
			$channel->queue_declare($this->queue, false, true, false, false);
			$channel->exchange_declare($this->exchange,$this->exchange_type, true, false);
			$channel->queue_bind($this->queue, $this->exchange,$this->routing_key);
			$msg = new AMQPMessage($json_message, array('content_type' => 'text/plain',
						'delivery_mode' => 2));
			$channel->basic_publish($msg, $this->exchange,$this->routing_key);
			$channel->close();
			$conn->close();
		}
		catch(Exception $e)
		{
			die("failed to send message to exchange: ". $e->getMessage()."\n");
		}
	}
}
?>

