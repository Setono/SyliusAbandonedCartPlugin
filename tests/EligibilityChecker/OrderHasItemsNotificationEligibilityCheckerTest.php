<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\OrderHasItemsNotificationEligibilityChecker;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;

final class OrderHasItemsNotificationEligibilityCheckerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_ineligible_when_order_is_null(): void
    {
        $notification = new Notification();
        $checker = new OrderHasItemsNotificationEligibilityChecker();

        self::assertFalse($checker->check($notification)->eligible);
    }

    /**
     * @test
     */
    public function it_returns_ineligible_when_order_is_empty(): void
    {
        $order = new Order();
        $notification = new Notification();
        $notification->setCart($order);

        $checker = new OrderHasItemsNotificationEligibilityChecker();

        self::assertFalse($checker->check($notification)->eligible);
    }

    /**
     * @test
     */
    public function it_returns_eligible_when_order_has_items(): void
    {
        $orderItem = new OrderItem();
        $order = new Order();
        $order->addItem($orderItem);

        $notification = new Notification();
        $notification->setCart($order);

        $checker = new OrderHasItemsNotificationEligibilityChecker();

        self::assertTrue($checker->check($notification)->eligible);
    }
}
