<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class NotificationFactory implements NotificationFactoryInterface
{
    /** @param FactoryInterface<NotificationInterface> $decorated */
    public function __construct(private readonly FactoryInterface $decorated)
    {
    }

    public function createNew(): NotificationInterface
    {
        return $this->decorated->createNew();
    }

    public function createWithCart(OrderInterface $order): NotificationInterface
    {
        $obj = $this->createNew();
        $obj->setCart($order);

        return $obj;
    }
}
