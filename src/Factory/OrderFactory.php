<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * This factory decorates the order factory to set the token on the order
 * because we need this token if we want to reinstate a cart to a customer.
 */
final class OrderFactory implements FactoryInterface
{
    private FactoryInterface $decorated;

    private OrderTokenAssignerInterface $orderTokenAssigner;

    public function __construct(
        FactoryInterface $decorated,
        OrderTokenAssignerInterface $orderTokenAssigner,
    ) {
        $this->decorated = $decorated;
        $this->orderTokenAssigner = $orderTokenAssigner;
    }

    public function createNew(): object
    {
        $order = $this->decorated->createNew();

        if ($order instanceof OrderInterface) {
            $this->orderTokenAssigner->assignTokenValueIfNotSet($order);
        }

        return $order;
    }
}
