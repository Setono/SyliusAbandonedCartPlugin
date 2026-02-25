<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Mailer;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Mailer\EmailManager;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\CartRecoveryUrlGeneratorInterface;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\UnsubscribeUrlGeneratorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;

final class EmailManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_sends_email_with_correct_parameters(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getChannel()->willReturn($channel->reveal());
        $order->getLocaleCode()->willReturn('en_US');

        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getCart()->willReturn($order->reveal());
        $notification->getEmail()->willReturn('customer@example.com');

        $emailSender = $this->prophesize(SenderInterface::class);
        $emailSender->send('abandoned_cart_email', ['customer@example.com'], [
            'notification' => $notification->reveal(),
            'channel' => $channel->reveal(),
            'localeCode' => 'en_US',
            'urls' => [
                'cartRecovery' => 'https://shop.com/recover/abc123',
                'unsubscribe' => 'https://shop.com/unsubscribe?email=customer@example.com&hash=xyz',
            ],
        ])->shouldBeCalled();

        $cartRecoveryUrlGenerator = $this->prophesize(CartRecoveryUrlGeneratorInterface::class);
        $cartRecoveryUrlGenerator->generate($order->reveal())->willReturn('https://shop.com/recover/abc123');

        $unsubscribeUrlGenerator = $this->prophesize(UnsubscribeUrlGeneratorInterface::class);
        $unsubscribeUrlGenerator->generate($channel->reveal(), 'customer@example.com', 'en_US')->willReturn('https://shop.com/unsubscribe?email=customer@example.com&hash=xyz');

        $emailManager = new EmailManager(
            $emailSender->reveal(),
            $cartRecoveryUrlGenerator->reveal(),
            $unsubscribeUrlGenerator->reveal(),
        );

        $emailManager->sendNotification($notification->reveal());
    }

    /**
     * @test
     */
    public function it_throws_when_notification_has_no_cart(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getCart()->willReturn(null);

        $emailSender = $this->prophesize(SenderInterface::class);
        $cartRecoveryUrlGenerator = $this->prophesize(CartRecoveryUrlGeneratorInterface::class);
        $unsubscribeUrlGenerator = $this->prophesize(UnsubscribeUrlGeneratorInterface::class);

        $emailManager = new EmailManager(
            $emailSender->reveal(),
            $cartRecoveryUrlGenerator->reveal(),
            $unsubscribeUrlGenerator->reveal(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $emailManager->sendNotification($notification->reveal());
    }

    /**
     * @test
     */
    public function it_throws_when_cart_has_no_channel(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getChannel()->willReturn(null);

        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getCart()->willReturn($order->reveal());

        $emailSender = $this->prophesize(SenderInterface::class);
        $cartRecoveryUrlGenerator = $this->prophesize(CartRecoveryUrlGeneratorInterface::class);
        $unsubscribeUrlGenerator = $this->prophesize(UnsubscribeUrlGeneratorInterface::class);

        $emailManager = new EmailManager(
            $emailSender->reveal(),
            $cartRecoveryUrlGenerator->reveal(),
            $unsubscribeUrlGenerator->reveal(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $emailManager->sendNotification($notification->reveal());
    }

    /**
     * @test
     */
    public function it_throws_when_notification_has_no_email(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getChannel()->willReturn($channel->reveal());

        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getCart()->willReturn($order->reveal());
        $notification->getEmail()->willReturn(null);

        $emailSender = $this->prophesize(SenderInterface::class);
        $cartRecoveryUrlGenerator = $this->prophesize(CartRecoveryUrlGeneratorInterface::class);
        $unsubscribeUrlGenerator = $this->prophesize(UnsubscribeUrlGeneratorInterface::class);

        $emailManager = new EmailManager(
            $emailSender->reveal(),
            $cartRecoveryUrlGenerator->reveal(),
            $unsubscribeUrlGenerator->reveal(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $emailManager->sendNotification($notification->reveal());
    }
}
