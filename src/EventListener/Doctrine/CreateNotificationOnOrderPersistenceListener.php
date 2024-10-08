<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAbandonedCartPlugin\Factory\NotificationFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class CreateNotificationOnOrderPersistenceListener
{
    use ORMTrait;

    public function __construct(
        private readonly NotificationFactoryInterface $notificationFactory,
        ManagerRegistry $managerRegistry,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $obj = $eventArgs->getObject();
        if (!$obj instanceof OrderInterface) {
            return;
        }

        $notification = $this->notificationFactory->createWithCart($obj);

        $manager = $this->getManager($notification);
        $manager->persist($notification);
    }
}
