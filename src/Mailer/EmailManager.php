<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Mailer;

use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasherInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Webmozart\Assert\Assert;

final class EmailManager implements EmailManagerInterface
{
    private SenderInterface $emailSender;

    private EmailHasherInterface $emailHasher;

    public function __construct(SenderInterface $emailSender, EmailHasherInterface $emailHasher)
    {
        $this->emailSender = $emailSender;
        $this->emailHasher = $emailHasher;
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
            'email' => $email,
            'hashedEmail' => $this->emailHasher->hash($email),
        ]);
    }
}
