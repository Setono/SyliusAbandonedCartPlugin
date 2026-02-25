<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\EligibilityChecker;

use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\SubscribedToNewsletterNotificationEligibilityChecker;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\EligibilityChecker\SubscribedToNewsletterNotificationEligibilityChecker
 */
final class SubscribedToNewsletterNotificationEligibilityCheckerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_eligible_when_customer_is_null(): void
    {
        $order = new Order();
        $notification = new Notification();
        $notification->setCart($order);

        $checker = new SubscribedToNewsletterNotificationEligibilityChecker();
        self::assertTrue($checker->check($notification)->eligible);
    }

    /**
     * @test
     */
    public function it_returns_eligible_when_customer_is_subscribed(): void
    {
        $customer = new Customer();
        $customer->setSubscribedToNewsletter(true);

        $order = new Order();
        $order->setCustomer($customer);

        $notification = new Notification();
        $notification->setCart($order);

        $checker = new SubscribedToNewsletterNotificationEligibilityChecker();
        self::assertTrue($checker->check($notification)->eligible);
    }

    /**
     * @test
     */
    public function it_returns_non_eligible_when_customer_is_not_subscribed(): void
    {
        $customer = new Customer();
        $customer->setSubscribedToNewsletter(false);

        $order = new Order();
        $order->setCustomer($customer);

        $notification = new Notification();
        $notification->setCart($order);

        $checker = new SubscribedToNewsletterNotificationEligibilityChecker();
        self::assertFalse($checker->check($notification)->eligible);
    }
}
