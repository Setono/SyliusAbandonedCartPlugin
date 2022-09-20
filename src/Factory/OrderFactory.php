<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * This factory decorates the order factory and creates a notification each time a new order is created.
 * Also, it sets the token on the order because we need this token if we want to reinstate a cart to a customer.
 *
 * If the order is persisted and flushed, the notification will also be persisted
 */
final class OrderFactory implements FactoryInterface
{
    use ORMManagerTrait;

    private FactoryInterface $decorated;

    private OrderTokenAssignerInterface $orderTokenAssigner;

    private NotificationFactoryInterface $notificationFactory;

    public function __construct(
        FactoryInterface $decorated,
        OrderTokenAssignerInterface $orderTokenAssigner,
        NotificationFactoryInterface $notificationFactory,
        ManagerRegistry $managerRegistry
    ) {
        $this->decorated = $decorated;
        $this->orderTokenAssigner = $orderTokenAssigner;
        $this->notificationFactory = $notificationFactory;
        $this->managerRegistry = $managerRegistry;
    }

    public function createNew(): object
    {
        $order = $this->decorated->createNew();

        if ($order instanceof OrderInterface) {
            $this->orderTokenAssigner->assignTokenValueIfNotSet($order);

            $notification = $this->notificationFactory->createWithCart($order);

            $manager = $this->getManager($notification);
            if ($manager->isOpen()) {
                $manager->persist($notification);
            }
        }

        return $order;
    }
}
