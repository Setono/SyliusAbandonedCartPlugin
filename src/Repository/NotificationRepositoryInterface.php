<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Repository;

use DateTimeInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * Will remove notifications older than the given threshold
     */
    public function removeOlderThan(DateTimeInterface $threshold): void;

    public function findOneByOrder(OrderInterface $order): ?NotificationInterface;
}
