<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\Publishers;

use App\Application\Services\MessagePublisherInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class WorkOrderRabbitMQPublisher implements MessagePublisherInterface
{
    public function publish(string $queue, array $payload): void
    {
        $host = env('RABBITMQ_HOST', 'localhost');
        $port = (int) env('RABBITMQ_PORT', 5672);
        $user = env('RABBITMQ_USER', 'guest');
        $pass = env('RABBITMQ_PASSWORD', 'guest');

        $connection = new AMQPStreamConnection($host, $port, $user, $pass);
        $channel = $connection->channel();

        $channel->queue_declare($queue, false, true, false, false);

        $msg = new AMQPMessage(json_encode($payload, JSON_THROW_ON_ERROR), [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/json',
        ]);

        $channel->basic_publish($msg, '', $queue);

        $channel->close();
        $connection->close();
    }
}