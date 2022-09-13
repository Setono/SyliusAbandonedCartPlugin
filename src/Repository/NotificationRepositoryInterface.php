<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Repository;

use DateInterval;
use DateTimeInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * The idle threshold indicates how long the cart must have been idle before a notification is considered to be sent
     *
     * @return list<NotificationInterface>
     */
    public function findNew(DateInterval $idleThreshold): array;

    /**
     * Will remove notifications older than the given threshold
     */
    public function removeOlderThan(DateTimeInterface $threshold): void;
}
