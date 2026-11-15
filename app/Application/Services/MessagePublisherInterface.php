<?php

declare(strict_types=1);

namespace App\Application\Services;

interface MessagePublisherInterface
{
    public function publish(string $queue, array $payload): void;
}