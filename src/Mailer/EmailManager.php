<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Mailer;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\CartRecoveryUrlGeneratorInterface;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\UnsubscribeUrlGeneratorInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Webmozart\Assert\Assert;

final class EmailManager implements EmailManagerInterface
{
    private SenderInterface $emailSender;

    private CartRecoveryUrlGeneratorInterface $cartRecoveryUrlGenerator;

    private UnsubscribeUrlGeneratorInterface $unsubscribeUrlGenerator;

    public function __construct(
        SenderInterface $emailSender,
        CartRecoveryUrlGeneratorInterface $cartRecoveryUrlGenerator,
        UnsubscribeUrlGeneratorInterface $unsubscribeUrlGenerator
    ) {
        $this->emailSender = $emailSender;
        $this->cartRecoveryUrlGenerator = $cartRecoveryUrlGenerator;
        $this->unsubscribeUrlGenerator = $unsubscribeUrlGenerator;
    }

    public function sendNotification(NotificationInterface $notification): void
    {
        $order = $notification->getCart();
        Assert::notNull($order);

        $channel = $order->getChannel();

        $email = $notification->getEmail();
        Assert::notNull($email);

        $this->emailSender->send('abandoned_cart_email', [$email], [
            'notification' => $notification,
            'channel' => $channel,
            'localeCode' => $order->getLocaleCode(),
            'urls' => [
                'cartRecovery' => $this->cartRecoveryUrlGenerator->generate($order),
                'unsubscribe' => $this->unsubscribeUrlGenerator->generate($email, (string) $order->getLocaleCode()),
            ],
        ]);
    }
}
