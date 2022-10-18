<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\SyliusAbandonedCartPlugin\Factory\NotificationFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class CreateNotificationOnOrderPersistenceListener
{
    use ORMManagerTrait;

    private NotificationFactoryInterface $notificationFactory;

    public function __construct(
        NotificationFactoryInterface $notificationFactory,
        ManagerRegistry $managerRegistry
    ) {
        $this->notificationFactory = $notificationFactory;
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
