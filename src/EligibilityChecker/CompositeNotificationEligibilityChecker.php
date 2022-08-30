<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

final class CompositeNotificationEligibilityChecker implements NotificationEligibilityCheckerInterface
{
    /** @var list<NotificationEligibilityCheckerInterface> */
    private array $checkers = [];

    public function add(NotificationEligibilityCheckerInterface $notificationEligibilityChecker): void
    {
        $this->checkers[] = $notificationEligibilityChecker;
    }

    public function check(NotificationInterface $notification): EligibilityCheck
    {
        $eligible = true;
        $reasons = [];

        foreach ($this->checkers as $checker) {
            $check = $checker->check($notification);
            if (!$check->eligible) {
                $eligible = false;
                $reasons[] = $check->reasons;
            }
        }

        return new EligibilityCheck($eligible, array_merge(...$reasons));
    }
}
