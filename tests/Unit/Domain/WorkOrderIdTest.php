<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\ValueObjects\WorkOrderId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WorkOrderIdTest extends TestCase
{
    public function test_should_create_valid_work_order_id(): void
    {
        $id = new WorkOrderId(150);

        $this->assertSame(150, $id->getValue());
        $this->assertSame('150', (string) $id);
    }

    public function test_should_throw_exception_when_id_is_zero_or_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Work Order ID must be a positive integer.');

        new WorkOrderId(0);
    }

    public function test_should_compare_equality_correctly(): void
    {
        $id1 = new WorkOrderId(10);
        $id2 = new WorkOrderId(10);
        $id3 = new WorkOrderId(25);

        $this->assertTrue($id1->equals($id2));
        $this->assertFalse($id1->equals($id3));
    }
}