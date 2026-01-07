<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Creator;

interface NotificationCreatorInterface
{
    /**
     * Creates notifications for idle carts.
     * Returns the number of notifications created.
     */
    public function create(bool $dryRun = false): int;
}
