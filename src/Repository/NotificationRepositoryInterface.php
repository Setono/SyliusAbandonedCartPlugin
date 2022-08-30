<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Repository;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<NotificationInterface>
     */
    public function findNew(): array;
}
