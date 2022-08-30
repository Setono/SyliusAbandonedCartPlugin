<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface NotificationFactoryInterface extends FactoryInterface
{
    public function createNew(): NotificationInterface;

    public function createWithOrder(OrderInterface $order): NotificationInterface;
}
