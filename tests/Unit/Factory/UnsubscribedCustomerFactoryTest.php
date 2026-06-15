<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Factory;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\Factory\UnsubscribedCustomerFactory;
use Setono\SyliusAbandonedCartPlugin\Factory\UnsubscribedCustomerFactoryInterface;
use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomer;
use Sylius\Component\Resource\Factory\Factory;

final class UnsubscribedCustomerFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_with_email(): void
    {
        $entity = $this->getFactory()->createWithEmail('johndoe@example.com');

        self::assertSame('johndoe@example.com', $entity->getEmail());
    }

    private function getFactory(): UnsubscribedCustomerFactoryInterface
    {
        /** @phpstan-ignore argument.type */
        return new UnsubscribedCustomerFactory(new Factory(UnsubscribedCustomer::class));
    }
}
