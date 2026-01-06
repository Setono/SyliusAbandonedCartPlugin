<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Factory\OrderFactory;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;
use Sylius\Component\Resource\Factory\Factory;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\Factory\OrderFactory
 */
final class OrderFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_creates_notification_when_creating_order(): void
    {
        $orderTokenAssigner = $this->prophesize(OrderTokenAssignerInterface::class);
        $orderTokenAssigner->assignTokenValueIfNotSet(Argument::type(OrderInterface::class))->shouldBeCalled();

        $factory = new OrderFactory(
            new Factory(Order::class),
            $orderTokenAssigner->reveal(),
        );
        $factory->createNew();
    }
}
