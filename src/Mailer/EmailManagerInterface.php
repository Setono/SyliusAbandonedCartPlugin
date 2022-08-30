<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Mailer;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

interface EmailManagerInterface
{
    public function sendNotification(NotificationInterface $notification): void;
}
