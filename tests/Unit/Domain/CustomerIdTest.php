<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\ValueObjects\CustomerId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CustomerIdTest extends TestCase
{
    public function test_should_create_valid_customer_id(): void
    {
        $customerId = new CustomerId(42);

        $this->assertSame(42, $customerId->getValue());
        $this->assertSame('42', (string) $customerId);
    }

    public function test_should_throw_exception_when_id_is_zero_or_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer ID must be a positive integer.');

        new CustomerId(0);
    }

    public function test_should_compare_equality_correctly(): void
    {
        $id1 = new CustomerId(10);
        $id2 = new CustomerId(10);
        $id3 = new CustomerId(20);

        $this->assertTrue($id1->equals($id2));
        $this->assertFalse($id1->equals($id3));
    }
}