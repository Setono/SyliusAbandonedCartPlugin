<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * This factory decorates the order factory and create a notification each time a new order is created.
 * If then the order is persisted and flushed, the notification will also be persisted
 */
final class OrderFactory implements FactoryInterface
{
    use ORMManagerTrait;

    private FactoryInterface $decorated;

    private NotificationFactoryInterface $notificationFactory;

    public function __construct(
        FactoryInterface $decorated,
        NotificationFactoryInterface $notificationFactory,
        ManagerRegistry $managerRegistry
    ) {
        $this->decorated = $decorated;
        $this->notificationFactory = $notificationFactory;
        $this->managerRegistry = $managerRegistry;
    }

    public function createNew(): object
    {
        $order = $this->decorated->createNew();

        if ($order instanceof OrderInterface) {
            $notification = $this->notificationFactory->createWithOrder($order);
            $this->getManager($notification)->persist($notification);
        }

        return $order;
    }
}
