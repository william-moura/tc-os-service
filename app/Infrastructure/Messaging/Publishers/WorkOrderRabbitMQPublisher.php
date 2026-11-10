<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging\Publishers;

use App\Application\Services\MessagePublisherInterface;
use App\Infrastructure\Messaging\RabbitMQService;

final class WorkOrderRabbitMQPublisher implements MessagePublisherInterface
{
    public function __construct(
        private readonly RabbitMQService $rabbitMQService
    ) {}

    public function publish(string $queue, array $payload): void
    {
        $this->rabbitMQService->publish($queue, $payload);
    }
}