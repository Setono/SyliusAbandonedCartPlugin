<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Repository;

use DateTimeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * Will remove notifications older than the given threshold
     */
    public function removeOlderThan(DateTimeInterface $threshold): void;
}
