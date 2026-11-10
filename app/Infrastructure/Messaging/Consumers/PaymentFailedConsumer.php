<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\Consumers;

use App\Application\UseCases\CancelWorkOrderUseCase;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class PaymentFailedConsumer
{
    public function __construct(
        private CancelWorkOrderUseCase $cancelUseCase
    ) {}

    public function handle(): void
    {
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', 'localhost'),
            (int) env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest')
        );

        $channel = $connection->channel();
        $channel->queue_declare('queue.payment.failed', false, true, false, false);

        $callback = function ($msg) {
            $data = json_decode($msg->body, true);
            if (isset($data['work_order_id'])) {
                // Executa compensação da Saga
                $this->cancelUseCase->execute((int) $data['work_order_id'], $data['reason'] ?? 'Payment rejected');
            }
            $msg->ack();
        };

        $channel->basic_consume('queue.payment.failed', '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}