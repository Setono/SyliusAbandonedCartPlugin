<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Message\Command;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Webmozart\Assert\Assert;

final class ProcessNotification implements CommandInterface
{
    public readonly int $notification;

    public function __construct(NotificationInterface|int $notification)
    {
        if ($notification instanceof NotificationInterface) {
            $notification = $notification->getId();
        }

        Assert::integer($notification);

        $this->notification = $notification;
    }
}
