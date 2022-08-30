<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class NotificationFactory implements NotificationFactoryInterface
{
    private FactoryInterface $decorated;

    public function __construct(FactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function createNew(): NotificationInterface
    {
        /** @var NotificationInterface|object $obj */
        $obj = $this->decorated->createNew();
        Assert::isInstanceOf($obj, NotificationInterface::class);

        return $obj;
    }

    public function createWithOrder(OrderInterface $order): NotificationInterface
    {
        $obj = $this->createNew();
        $obj->setCart($order);

        return $obj;
    }
}
