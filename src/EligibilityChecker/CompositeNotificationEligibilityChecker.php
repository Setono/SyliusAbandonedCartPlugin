<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use Setono\CompositeCompilerPass\CompositeService;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

/**
 * @extends CompositeService<NotificationEligibilityCheckerInterface>
 */
final class CompositeNotificationEligibilityChecker extends CompositeService implements NotificationEligibilityCheckerInterface
{
    public function check(NotificationInterface $notification): EligibilityCheck
    {
        $eligible = true;
        $reasons = [];

        foreach ($this->services as $checker) {
            $check = $checker->check($notification);
            if (!$check->eligible) {
                $eligible = false;
                $reasons[] = $check->reasons;
            }
        }

        return new EligibilityCheck($eligible, array_merge(...$reasons));
    }
}
