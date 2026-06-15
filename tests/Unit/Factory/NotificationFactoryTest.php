<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Factory;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\Factory\NotificationFactory;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Factory\Factory;

final class NotificationFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_with_cart(): void
    {
        $order = new Order();

        /** @phpstan-ignore argument.type */
        $factory = new NotificationFactory(new Factory(Notification::class));
        $notification = $factory->createWithCart($order);

        self::assertSame($order, $notification->getCart());
    }
}
