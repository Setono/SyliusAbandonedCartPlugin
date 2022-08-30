<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

interface NotificationEligibilityCheckerInterface
{
    public function check(NotificationInterface $notification): EligibilityCheck;
}
