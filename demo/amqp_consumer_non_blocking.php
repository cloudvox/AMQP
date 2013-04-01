<?php

include(__DIR__ . '/config.php');
use AMQP\Connection;

$exchange = 'router';
$queue = 'msgs';
$consumerTag = 'consumer';

$connection = new Connection(AMQP_RESOURCE);
$channel = $connection->channel();

/*
    The following code is the same both in the consumer and the producer.
    In this way we are sure we always have a queue to consume from and an
        exchange where to publish messages.
*/

/*
    name: $queue
    passive: false
    durable: true // the queue will survive server restarts
    exclusive: false // the queue can be accessed in other channels
    auto_delete: false //the queue won't be deleted once the channel is closed.
*/
$channel->queueDeclare(array('queue' => $queue, 'durable' => true, 'auto_delete' => false));

/*
    name: $exchange
    type: direct
    passive: false
    durable: true // the exchange will survive server restarts
    auto_delete: false //the exchange won't be deleted once the channel is closed.
*/

$channel->exchangeDeclare($exchange, 'direct', array('durable' => true, 'auto_delete' => false));

$channel->queueBind($queue, $exchange);

function process_message($msg)
{

    echo "\n--------\n";
    echo $msg->body;
    echo "\n--------\n";

    $msg->delivery_info[ 'channel' ]->
        basic_ack($msg->delivery_info[ 'delivery_tag' ]);

    // Send a message with the string "quit" to cancel the consumer.
    if ($msg->body === 'quit') {
        $msg->delivery_info[ 'channel' ]->
            basic_cancel($msg->delivery_info[ 'consumer_tag' ]);
    }
}

/*
    queue: Queue from where to get the messages
    consumer_tag: Consumer identifier
    no_local: Don't receive messages published by this consumer.
    no_ack: Tells the server if the consumer will acknowledge the messages.
    exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
    nowait:
    callback: A PHP Callback
*/

$channel->basicConsume(array('queue' => $queue, 'consumer_tag' => $consumerTag, 'callback' => 'process_message'));

register_shutdown_function(
    function() use ($channel, $connection)
    {
        $channel->close();
        $connection->close();
    }
);

// Loop as long as the channel has callbacks registered
while (count($channel->callbacks)) {
    $read = array(
        $connection->getSocket()
    ); // add here other sockets that you need to attend
    $write = null;
    $except = null;
    if (false ===
        ($num_changed_streams = stream_select($read, $write, $except, 60))
    ) {
        /* Error handling */
    } elseif ($num_changed_streams > 0) {
        $channel->wait();
    }
}

