<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\DataProvider;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

interface PendingNotificationDataProviderInterface
{
    /**
     * Returns notifications ready to be processed (in initial state with idle carts).
     *
     * @return iterable<NotificationInterface>
     */
    public function getNotifications(): iterable;
}
