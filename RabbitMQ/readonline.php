object(rabbitMQClient)#7 (13) {
  ["machine"]=>
  array(2) {
    ["testServer"]=>
    array(8) {
      ["BROKER_HOST"]=>
      string(9) "127.0.0.1"
      ["BROKER_PORT"]=>
      string(4) "5672"
      ["USER"]=>
      string(4) "test"
      ["PASSWORD"]=>
      string(4) "test"
      ["VHOST"]=>
      string(8) "testHost"
      ["EXCHANGE"]=>
      string(12) "testExchange"
      ["QUEUE"]=>
      string(9) "testQueue"
      ["AUTO_DELETE"]=>
      string(1) "1"
    }
    ["dataServer"]=>
    array(8) {
      ["BROKER_HOST"]=>
      string(9) "127.0.0.1"
      ["BROKER_PORT"]=>
      string(4) "5672"
      ["USER"]=>
      string(4) "test"
      ["PASSWORD"]=>
      string(4) "test"
      ["VHOST"]=>
      string(8) "testHost"
      ["EXCHANGE"]=>
      string(12) "dataExchange"
      ["QUEUE"]=>
      string(9) "dataQueue"
      ["AUTO_DELETE"]=>
      string(0) ""
    }
  }
  ["BROKER_HOST"]=>
  string(9) "127.0.0.1"
  ["BROKER_PORT"]=>
  string(4) "5672"
  ["USER"]=>
  string(4) "test"
  ["PASSWORD"]=>
  string(4) "test"
  ["VHOST"]=>
  string(8) "testHost"
  ["exchange"]=>
  string(12) "dataExchange"
  ["queue"]=>
  string(9) "dataQueue"
  ["routing_key"]=>
  string(1) "*"
  ["response_queue"]=>
  array(1) {
    ["65d4b49f195c6"]=>
    string(7) "waiting"
  }
  ["exchange_type"]=>
  string(5) "topic"
  ["auto_delete"]=>
  string(0) ""
  ["conn_queue"]=>
  object(AMQPQueue)#12 (9) {
    ["connection":"AMQPQueue":private]=>
    object(AMQPConnection)#8 (18) {
      ["login":"AMQPConnection":private]=>
      string(4) "test"
      ["password":"AMQPConnection":private]=>
      string(4) "test"
      ["host":"AMQPConnection":private]=>
      string(9) "127.0.0.1"
      ["vhost":"AMQPConnection":private]=>
      string(8) "testHost"
      ["port":"AMQPConnection":private]=>
      int(5672)
      ["read_timeout":"AMQPConnection":private]=>
      float(0)
      ["write_timeout":"AMQPConnection":private]=>
      float(0)
      ["connect_timeout":"AMQPConnection":private]=>
      float(0)
      ["rpc_timeout":"AMQPConnection":private]=>
      float(0)
      ["channel_max":"AMQPConnection":private]=>
      int(256)
      ["frame_max":"AMQPConnection":private]=>
      int(131072)
      ["heartbeat":"AMQPConnection":private]=>
      int(0)
      ["cacert":"AMQPConnection":private]=>
      string(0) ""
      ["key":"AMQPConnection":private]=>
      string(0) ""
      ["cert":"AMQPConnection":private]=>
      string(0) ""
      ["verify":"AMQPConnection":private]=>
      bool(true)
      ["sasl_method":"AMQPConnection":private]=>
      int(0)
      ["connection_name":"AMQPConnection":private]=>
      NULL
    }
    ["channel":"AMQPQueue":private]=>
    object(AMQPChannel)#9 (6) {
      ["connection":"AMQPChannel":private]=>
      object(AMQPConnection)#8 (18) {
        ["login":"AMQPConnection":private]=>
        string(4) "test"
        ["password":"AMQPConnection":private]=>
        string(4) "test"
        ["host":"AMQPConnection":private]=>
        string(9) "127.0.0.1"
        ["vhost":"AMQPConnection":private]=>
        string(8) "testHost"
        ["port":"AMQPConnection":private]=>
        int(5672)
        ["read_timeout":"AMQPConnection":private]=>
        float(0)
        ["write_timeout":"AMQPConnection":private]=>
        float(0)
        ["connect_timeout":"AMQPConnection":private]=>
        float(0)
        ["rpc_timeout":"AMQPConnection":private]=>
        float(0)
        ["channel_max":"AMQPConnection":private]=>
        int(256)
        ["frame_max":"AMQPConnection":private]=>
        int(131072)
        ["heartbeat":"AMQPConnection":private]=>
        int(0)
        ["cacert":"AMQPConnection":private]=>
        string(0) ""
        ["key":"AMQPConnection":private]=>
        string(0) ""
        ["cert":"AMQPConnection":private]=>
        string(0) ""
        ["verify":"AMQPConnection":private]=>
        bool(true)
        ["sasl_method":"AMQPConnection":private]=>
        int(0)
        ["connection_name":"AMQPConnection":private]=>
        NULL
      }
      ["prefetch_count":"AMQPChannel":private]=>
      int(3)
      ["prefetch_size":"AMQPChannel":private]=>
      int(0)
      ["global_prefetch_count":"AMQPChannel":private]=>
      int(0)
      ["global_prefetch_size":"AMQPChannel":private]=>
      int(0)
      ["consumers":"AMQPChannel":private]=>
      array(0) {
      }
    }
    ["name":"AMQPQueue":private]=>
    string(9) "dataQueue"
    ["consumer_tag":"AMQPQueue":private]=>
    NULL
    ["passive":"AMQPQueue":private]=>
    bool(false)
    ["durable":"AMQPQueue":private]=>
    bool(false)
    ["exclusive":"AMQPQueue":private]=>
    bool(false)
    ["auto_delete":"AMQPQueue":private]=>
    bool(true)
    ["arguments":"AMQPQueue":private]=>
    array(0) {
    }
  }
}






