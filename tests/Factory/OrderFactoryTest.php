<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Factory\NotificationFactory;
use Setono\SyliusAbandonedCartPlugin\Factory\OrderFactory;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
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

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->isOpen()->willReturn(true);
        $entityManager->persist(Argument::type(NotificationInterface::class))->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Notification::class)->willReturn($entityManager->reveal());

        $factory = new OrderFactory(
            new Factory(Order::class),
            $orderTokenAssigner->reveal(),
            new NotificationFactory(new Factory(Notification::class)),
            $managerRegistry->reveal()
        );
        $factory->createNew();
    }
}
