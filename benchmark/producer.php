<?php

/**
 * Usage:
 *  php producer.php 10000
 * The integer arguments tells the script how many messages to publish.
 */

include(__DIR__ . '/config.php');

use AMQP\Connection;
use AMQP\Message;

$exchange = 'bench_exchange';
$queue = 'bench_queue';

$conn = new Connection(AMQP_RESOURCE);
$ch = $conn->channel();

$ch->queueDeclare($queue, false, false, false, false);

$ch->exchangeDeclare($exchange, 'direct', false, false, false);

$ch->queueBind($queue, $exchange);

$msg_body = <<<EOT
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyza
EOT;

$msg = new Message($msg_body);

$time = microtime(true);

$max = isset($argv[ 1 ]) ? (int)$argv[ 1 ] : 1;

// Publishes $max messages using $msgBody as the content.
for ($i = 0; $i < $max; $i++) {
    $ch->basicPublish($msg, $exchange);
}

echo microtime(true) - $time, "\n";

$ch->basicPublish(new Message('quit'), $exchange);

$ch->close();
$conn->close();

