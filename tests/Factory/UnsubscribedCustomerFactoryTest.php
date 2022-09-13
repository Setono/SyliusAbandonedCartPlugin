<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\Factory;

use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\Factory\UnsubscribedCustomerFactory;
use Setono\SyliusAbandonedCartPlugin\Factory\UnsubscribedCustomerFactoryInterface;
use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomer;
use Sylius\Component\Resource\Factory\Factory;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\Factory\UnsubscribedCustomerFactory
 */
final class UnsubscribedCustomerFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_with_email(): void
    {
        $entity = $this->getFactory()->createWithEmail('johndoe@example.com');

        self::assertSame('johndoe@example.com', $entity->getEmail());
    }

    private function getFactory(): UnsubscribedCustomerFactoryInterface
    {
        return new UnsubscribedCustomerFactory(new Factory(UnsubscribedCustomer::class));
    }
}
