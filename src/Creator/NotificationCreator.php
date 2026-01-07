<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Creator;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAbandonedCartPlugin\DataProvider\IdleCartDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\Factory\NotificationFactoryInterface;

final class NotificationCreator implements NotificationCreatorInterface, LoggerAwareInterface
{
    use ORMTrait;

    private LoggerInterface $logger;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly IdleCartDataProviderInterface $idleCartDataProvider,
        private readonly NotificationFactoryInterface $notificationFactory,
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = new NullLogger();
    }

    public function create(bool $dryRun = false): int
    {
        $count = 0;

        foreach ($this->idleCartDataProvider->getCarts() as $cart) {
            if ($dryRun) {
                $this->logger->info(sprintf(
                    '[DRY-RUN] Would create notification for cart #%d',
                    $cart->getId(),
                ));
                ++$count;

                continue;
            }

            $notification = $this->notificationFactory->createWithCart($cart);

            $manager = $this->getManager($notification);
            $manager->persist($notification);
            $manager->flush();

            $this->logger->debug(sprintf(
                'Created notification #%d for cart #%d',
                $notification->getId(),
                $cart->getId(),
            ));

            ++$count;
        }

        $this->logger->info(sprintf(
            '%s %d notification(s)',
            $dryRun ? '[DRY-RUN] Would create' : 'Created',
            $count,
        ));

        return $count;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
