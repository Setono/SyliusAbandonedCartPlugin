<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomer;

final class UnsubscribedCustomerTest extends TestCase
{
    #[Test]
    public function it_has_no_id_until_persisted(): void
    {
        self::assertNull((new UnsubscribedCustomer())->getId());
    }

    #[Test]
    public function it_holds_an_email(): void
    {
        $customer = new UnsubscribedCustomer();
        self::assertNull($customer->getEmail());

        $customer->setEmail('john@example.com');
        self::assertSame('john@example.com', $customer->getEmail());
    }
}
