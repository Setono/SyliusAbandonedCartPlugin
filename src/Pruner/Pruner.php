<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Pruner;

use DateTimeImmutable;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepositoryInterface;

final class Pruner implements PrunerInterface
{
    private NotificationRepositoryInterface $notificationRepository;

    private int $pruneOlderThan;

    public function __construct(NotificationRepositoryInterface $notificationRepository, int $pruneOlderThan)
    {
        $this->notificationRepository = $notificationRepository;
        $this->pruneOlderThan = $pruneOlderThan;
    }

    public function prune(): void
    {
        $this->notificationRepository->removeOlderThan(
            new DateTimeImmutable(sprintf('-%d minutes', $this->pruneOlderThan)),
        );
    }
}
