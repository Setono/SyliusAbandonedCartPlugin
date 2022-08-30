<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Message\Command;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Webmozart\Assert\Assert;

final class ProcessNotification implements CommandInterface
{
    /** @readonly */
    public int $notificationId;

    /**
     * @param int|NotificationInterface $notification
     */
    public function __construct($notification)
    {
        if ($notification instanceof NotificationInterface) {
            $notification = $notification->getId();
        }

        Assert::integer($notification);

        $this->notificationId = $notification;
    }
}
