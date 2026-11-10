<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Infrastructure\Messaging\Consumers\PaymentFailedConsumer;
use Illuminate\Console\Command;

final class StartRabbitMQConsumers extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Start consuming Saga pattern compensation events';

    public function handle(PaymentFailedConsumer $consumer): void
    {
        $this->info('Starting Saga Consumer: listening on queue.payment.failed...');
        $consumer->handle();
    }
}