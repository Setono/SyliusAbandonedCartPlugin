<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Mailer;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;

class EmailManager implements EmailManagerInterface
{
    private SenderInterface $emailSender;

    public function __construct(SenderInterface $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    public function sendNotification(NotificationInterface $notification): void
    {
        $this->emailSender->send('abandoned_cart_email', [$notification->getEmail()], [
            'notification' => $notification,
        ]);
    }
}
