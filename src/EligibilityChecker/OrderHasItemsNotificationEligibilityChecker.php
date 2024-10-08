<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

final class OrderHasItemsNotificationEligibilityChecker implements NotificationEligibilityCheckerInterface
{
    public function check(NotificationInterface $notification): EligibilityCheck
    {
        $order = $notification->getCart();
        if (null === $order) {
            return new EligibilityCheck(false, 'The order is not set');
        }

        if ($order->getItems()->isEmpty()) {
            return new EligibilityCheck(false, 'The order has no items');
        }

        return new EligibilityCheck(true);
    }
}
