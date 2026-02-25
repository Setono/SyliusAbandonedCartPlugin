<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class NotificationTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_returns_true_for_is_failed_when_state_is_failed(): void
    {
        $notification = new Notification();
        $notification->setState(NotificationWorkflow::STATE_FAILED);

        self::assertTrue($notification->isFailed());
    }

    /**
     * @test
     */
    public function it_returns_false_for_is_failed_when_state_is_not_failed(): void
    {
        $notification = new Notification();
        $notification->setState(NotificationWorkflow::STATE_PENDING);

        self::assertFalse($notification->isFailed());
    }

    /**
     * @test
     */
    public function it_returns_true_for_is_ineligible_when_state_is_ineligible(): void
    {
        $notification = new Notification();
        $notification->setState(NotificationWorkflow::STATE_INELIGIBLE);

        self::assertTrue($notification->isIneligible());
    }

    /**
     * @test
     */
    public function it_returns_false_for_is_ineligible_when_state_is_not_ineligible(): void
    {
        $notification = new Notification();
        $notification->setState(NotificationWorkflow::STATE_SENT);

        self::assertFalse($notification->isIneligible());
    }

    /**
     * @test
     */
    public function it_returns_true_for_is_deletable_when_state_is_not_sent(): void
    {
        $notification = new Notification();

        foreach ([NotificationWorkflow::STATE_PENDING, NotificationWorkflow::STATE_PROCESSING, NotificationWorkflow::STATE_FAILED, NotificationWorkflow::STATE_INELIGIBLE] as $state) {
            $notification->setState($state);
            self::assertTrue($notification->isDeletable(), sprintf('Expected isDeletable() to be true for state "%s"', $state));
        }
    }

    /**
     * @test
     */
    public function it_returns_false_for_is_deletable_when_state_is_sent(): void
    {
        $notification = new Notification();
        $notification->setState(NotificationWorkflow::STATE_SENT);

        self::assertFalse($notification->isDeletable());
    }

    /**
     * @test
     */
    public function it_manages_processing_errors(): void
    {
        $notification = new Notification();

        self::assertSame([], $notification->getProcessingErrors());

        $notification->addProcessingError('Error 1');
        self::assertSame(['Error 1'], $notification->getProcessingErrors());

        $notification->addProcessingErrors(['Error 2', 'Error 3']);
        self::assertSame(['Error 1', 'Error 2', 'Error 3'], $notification->getProcessingErrors());

        $notification->resetProcessingErrors();
        self::assertSame([], $notification->getProcessingErrors());
    }

    /**
     * @test
     */
    public function it_derives_email_from_cart_customer(): void
    {
        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getEmailCanonical()->willReturn('customer@example.com');

        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn($customer->reveal());

        $notification = new Notification();
        $notification->setCart($order->reveal());

        self::assertSame('customer@example.com', $notification->getEmail());
    }

    /**
     * @test
     */
    public function it_returns_null_email_when_no_cart(): void
    {
        $notification = new Notification();

        self::assertNull($notification->getEmail());
    }

    /**
     * @test
     */
    public function it_returns_null_email_when_cart_has_no_customer(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn(null);

        $notification = new Notification();
        $notification->setCart($order->reveal());

        self::assertNull($notification->getEmail());
    }

    /**
     * @test
     */
    public function it_returns_recipient_first_name_from_customer(): void
    {
        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getFirstName()->willReturn('John');

        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn($customer->reveal());

        $notification = new Notification();
        $notification->setCart($order->reveal());

        self::assertSame('John', $notification->getRecipientFirstName());
    }

    /**
     * @test
     */
    public function it_returns_recipient_first_name_from_billing_address(): void
    {
        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getFirstName()->willReturn(null);

        $address = $this->prophesize(AddressInterface::class);
        $address->getFirstName()->willReturn('Jane');

        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn($customer->reveal());
        $order->getBillingAddress()->willReturn($address->reveal());

        $notification = new Notification();
        $notification->setCart($order->reveal());

        self::assertSame('Jane', $notification->getRecipientFirstName());
    }

    /**
     * @test
     */
    public function it_returns_null_recipient_first_name_when_no_cart(): void
    {
        $notification = new Notification();

        self::assertNull($notification->getRecipientFirstName());
    }

    /**
     * @test
     */
    public function it_returns_null_recipient_first_name_when_no_customer_or_address(): void
    {
        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getFirstName()->willReturn(null);

        $order = $this->prophesize(OrderInterface::class);
        $order->getCustomer()->willReturn($customer->reveal());
        $order->getBillingAddress()->willReturn(null);

        $notification = new Notification();
        $notification->setCart($order->reveal());

        self::assertNull($notification->getRecipientFirstName());
    }
}