object(rabbitMQClient)#13 (13) {
  ["machine"]=>
  array(2) {
    ["testServer"]=>
    array(8) {
      ["BROKER_HOST"]=>
      string(9) "127.0.0.1"
      ["BROKER_PORT"]=>
      string(4) "5672"
      ["USER"]=>
      string(4) "test"
      ["PASSWORD"]=>
      string(4) "test"
      ["VHOST"]=>
      string(8) "testHost"
      ["EXCHANGE"]=>
      string(12) "testExchange"
      ["QUEUE"]=>
      string(9) "testQueue"
      ["AUTO_DELETE"]=>
      string(1) "1"
    }
    ["dataServer"]=>
    array(8) {
      ["BROKER_HOST"]=>
      string(9) "127.0.0.1"
      ["BROKER_PORT"]=>
      string(4) "5672"
      ["USER"]=>
      string(4) "test"
      ["PASSWORD"]=>
      string(4) "test"
      ["VHOST"]=>
      string(8) "testHost"
      ["EXCHANGE"]=>
      string(12) "dataExchange"
      ["QUEUE"]=>
      string(9) "dataQueue"
      ["AUTO_DELETE"]=>
      string(0) ""
    }
  }
  ["BROKER_HOST"]=>
  string(9) "127.0.0.1"
  ["BROKER_PORT"]=>
  string(4) "5672"
  ["USER"]=>
  string(4) "test"
  ["PASSWORD"]=>
  string(4) "test"
  ["VHOST"]=>
  string(8) "testHost"
  ["exchange"]=>
  string(12) "dataExchange"
  ["queue"]=>
  string(9) "dataQueue"
  ["routing_key"]=>
  string(1) "*"
  ["response_queue"]=>
  array(1) {
    ["65d4b4a4a600b"]=>
    string(7) "waiting"
  }
  ["exchange_type"]=>
  string(5) "topic"
  ["auto_delete"]=>
  string(0) ""
  ["conn_queue"]=>
  object(AMQPQueue)#15 (9) {
    ["connection":"AMQPQueue":private]=>
    object(AMQPConnection)#12 (18) {
      ["login":"AMQPConnection":private]=>
      string(4) "test"
      ["password":"AMQPConnection":private]=>
      string(4) "test"
      ["host":"AMQPConnection":private]=>
      string(9) "127.0.0.1"
      ["vhost":"AMQPConnection":private]=>
      string(8) "testHost"
      ["port":"AMQPConnection":private]=>
      int(5672)
      ["read_timeout":"AMQPConnection":private]=>
      float(0)
      ["write_timeout":"AMQPConnection":private]=>
      float(0)
      ["connect_timeout":"AMQPConnection":private]=>
      float(0)
      ["rpc_timeout":"AMQPConnection":private]=>
      float(0)
      ["channel_max":"AMQPConnection":private]=>
      int(256)
      ["frame_max":"AMQPConnection":private]=>
      int(131072)
      ["heartbeat":"AMQPConnection":private]=>
      int(0)
      ["cacert":"AMQPConnection":private]=>
      string(0) ""
      ["key":"AMQPConnection":private]=>
      string(0) ""
      ["cert":"AMQPConnection":private]=>
      string(0) ""
      ["verify":"AMQPConnection":private]=>
      bool(true)
      ["sasl_method":"AMQPConnection":private]=>
      int(0)
      ["connection_name":"AMQPConnection":private]=>
      NULL
    }
    ["channel":"AMQPQueue":private]=>
    object(AMQPChannel)#7 (6) {
      ["connection":"AMQPChannel":private]=>
      object(AMQPConnection)#12 (18) {
        ["login":"AMQPConnection":private]=>
        string(4) "test"
        ["password":"AMQPConnection":private]=>
        string(4) "test"
        ["host":"AMQPConnection":private]=>
        string(9) "127.0.0.1"
        ["vhost":"AMQPConnection":private]=>
        string(8) "testHost"
        ["port":"AMQPConnection":private]=>
        int(5672)
        ["read_timeout":"AMQPConnection":private]=>
        float(0)
        ["write_timeout":"AMQPConnection":private]=>
        float(0)
        ["connect_timeout":"AMQPConnection":private]=>
        float(0)
        ["rpc_timeout":"AMQPConnection":private]=>
        float(0)
        ["channel_max":"AMQPConnection":private]=>
        int(256)
        ["frame_max":"AMQPConnection":private]=>
        int(131072)
        ["heartbeat":"AMQPConnection":private]=>
        int(0)
        ["cacert":"AMQPConnection":private]=>
        string(0) ""
        ["key":"AMQPConnection":private]=>
        string(0) ""
        ["cert":"AMQPConnection":private]=>
        string(0) ""
        ["verify":"AMQPConnection":private]=>
        bool(true)
        ["sasl_method":"AMQPConnection":private]=>
        int(0)
        ["connection_name":"AMQPConnection":private]=>
        NULL
      }
      ["prefetch_count":"AMQPChannel":private]=>
      int(3)
      ["prefetch_size":"AMQPChannel":private]=>
      int(0)
      ["global_prefetch_count":"AMQPChannel":private]=>
      int(0)
      ["global_prefetch_size":"AMQPChannel":private]=>
      int(0)
      ["consumers":"AMQPChannel":private]=>
      array(0) {
      }
    }
    ["name":"AMQPQueue":private]=>
    string(9) "dataQueue"
    ["consumer_tag":"AMQPQueue":private]=>
    NULL
    ["passive":"AMQPQueue":private]=>
    bool(false)
    ["durable":"AMQPQueue":private]=>
    bool(false)
    ["exclusive":"AMQPQueue":private]=>
    bool(false)
    ["auto_delete":"AMQPQueue":private]=>
    bool(true)
    ["arguments":"AMQPQueue":private]=>
    array(0) {
    }
  }
}

