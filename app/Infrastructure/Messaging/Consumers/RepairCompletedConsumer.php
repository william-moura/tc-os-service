<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\Consumers;

use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Domain\ValueObjects\WorkOrderStatus;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class RepairCompletedConsumer
{
    public function __construct(
        private WorkOrderRepositoryInterface $repository
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
        $queueName = 'queue.repair.completed';

        $channel->queue_declare($queueName, false, true, false, false);

        Log::info("Listening for repair completion events on {$queueName}...");

        $callback = function (AMQPMessage $msg): void {
            try {
                $payload = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);

                if (!isset($payload['work_order_id'])) {
                    Log::warning('Received repair completed event without work_order_id', ['body' => $msg->body]);
                    $msg->ack();
                    return;
                }

                $workOrderId = (int) $payload['work_order_id'];
                $workOrder = $this->repository->findById($workOrderId);

                if (!$workOrder) {
                    Log::error("Work order not found for completion: {$workOrderId}");
                    $msg->ack();
                    return;
                }

                // Transição do agregado para concluído
                $workOrder->transitionTo(WorkOrderStatus::COMPLETED);
                $this->repository->save($workOrder);

                Log::info("Work order {$workOrderId} transitioned to COMPLETED successfully.");
                $msg->ack();
            } catch (Exception $e) {
                Log::error("Failed to process repair completed event: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
                // Rejeita e não reenvia caso o payload seja inválido
                $msg->nack(false, false);
            }
        };

        $channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}