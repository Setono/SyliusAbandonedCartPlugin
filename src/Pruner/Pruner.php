<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Pruner;

use DateTimeImmutable;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepositoryInterface;

final readonly class Pruner implements PrunerInterface
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private int $pruneOlderThan,
    ) {
    }

    public function prune(): void
    {
        $this->notificationRepository->removeOlderThan(
            new DateTimeImmutable(sprintf('-%d minutes', $this->pruneOlderThan)),
        );
    }
}
