<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum WorkOrderStatus: string
{
    case CREATED = 'CREATED';
    case AWAITING_PAYMENT = 'AWAITING_PAYMENT';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELED = 'CANCELED';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::CREATED => in_array($next, [self::AWAITING_PAYMENT, self::CANCELED], true),
            self::AWAITING_PAYMENT => in_array($next, [self::IN_PROGRESS, self::CANCELED], true),
            self::IN_PROGRESS => in_array($next, [self::COMPLETED, self::CANCELED], true),
            self::COMPLETED, self::CANCELED => false,
        };
    }
}