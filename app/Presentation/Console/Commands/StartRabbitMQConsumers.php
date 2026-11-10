<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Infrastructure\Messaging\Consumers\PaymentFailedConsumer;
use App\Infrastructure\Messaging\Consumers\RepairCompletedConsumer;
use Illuminate\Console\Command;

final class StartRabbitMQConsumers extends Command
{
    protected $signature = 'rabbitmq:consume {queue=all : The queue to listen to (all, payment-failed, repair-completed)}';
    protected $description = 'Consume RabbitMQ events for Saga Pattern coordination';

    public function handle(
        PaymentFailedConsumer $paymentFailedConsumer,
        RepairCompletedConsumer $repairCompletedConsumer
    ): void {
        $target = $this->argument('queue');

        match ($target) {
            'repair-completed' => $repairCompletedConsumer->handle(),
            'payment-failed' => $paymentFailedConsumer->handle(),
            default => $this->info('Run consumers in separate supervisor/K8s worker processes for each queue.'),
        };
    }
}