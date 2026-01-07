<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Creator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Creator\NotificationCreator;
use Setono\SyliusAbandonedCartPlugin\DataProvider\IdleCartDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\Factory\NotificationFactoryInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class NotificationCreatorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_creates_notifications_for_idle_carts(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getId()->willReturn(1);

        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getId()->willReturn(100);

        $idleCartDataProvider = $this->prophesize(IdleCartDataProviderInterface::class);
        $idleCartDataProvider->getCarts()->willReturn([$order->reveal()]);

        $notificationFactory = $this->prophesize(NotificationFactoryInterface::class);
        $notificationFactory->createWithCart($order->reveal())->willReturn($notification->reveal());

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->persist($notification->reveal())->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Argument::any())->willReturn($entityManager->reveal());

        $creator = new NotificationCreator(
            $managerRegistry->reveal(),
            $idleCartDataProvider->reveal(),
            $notificationFactory->reveal(),
        );

        $count = $creator->create();

        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function it_does_not_persist_in_dry_run_mode(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getId()->willReturn(1);

        $idleCartDataProvider = $this->prophesize(IdleCartDataProviderInterface::class);
        $idleCartDataProvider->getCarts()->willReturn([$order->reveal()]);

        $notificationFactory = $this->prophesize(NotificationFactoryInterface::class);
        $notificationFactory->createWithCart(Argument::any())->shouldNotBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $creator = new NotificationCreator(
            $managerRegistry->reveal(),
            $idleCartDataProvider->reveal(),
            $notificationFactory->reveal(),
        );

        $count = $creator->create(true);

        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function it_returns_zero_when_no_idle_carts(): void
    {
        $idleCartDataProvider = $this->prophesize(IdleCartDataProviderInterface::class);
        $idleCartDataProvider->getCarts()->willReturn([]);

        $notificationFactory = $this->prophesize(NotificationFactoryInterface::class);

        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $creator = new NotificationCreator(
            $managerRegistry->reveal(),
            $idleCartDataProvider->reveal(),
            $notificationFactory->reveal(),
        );

        $count = $creator->create();

        self::assertSame(0, $count);
    }
}
